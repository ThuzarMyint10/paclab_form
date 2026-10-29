<?php

namespace App\Services;

use App\Mail\CoaReleasedMail;
use App\Mail\EnquiryReceivedMail;
use App\Mail\InvoiceIssuedMail;
use App\Mail\NewEnquiryAdminMail;
use App\Mail\QuotationIssuedMail;
use App\Mail\QuotationResponseAdminMail;
use App\Mail\SampleSubmissionFormMail;
use App\Mail\TrackingUpdateMail;
use App\Models\CoaReport;
use App\Models\Company;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\LabTest;
use App\Models\Setting;
use App\Models\TrackingStatus;
use App\Models\TrackingUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * All business rules of the PacLab job flow live here so that the admin
 * pages, customer portal and public links behave identically.
 */
class Workflow
{
    /* =========================================================
     | 1. Customer submits an enquiry
     * ========================================================= */
    public static function createEnquiry(array $data, ?User $user = null): Enquiry
    {
        $setupToken = null;

        $enquiry = DB::transaction(function () use ($data, $user, &$setupToken) {
            // -- Find or create the customer account so they can log in and see only their own jobs
            $customer = ($user && $user->isCustomer()) ? $user : User::where('email', $data['email'])->first();
            if (! $customer) {
                $customer = User::create([
                    'name' => $data['contact_person'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(40)),
                    'role' => 'customer',
                    'company_name' => $data['company_name'],
                    'phone' => $data['phone'] ?? null,
                ]);
                $setupToken = Password::broker()->createToken($customer);
            }

            // -- Link to the customer account master for agreed discount / payment terms
            $company = $customer->company ?: Company::matchByName($data['company_name']);
            if ($company && ! $customer->company_id && $customer->isCustomer()) {
                $customer->forceFill(['company_id' => $company->id])->save();
            }

            $currency = in_array($data['currency'] ?? '', ['SGD', 'USD'], true) ? $data['currency'] : ($company->currency ?? 'SGD');

            $enquiry = Enquiry::create([
                'reference' => Numbering::enquiry(),
                'access_token' => Str::random(48),
                'customer_id' => $customer->isCustomer() ? $customer->id : null,
                'company_id' => $company?->id,
                'status' => 'submitted',
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'fax' => $data['fax'] ?? null,
                'address' => $data['address'] ?? null,
                'country' => $data['country'] ?? null,
                'currency' => $currency,
                // Turnaround is chosen per test; recalculate() sets this to 'urgent' if any test is urgent
                'turnaround' => 'standard',
                'special_instructions' => $data['special_instructions'] ?? null,
                'report_name' => $data['report_name'] ?? null,
                'report_company' => $data['report_company'] ?? null,
                'report_address' => $data['report_address'] ?? null,
                'invoice_name' => $data['invoice_name'] ?? null,
                'invoice_company' => $data['invoice_company'] ?? null,
                'invoice_address' => $data['invoice_address'] ?? null,
                'discount_percent' => $company ? (float) $company->discount_percent : 0,
                'payment_terms' => $company?->payment_terms ?: Setting::get('default_payment_terms'),
            ]);

            self::saveSamples($enquiry, $data['samples'] ?? []);
            $enquiry->recalculate();

            self::track($enquiry, 'enquiry_received', null, null, false);

            return $enquiry;
        });

        // Emails go out after the data is safely stored
        Notifier::send($enquiry->email, new EnquiryReceivedMail($enquiry, $setupToken));
        Notifier::admins(new NewEnquiryAdminMail($enquiry));

        return $enquiry;
    }

    /**
     * $samples = [ ['description'=>..,'sample_type'=>..,'quantity'=>..,'storage'=>..,'batch_no'=>..,'production_date'=>..,
     *               'turnaround'=>'standard|urgent',
     *               'tests'=>[ ['lab_test_id'=>..,'quantity'=>..], ...] ], ... ]
     */
    public static function saveSamples(Enquiry $enquiry, array $samples): void
    {
        $testIds = collect($samples)->pluck('tests')->flatten(1)->pluck('lab_test_id')->filter()->unique();
        $tests = LabTest::whereIn('id', $testIds)->get()->keyBy('id');
        $itemOrder = 0;

        foreach (array_values($samples) as $s => $sample) {
            $row = $enquiry->samples()->create([
                'description' => $sample['description'],
                'sample_type' => $sample['sample_type'] ?? null,
                'batch_no' => $sample['batch_no'] ?? null,
                'production_date' => $sample['production_date'] ?? null,
                'quantity' => max(1, (int) ($sample['quantity'] ?? 1)),
                'storage' => $sample['storage'] ?? null,
                'urgent' => ($sample['turnaround'] ?? 'standard') === 'urgent',
                'sort_order' => $s,
            ]);

            foreach ($sample['tests'] ?? [] as $t) {
                $test = $tests->get($t['lab_test_id'] ?? 0);
                if (! $test) {
                    continue;
                }
                $price = $test->priceIn($enquiry->currency);
                // Each test is charged per sample: qty defaults to the number of samples
                $qty = max(1, (int) ($t['quantity'] ?? $row->quantity));
                $enquiry->items()->create([
                    'enquiry_sample_id' => $row->id,
                    'lab_test_id' => $test->id,
                    'test_name' => $test->name,
                    'matrix' => $test->matrix,
                    'method' => $test->method,
                    'list_price' => $price,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'urgent' => $row->urgent, // every test follows its sample's turnaround
                    'amount' => round($price * $qty, 2),
                    'sort_order' => $itemOrder++,
                ]);
            }
        }
    }

