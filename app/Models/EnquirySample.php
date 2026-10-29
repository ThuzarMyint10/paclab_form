<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnquirySample extends Model
{
    protected $fillable = ['enquiry_id', 'description', 'sample_type', 'batch_no', 'production_date', 'quantity', 'storage', 'urgent', 'lab_code', 'sort_order'];

    protected function casts(): array
    {
        return ['production_date' => 'date', 'urgent' => 'boolean'];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EnquiryItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
