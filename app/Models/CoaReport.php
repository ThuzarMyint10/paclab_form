<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaReport extends Model
{
    protected $fillable = [
        'enquiry_id', 'report_number', 'report_date', 'samples_received_date', 'contact_name',
        'signatory_name', 'signatory_title', 'remarks', 'status', 'released_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'samples_received_date' => 'date',
            'released_at' => 'datetime',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }
}
