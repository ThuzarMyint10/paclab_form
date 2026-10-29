<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer account master (from "PAC LAB EXTERNAL PAYMENT TERMS - DISCOUNTS").
 * Holds each customer's agreed currency, discount and payment terms.
 */
class Company extends Model
{
    protected $fillable = [
        'name', 'country', 'currency', 'discount_percent', 'payment_terms', 'address', 'email', 'phone', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    /** Find a company by a loosely-typed name ("Cargill Feed Sdn Bhd" == "CARGILL FEED SDN BHD ") */
    public static function matchByName(?string $name): ?self
    {
        $name = self::normalise($name);
        if ($name === '') {
            return null;
        }

        $match = static::query()->where('is_active', true)->get(['id', 'name'])
            ->first(fn ($c) => self::normalise($c->name) === $name);

        return $match ? static::find($match->id) : null;
    }

    public static function normalise(?string $name): string
    {
        $name = strtoupper((string) $name);
        $name = str_replace(['.', ',', '(', ')', '&', '-'], ' ', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
