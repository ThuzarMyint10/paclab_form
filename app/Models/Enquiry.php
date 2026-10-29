<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * One enquiry = one job: enquiry → quotation → approval → sample submission → testing → report → invoice.
 */
class Enquiry extends Model
{
    public const STATUSES = [
        'submitted' => 'Enquiry Received',
        'quoted' => 'Quotation Sent',
        'approved' => 'Approved – Awaiting Samples',
        'declined' => 'Quotation Declined',
        'sample_received' => 'Sample Received',
        'in_progress' => 'Testing In Progress',
        'completed' => 'Testing Completed',
        'reported' => 'Report (COA) Issued',
        'dispatched' => 'Sample Sent / Dispatched',
        'on_hold' => 'On Hold',
        'closed' => 'Job Closed',
        'cancelled' => 'Cancelled',
    ];

    /** Bootstrap colour for status badges */
    public const STATUS_COLOURS = [
        'submitted' => 'secondary',
        'quoted' => 'info',
        'approved' => 'primary',
        'declined' => 'danger',
        'sample_received' => 'warning',
        'in_progress' => 'warning',
        'completed' => 'success',
        'reported' => 'success',
        'dispatched' => 'success',
        'on_hold' => 'dark',
        'closed' => 'dark',
        'cancelled' => 'danger',
    ];

    /** Customer-facing progress bar */
    public const PROGRESS_STEPS = [
        'submitted' => 'Enquiry',
        'quoted' => 'Quotation',
        'approved' => 'Approved',
        'sample_received' => 'Sample Received',
        'in_progress' => 'Testing',
        'completed' => 'Completed',
        'reported' => 'Report Issued',
    ];

