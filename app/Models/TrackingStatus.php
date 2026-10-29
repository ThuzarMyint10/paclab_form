<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingStatus extends Model
{
    protected $fillable = ['code', 'label', 'customer_message', 'sets_status', 'is_manual', 'notify_customer', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_manual' => 'boolean',
            'notify_customer' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
