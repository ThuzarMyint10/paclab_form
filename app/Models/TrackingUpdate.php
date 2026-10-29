<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingUpdate extends Model
{
    protected $fillable = ['enquiry_id', 'tracking_status_id', 'label', 'remarks', 'visible_to_customer', 'customer_notified', 'user_id'];

    protected function casts(): array
    {
        return [
            'visible_to_customer' => 'boolean',
            'customer_notified' => 'boolean',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TrackingStatus::class, 'tracking_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