    protected $fillable = [
        'reference', 'access_token', 'customer_id', 'company_id', 'status',
        'company_name', 'contact_person', 'email', 'phone', 'mobile', 'fax', 'address', 'country', 'currency', 'turnaround',
        'special_instructions', 'report_name', 'report_company', 'report_address', 'invoice_name', 'invoice_company', 'invoice_address',
        'quotation_number', 'quotation_date', 'quotation_valid_until', 'subtotal', 'surcharge_percent', 'surcharge_amount',
        'discount_percent', 'discount_amount', 'total', 'payment_terms', 'quotation_notes', 'internal_notes', 'priced_by', 'quoted_at',
        'responded_at', 'decline_reason', 'po_number', 'po_file', 'approved_by_name',
        'ssf_number', 'ssf_generated_at', 'courier_name', 'courier_tracking_no', 'samples_received_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'quotation_valid_until' => 'date',
            'quoted_at' => 'datetime',
            'responded_at' => 'datetime',
            'ssf_generated_at' => 'datetime',
            'samples_received_at' => 'datetime',
            'completed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'surcharge_percent' => 'decimal:2',
            'surcharge_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /* ---------------- Relations ---------------- */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pricedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'priced_by');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(EnquirySample::class)->orderBy('sort_order')->orderBy('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EnquiryItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(TrackingUpdate::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('id');
    }

    public function coa(): HasOne
    {
        return $this->hasOne(CoaReport::class);
    }

    /* ---------------- Status helpers ---------------- */

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusColour(): string
    {
        return self::STATUS_COLOURS[$this->status] ?? 'secondary';
    }

    /** Prices may be calculated / amended until the customer has responded */
    public function canEditPricing(): bool
    {
        return in_array($this->status, ['submitted', 'quoted'], true);
    }

    /** Customer may approve / decline */
    public function canRespond(): bool
    {
        return $this->status === 'quoted'
            && (! $this->quotation_valid_until || $this->quotation_valid_until->endOfDay()->isFuture());
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /** Tracking updates, SSF, invoice and COA are only possible once the quotation is approved */
    public function isApprovedJob(): bool
    {
        return ! in_array($this->status, ['submitted', 'quoted', 'declined', 'cancelled'], true);
    }

    public function isUrgent(): bool
    {
        return $this->turnaround === 'urgent';
    }

    /** Index of the current step in the customer progress bar (-1 = declined/cancelled) */
    public function progressIndex(): int
    {
        if (in_array($this->status, ['declined', 'cancelled'], true)) {
            return -1;
        }
        if (in_array($this->status, ['dispatched', 'closed'], true)) {
            return count(self::PROGRESS_STEPS) - 1;
        }
        if ($this->status === 'on_hold') {
            $status = $this->samples_received_at ? 'sample_received' : 'approved';
        } else {
            $status = $this->status;
        }
        $index = array_search($status, array_keys(self::PROGRESS_STEPS), true);

        return $index === false ? 0 : $index;
    }

    /* ---------------- Pricing ---------------- */

    /**
     * Turnaround is chosen per sample; every test line follows its sample.
     * subtotal = Σ(unit price × qty)
     * urgent lines   → + surcharge %, no discount
     * standard lines → − discount %
     */
    public function recalculate(): void
    {
        $this->load('items');

        foreach ($this->items as $item) {
            $amount = round((float) $item->unit_price * max(1, (int) $item->quantity), 2);
            if ((float) $item->amount !== $amount) {
                $item->forceFill(['amount' => $amount])->save();
            }
        }

        $subtotal = round($this->items->sum(fn ($i) => (float) $i->amount), 2);
        $urgentBase = round($this->items->where('urgent', true)->sum(fn ($i) => (float) $i->amount), 2);
        $standardBase = round($subtotal - $urgentBase, 2);
        $hasUrgent = $this->items->contains(fn ($i) => $i->urgent);

        $surchargePct = $hasUrgent ? (float) Setting::get('urgent_surcharge_percent', 50) : 0;
        $this->surcharge_percent = $surchargePct;
        $this->surcharge_amount = round($urgentBase * $surchargePct / 100, 2);
        $this->discount_amount = round($standardBase * (float) $this->discount_percent / 100, 2);
        $this->turnaround = $hasUrgent ? 'urgent' : 'standard';

        $this->subtotal = $subtotal;
        $this->total = round($subtotal + (float) $this->surcharge_amount - (float) $this->discount_amount, 2);
        $this->save();
    }

    /** "Standard", "Urgent" or "Mixed (1 urgent sample)" */
    public function turnaroundLabel(): string
    {
        $samples = $this->relationLoaded('samples') ? $this->samples : $this->samples()->get();
        $urgent = $samples->where('urgent', true)->count();
        if ($urgent === 0) {
            return 'Standard';
        }

        return $urgent === $samples->count() ? 'Urgent' : 'Mixed ('.$urgent.' urgent sample'.($urgent > 1 ? 's' : '').')';
    }

    public function currencySymbol(): string
    {
        return self::symbolFor($this->currency);
    }

    public static function symbolFor(?string $currency): string
    {
        return strtoupper((string) $currency) === 'USD' ? 'US$' : 'S$';
    }

    public function money($amount): string
    {
        return $this->currencySymbol().number_format((float) $amount, 2);
    }

    /* ---------------- Addresses ---------------- */

    public function reportingAddress(): array
    {
        return [
            'name' => $this->report_name ?: $this->contact_person,
            'company' => $this->report_company ?: $this->company_name,
            'address' => $this->report_address ?: $this->address,
        ];
    }

    public function invoiceAddress(): array
    {
        return [
            'name' => $this->invoice_name ?: $this->contact_person,
            'company' => $this->invoice_company ?: $this->company_name,
            'address' => $this->invoice_address ?: $this->address,
        ];
    }

    public function totalSamples(): int
    {
        return (int) $this->samples->sum('quantity');
    }

    public function latestInvoice(): ?Invoice
    {
        return $this->invoices->first();
    }

    /** Secure public link that lets the customer view/approve without logging in */
    public function publicUrl(): string
    {
        return route('quotation.public', ['token' => $this->access_token]);
    }
}