    /* =========================================================
     | 2. Staff prices the enquiry
     * ========================================================= */

    /** Reset every line to the standard (pre-approved) price list in the enquiry currency */
    public static function applyPriceList(Enquiry $enquiry): void
    {
        foreach ($enquiry->items()->with('labTest')->get() as $item) {
            if ($item->labTest) {
                $price = $item->labTest->priceIn($enquiry->currency);
                $item->update(['list_price' => $price, 'unit_price' => $price]);
            }
        }
        if ($enquiry->company) {
            $enquiry->discount_percent = $enquiry->company->discount_percent;
        }
        $enquiry->recalculate();
    }

    /** Staff clicks "Update & Send Quotation" */
    public static function issueQuotation(Enquiry $enquiry, User $staff): void
    {
        $revised = (bool) $enquiry->quotation_number;

        DB::transaction(function () use ($enquiry, $staff) {
            $enquiry->recalculate();
            $enquiry->forceFill([
                'quotation_number' => $enquiry->quotation_number ?: Numbering::quotation(),
                'quotation_date' => now()->toDateString(),
                'quotation_valid_until' => now()->addDays((int) Setting::get('quotation_valid_days', 90))->toDateString(),
                'status' => 'quoted',
                'quoted_at' => now(),
                'priced_by' => $staff->id,
            ])->save();
        });

        self::track($enquiry, 'quotation_issued', $revised ? 'Revised quotation issued.' : null, $staff, false);
        Notifier::send($enquiry->email, new QuotationIssuedMail($enquiry->fresh(), $revised));
    }

    /* =========================================================
     | 3. Customer responds
     * ========================================================= */
    public static function approve(Enquiry $enquiry, array $data): void
    {
        DB::transaction(function () use ($enquiry, $data) {
            $enquiry->forceFill([
                'status' => 'approved',
                'responded_at' => now(),
                'approved_by_name' => $data['approved_by_name'] ?? $enquiry->contact_person,
                'po_number' => $data['po_number'] ?? null,
                'po_file' => $data['po_file'] ?? $enquiry->po_file,
                'ssf_number' => $enquiry->ssf_number ?: Numbering::ssf(),
                'ssf_generated_at' => now(),
            ])->save();
        });

        self::track($enquiry, 'quotation_approved', 'Approved by '.($enquiry->approved_by_name).($enquiry->po_number ? ' · PO '.$enquiry->po_number : ''), null, false);
        self::track($enquiry, 'awaiting_samples', 'Sample Submission Form '.$enquiry->ssf_number.' generated.', null, false);

        $enquiry = $enquiry->fresh();
        Notifier::send($enquiry->email, new SampleSubmissionFormMail($enquiry));
        Notifier::admins(new QuotationResponseAdminMail($enquiry, true));
    }

    /** Declined = end of the process, nothing further can be done on this job */
    public static function decline(Enquiry $enquiry, ?string $reason): void
    {
        $enquiry->forceFill([
            'status' => 'declined',
            'responded_at' => now(),
            'decline_reason' => $reason,
        ])->save();

        self::track($enquiry, 'quotation_declined', $reason ? 'Reason: '.$reason : null, null, true);
        Notifier::admins(new QuotationResponseAdminMail($enquiry->fresh(), false));
    }

    /* =========================================================
     | 4. Tracking updates (pre-described statuses)
     * ========================================================= */
    public static function track(Enquiry $enquiry, TrackingStatus|string $status, ?string $remarks = null, ?User $user = null, ?bool $notify = null, bool $visible = true): TrackingUpdate
    {
        $statusModel = $status instanceof TrackingStatus ? $status : TrackingStatus::byCode($status);
        $label = $statusModel?->label ?? Str::headline((string) $status);

        $update = $enquiry->trackingUpdates()->create([
            'tracking_status_id' => $statusModel?->id,
            'label' => $label,
            'remarks' => $remarks,
            'visible_to_customer' => $visible,
            'user_id' => $user?->id,
        ]);

        if ($statusModel && $statusModel->sets_status) {
            $changes = ['status' => $statusModel->sets_status];
            if ($statusModel->sets_status === 'sample_received' && ! $enquiry->samples_received_at) {
                $changes['samples_received_at'] = now();
            }
            if ($statusModel->sets_status === 'completed' && ! $enquiry->completed_at) {
                $changes['completed_at'] = now();
            }
            $enquiry->forceFill($changes)->save();

            if ($statusModel->sets_status === 'sample_received') {
                self::assignLabCodes($enquiry);
            }
        }

        $shouldNotify = $notify ?? ($statusModel?->notify_customer ?? false);
        if ($shouldNotify && $visible) {
            $sent = Notifier::send($enquiry->email, new TrackingUpdateMail($enquiry->fresh(), $update));
            $update->forceFill(['customer_notified' => $sent])->save();
        }

        return $update;
    }

