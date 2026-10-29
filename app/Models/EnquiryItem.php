<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnquiryItem extends Model
{
    protected $fillable = [
        'enquiry_id', 'enquiry_sample_id', 'lab_test_id', 'test_name', 'matrix', 'method',
        'list_price', 'unit_price', 'quantity', 'urgent', 'amount', 'result_value', 'result_unit', 'result_method', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'list_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'urgent' => 'boolean',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(EnquirySample::class, 'enquiry_sample_id');
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->method ? "{$this->test_name} ({$this->method})" : $this->test_name;
    }

    public function turnaroundLabel(): string
    {
        return $this->urgent ? 'Urgent' : 'Standard';
    }

    /** True when staff changed the price away from the standard list */
    public function isAmended(): bool
    {
        return round((float) $this->unit_price, 2) !== round((float) $this->list_price, 2);
    }
}
