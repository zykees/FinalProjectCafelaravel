<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Status Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Default permissions for admin
    public function canManageUsers(): bool
    {
        return $this->isActive();
    }

    public function canManageProducts(): bool
    {
        return $this->isActive();
    }

    public function canManageOrders(): bool
    {
        return $this->isActive();
    }

    public function canManageBookings(): bool
    {
        return $this->isActive();
    }

    public function canManagePromotions(): bool
    {
        return $this->isActive();
    }

    public function canViewReports(): bool
    {
        return $this->isActive();
    }
}