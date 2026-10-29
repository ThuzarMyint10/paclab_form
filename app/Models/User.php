<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Lab Staff',
        'customer' => 'Customer',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'company_id', 'company_name', 'phone', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'staff'], true);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'customer_id');
    }

    public function sendPasswordResetNotification($token)
    {
        \App\Services\Notifier::send($this->email, new \App\Mail\PasswordLinkMail($this, $token, false));
    }
}