    /** Give every received sample a laboratory code (AJ12926, AJ12927 …) */
    public static function assignLabCodes(Enquiry $enquiry): void
    {
        foreach ($enquiry->samples()->whereNull('lab_code')->get() as $sample) {
            $sample->update(['lab_code' => Numbering::labCode()]);
        }
    }

    /* =========================================================
     | 5. Invoice
     * ========================================================= */
    public static function createInvoice(Enquiry $enquiry, User $staff): Invoice
    {
        $enquiry->loadMissing('items', 'samples');

        return DB::transaction(function () use ($enquiry, $staff) {
            $billTo = $enquiry->invoiceAddress();
            $isLocal = str_contains(strtolower((string) $enquiry->country), 'singapore');
            $codes = $enquiry->samples->pluck('lab_code')->filter()->values();
            $hasUrgent = $enquiry->items->contains(fn ($i) => $i->urgent);
            $mixed = $hasUrgent && $enquiry->items->contains(fn ($i) => ! $i->urgent);

            $invoice = Invoice::create([
                'enquiry_id' => $enquiry->id,
                'invoice_number' => Numbering::invoice(),
                'invoice_date' => now()->toDateString(),
                'status' => 'draft',
                'currency' => $enquiry->currency,
                'payment_terms' => $enquiry->payment_terms ?: Setting::get('default_payment_terms'),
                'po_number' => $enquiry->po_number,
                'our_ref' => trim($enquiry->ssf_number.' '.($codes->count() > 1 ? $codes->first().' - '.$codes->last() : $codes->first())),
                'bill_to' => trim(implode("\n", array_filter([$billTo['company'], $billTo['address'], $billTo['name']]))),
                // Discount applies to standard tests only. With a mix of standard and urgent tests the
                // discount is shown as its own line so it is not applied to urgent lines.
                'discount_percent' => $hasUrgent ? 0 : $enquiry->discount_percent,
                'gst_percent' => $isLocal ? (float) Setting::get('gst_percent', 9) : 0,
                'created_by' => $staff->id,
            ]);

            $order = 0;
            foreach ($enquiry->items as $item) {
                $invoice->items()->create([
                    'description' => $item->display_name.($item->urgent ? ' – URGENT' : ''),
                    'quantity' => $item->quantity,
                    'rate' => $item->unit_price,
                    'amount' => $item->amount,
                    'sort_order' => $order++,
                ]);
            }
            $pct = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
            if ($hasUrgent && (float) $enquiry->surcharge_amount > 0) {
                $invoice->items()->create([
                    'description' => 'Urgent service surcharge ('.$pct($enquiry->surcharge_percent).'% on urgent samples)',
                    'quantity' => 1,
                    'rate' => $enquiry->surcharge_amount,
                    'amount' => $enquiry->surcharge_amount,
                    'sort_order' => $order++,
                ]);
            }
            if ($mixed && (float) $enquiry->discount_amount > 0) {
                $invoice->items()->create([
                    'description' => 'Less discount ('.$pct($enquiry->discount_percent).'% on standard samples)',
                    'quantity' => 1,
                    'rate' => -1 * (float) $enquiry->discount_amount,
                    'amount' => -1 * (float) $enquiry->discount_amount,
                    'sort_order' => $order++,
                ]);
            }


            $invoice->recalculate();

            return $invoice;
        });
    }

    public static function issueInvoice(Invoice $invoice, User $staff): void
    {
        $invoice->recalculate();
        $invoice->forceFill(['status' => 'issued', 'issued_at' => now()])->save();
        $enquiry = $invoice->enquiry;

        self::track($enquiry, 'invoice_issued', 'Invoice '.$invoice->invoice_number, $staff, false);
        Notifier::send($enquiry->email, new InvoiceIssuedMail($invoice->fresh(['items', 'enquiry'])));
    }

    /* =========================================================
     | 6. Certificate of Analysis
     * ========================================================= */
    public static function coaFor(Enquiry $enquiry): CoaReport
    {
        return $enquiry->coa ?: $enquiry->coa()->create([
            'report_number' => $enquiry->ssf_number ?: $enquiry->reference,
            'report_date' => now()->toDateString(),
            'samples_received_date' => optional($enquiry->samples_received_at)->toDateString(),
            'contact_name' => $enquiry->contact_person,
            'signatory_name' => Setting::get('coa_signatory_name'),
            'signatory_title' => Setting::get('coa_signatory_title'),
            'status' => 'draft',
        ]);
    }

    public static function releaseCoa(Enquiry $enquiry, User $staff): void
    {
        $coa = self::coaFor($enquiry);
        $coa->forceFill(['status' => 'released', 'released_at' => now()])->save();

        self::track($enquiry, 'report_issued', 'Certificate of Analysis '.$coa->report_number, $staff, false);
        Notifier::send($enquiry->email, new CoaReleasedMail($enquiry->fresh(['coa', 'samples.items'])));
    }
}
