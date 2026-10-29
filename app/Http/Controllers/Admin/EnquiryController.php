<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EnquiryController as PublicEnquiryController;
use App\Models\Company;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\LabTest;
use App\Models\TrackingStatus;
use App\Services\Documents;
use App\Services\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::query()->withCount('items')->latest();

        if ($status = $request->query('status')) {
            $status === 'active'
                ? $query->whereIn('status', ['approved', 'sample_received', 'in_progress', 'on_hold'])
                : $query->where('status', $status);
        }
        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%$s%")
                ->orWhere('quotation_number', 'like', "%$s%")
                ->orWhere('ssf_number', 'like', "%$s%")
                ->orWhere('company_name', 'like', "%$s%")
                ->orWhere('contact_person', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%"));
        }

        return view('admin.enquiries.index', [
            'enquiries' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['samples.items.labTest', 'items', 'trackingUpdates.user', 'invoices', 'coa', 'company', 'customer', 'pricedBy']);

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'trackingStatuses' => TrackingStatus::where('is_manual', true)->where('is_active', true)->orderBy('sort_order')->get(),
            'companies' => Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'discount_percent', 'payment_terms', 'currency']),
            'testsJson' => PublicEnquiryController::testsForPicker(true),
        ]);
    }

    /** Edit customer details, link to company account master, change currency / turnaround */
    public function updateDetails(Request $request, Enquiry $enquiry)
    {
        abort_if($enquiry->isDeclined(), 403, 'Declined enquiries are closed.');

        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'contact_person' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'country' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'currency' => 'required|in:SGD,USD',
            'special_instructions' => 'nullable|string|max:2000',
            'report_name' => 'nullable|string|max:191',
            'report_company' => 'nullable|string|max:191',
            'report_address' => 'nullable|string|max:1000',
            'invoice_name' => 'nullable|string|max:191',
            'invoice_company' => 'nullable|string|max:191',
            'invoice_address' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:5000',
        ]);

        $currencyChanged = $data['currency'] !== $enquiry->currency;
        $companyChanged = (int) ($data['company_id'] ?? 0) !== (int) $enquiry->company_id;

        if (! $enquiry->canEditPricing()) {
            // Once approved, prices are locked
            unset($data['currency']);
            $currencyChanged = false;
        }

        $enquiry->update($data);
        $enquiry->load('company');

        if ($enquiry->canEditPricing() && $companyChanged && $enquiry->company) {
            $enquiry->forceFill([
                'discount_percent' => $enquiry->company->discount_percent,
                'payment_terms' => $enquiry->company->payment_terms ?: $enquiry->payment_terms,
            ])->save();
            if ($enquiry->customer && ! $enquiry->customer->company_id) {
                $enquiry->customer->forceFill(['company_id' => $enquiry->company_id])->save();
            }
        }

        if ($currencyChanged) {
            Workflow::applyPriceList($enquiry);
        } elseif ($enquiry->canEditPricing()) {
            $enquiry->recalculate();
        }

        return back()->with('success', 'Enquiry details saved.'.($currencyChanged ? ' Prices were re-calculated in '.$enquiry->currency.'.' : ''));
    }

    /** Staff amend unit prices / quantities / discount, optionally send the quotation */
    public function updatePricing(Request $request, Enquiry $enquiry)
    {
        if (! $enquiry->canEditPricing()) {
            return back()->with('error', 'Prices are locked once the customer has responded to the quotation.');
        }

        $data = $request->validate([
            'items' => 'array',
            'items.*.unit_price' => 'required|numeric|min:0|max:9999999',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
            'sample_turnaround' => 'array',
            'sample_turnaround.*' => 'in:standard,urgent',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'quotation_notes' => 'nullable|string|max:3000',
        ]);

        foreach ($data['items'] ?? [] as $id => $row) {
            $item = $enquiry->items()->whereKey($id)->first();
            if ($item) {
                $item->update([
                    'unit_price' => $row['unit_price'],
                    'quantity' => $row['quantity'],
                ]);
            }
        }

        // Turnaround is per sample: every test in the sample follows it
        foreach ($data['sample_turnaround'] ?? [] as $sampleId => $ta) {
            $sample = $enquiry->samples()->whereKey($sampleId)->first();
            if ($sample) {
                $urgent = $ta === 'urgent';
                $sample->update(['urgent' => $urgent]);
                $enquiry->items()->where('enquiry_sample_id', $sample->id)->update(['urgent' => $urgent]);
            }
        }

        $enquiry->forceFill([
            'discount_percent' => $data['discount_percent'],
            'payment_terms' => $data['payment_terms'] ?? $enquiry->payment_terms,
            'quotation_notes' => $data['quotation_notes'] ?? null,
        ]);
        $enquiry->recalculate();

        if ($request->input('action') === 'send') {
            return $this->sendQuotation($request, $enquiry);
        }

        return back()->with('success', 'Prices updated. Total: '.$enquiry->money($enquiry->total));
    }

    public function applyPriceList(Enquiry $enquiry)
    {
        if (! $enquiry->canEditPricing()) {
            return back()->with('error', 'Prices are locked.');
        }
        Workflow::applyPriceList($enquiry);

        return back()->with('success', 'All lines reset to the standard 2026 price list'.($enquiry->company ? ' with the '.$enquiry->company->name.' agreed discount.' : '.'));
    }

    public function addItem(Request $request, Enquiry $enquiry)
    {
        if (! $enquiry->canEditPricing()) {
            return back()->with('error', 'Prices are locked.');
        }

        $data = $request->validate([
            'enquiry_sample_id' => 'required|integer',
            'lab_test_id' => 'nullable|integer|exists:lab_tests,id',
            'test_name' => 'nullable|required_without:lab_test_id|string|max:191',
            'unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1|max:9999',
        ]);

        $sample = $enquiry->samples()->whereKey($data['enquiry_sample_id'])->firstOrFail();
        $test = ! empty($data['lab_test_id']) ? LabTest::find($data['lab_test_id']) : null;
        $list = $test ? $test->priceIn($enquiry->currency) : (float) ($data['unit_price'] ?? 0);

        $enquiry->items()->create([
            'enquiry_sample_id' => $sample->id,
            'lab_test_id' => $test?->id,
            'test_name' => $test?->name ?? $data['test_name'],
            'matrix' => $test?->matrix,
            'method' => $test?->method,
            'list_price' => $list,
            'unit_price' => isset($data['unit_price']) && $data['unit_price'] !== null ? $data['unit_price'] : $list,
            'quantity' => $data['quantity'] ?? $sample->quantity,
            'urgent' => $sample->urgent, // follows the sample's turnaround
            'sort_order' => (int) $enquiry->items()->reorder()->max('sort_order') + 1,
        ]);
        $enquiry->recalculate();

        return back()->with('success', 'Test added.');
    }

    public function removeItem(Enquiry $enquiry, EnquiryItem $item)
    {
        abort_unless((int) $item->enquiry_id === (int) $enquiry->id, 404);
        if (! $enquiry->canEditPricing()) {
            return back()->with('error', 'Prices are locked.');
        }
        $item->delete();
        $enquiry->recalculate();

        return back()->with('success', 'Test removed.');
    }

    public function sendQuotation(Request $request, Enquiry $enquiry)
    {
        if (! $enquiry->canEditPricing()) {
            return back()->with('error', 'This enquiry can no longer be quoted.');
        }
        if ($enquiry->items()->reorder()->count() === 0) {
            return back()->with('error', 'Add at least one test before sending the quotation.');
        }

        Workflow::issueQuotation($enquiry, $request->user());

        return back()->with('success', 'Quotation '.$enquiry->fresh()->quotation_number.' sent to '.$enquiry->email.'.');
    }

    /** Customer replied by email/phone — staff records the approval or decline for them */
    public function recordResponse(Request $request, Enquiry $enquiry)
    {
        if ($enquiry->status !== 'quoted') {
            return back()->with('error', 'Only a quotation that has been sent can be approved or declined.');
        }

        $data = $request->validate([
            'response' => 'required|in:approve,decline',
            'approved_by_name' => 'nullable|string|max:191',
            'po_number' => 'nullable|string|max:100',
            'po_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'decline_reason' => 'nullable|string|max:1000',
        ]);

        if ($data['response'] === 'approve') {
            if ($request->hasFile('po_document')) {
                $data['po_file'] = $request->file('po_document')->store('po-documents/'.$enquiry->id);
            }
            $data['approved_by_name'] = ($data['approved_by_name'] ?? null) ?: $enquiry->contact_person;
            Workflow::approve($enquiry, $data);

            return back()->with('success', 'Approval recorded. Sample Submission Form '.$enquiry->fresh()->ssf_number.' generated and emailed to the customer.');
        }

        Workflow::decline($enquiry, $data['decline_reason'] ?? 'Declined (recorded by staff)');

        return back()->with('success', 'Decline recorded. This job is now closed.');
    }

    /** Staff choose one of the pre-described tracking statuses */
    public function addTracking(Request $request, Enquiry $enquiry)
    {
        if (! $enquiry->isApprovedJob()) {
            return back()->with('error', $enquiry->isDeclined()
                ? 'The customer declined this quotation — no further updates are possible.'
                : 'Tracking starts once the customer approves the quotation.');
        }

        $data = $request->validate([
            'tracking_status_id' => 'required|exists:tracking_statuses,id',
            'remarks' => 'nullable|string|max:2000',
            'courier_name' => 'nullable|string|max:100',
            'courier_tracking_no' => 'nullable|string|max:100',
            'notify' => 'nullable|boolean',
            'internal_only' => 'nullable|boolean',
        ]);

        $status = TrackingStatus::where('is_manual', true)->findOrFail($data['tracking_status_id']);

        $remarks = $data['remarks'] ?? null;
        if (! empty($data['courier_name']) || ! empty($data['courier_tracking_no'])) {
            $enquiry->forceFill(array_filter([
                'courier_name' => $data['courier_name'] ?? null,
                'courier_tracking_no' => $data['courier_tracking_no'] ?? null,
            ]))->save();
            $remarks = trim(($remarks ? $remarks."\n" : '').'Courier: '.($data['courier_name'] ?? '-').' · Tracking no: '.($data['courier_tracking_no'] ?? '-'));
        }

        $visible = ! $request->boolean('internal_only');
        Workflow::track($enquiry, $status, $remarks, $request->user(), $visible && $request->boolean('notify'), $visible);

        return back()->with('success', 'Status updated to "'.$status->label.'".'.($visible && $request->boolean('notify') ? ' Customer notified by email.' : ''));
    }

    public function updateSamples(Request $request, Enquiry $enquiry)
    {
        abort_if($enquiry->isDeclined(), 403);

        $data = $request->validate([
            'samples' => 'required|array',
            'samples.*.description' => 'required|string|max:191',
            'samples.*.sample_type' => 'nullable|string|max:191',
            'samples.*.batch_no' => 'nullable|string|max:100',
            'samples.*.quantity' => 'required|integer|min:1|max:999',
            'samples.*.storage' => 'nullable|string|max:100',
            'samples.*.lab_code' => 'nullable|string|max:50',
        ]);

        foreach ($data['samples'] as $id => $row) {
            \App\Models\EnquirySample::where('enquiry_id', $enquiry->id)->whereKey($id)->update($row);
        }

        if ($request->input('action') === 'assign_codes') {
            Workflow::assignLabCodes($enquiry);
        }

        return back()->with('success', 'Sample details saved.');
    }

    public function document(Enquiry $enquiry, string $doc)
    {
        return match ($doc) {
            'quotation' => Documents::quotation($enquiry)->stream(Documents::fileName('PacLab_Quotation', $enquiry->quotation_number ?: $enquiry->reference.'-DRAFT')),
            'ssf' => $enquiry->ssf_number ? Documents::ssf($enquiry)->stream(Documents::fileName('PacLab_Sample_Submission_Form', $enquiry->ssf_number)) : abort(404),
            'coa' => $enquiry->coa ? Documents::coa($enquiry)->stream(Documents::fileName('PacLab_COA', $enquiry->coa->report_number)) : abort(404),
        };
    }

    public function poFile(Enquiry $enquiry)
    {
        abort_unless($enquiry->po_file && Storage::disk('local')->exists($enquiry->po_file), 404);

        return Storage::disk('local')->download($enquiry->po_file, 'PO_'.($enquiry->po_number ?: $enquiry->reference).'.'.pathinfo($enquiry->po_file, PATHINFO_EXTENSION));
    }
}
