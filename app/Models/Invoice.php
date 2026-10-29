<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'paid' => 'Paid',
        'void' => 'Void',
    ];

    protected $fillable = [
        'enquiry_id', 'invoice_number', 'invoice_date', 'status', 'currency', 'payment_terms', 'po_number', 'our_ref', 'bill_to',
        'total', 'discount_percent', 'discount_amount', 'subtotal', 'gst_percent', 'gst_amount', 'grand_total',
        'notes', 'issued_at', 'paid_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'total' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'gst_percent' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** TOTAL → LESS DISCOUNT → SUB-TOTAL → ADD GST → GRAND TOTAL (same order as the PacLab tax invoice) */
    public function recalculate(): void
    {
        $this->loadMissing('items');
        $total = round($this->items->sum(fn ($i) => (float) $i->amount), 2);
        $discount = round($total * (float) $this->discount_percent / 100, 2);
        $sub = round($total - $discount, 2);
        $gst = round($sub * (float) $this->gst_percent / 100, 2);

        $this->forceFill([
            'total' => $total,
            'discount_amount' => $discount,
            'subtotal' => $sub,
            'gst_amount' => $gst,
            'grand_total' => round($sub + $gst, 2),
        ])->save();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isVisibleToCustomer(): bool
    {
        return in_array($this->status, ['issued', 'paid'], true);
    }
}
