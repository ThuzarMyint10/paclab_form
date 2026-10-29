<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    protected $fillable = ['category', 'name', 'matrix', 'method', 'price_sgd', 'price_usd', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'price_sgd' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function priceIn(string $currency): float
    {
        return (float) (strtoupper($currency) === 'USD' ? $this->price_usd : $this->price_sgd);
    }

    /** "Premix/Feed/Food – HPLC" */
    public function getMatrixMethodAttribute(): string
    {
        return collect([$this->matrix, $this->method])->filter()->implode(' – ');
    }

    /** "Vitamin A (Retinol) (HPLC)" — as printed on quotation/invoice */
    public function getDisplayNameAttribute(): string
    {
        return $this->method ? "{$this->name} ({$this->method})" : $this->name;
    }
}
