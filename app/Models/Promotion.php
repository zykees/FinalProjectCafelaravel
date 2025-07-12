<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
class Promotion extends Model
{
    use SoftDeletes;

   protected $fillable = [
        'title',
        'description',
        'image',
        'activity_details',
        'max_participants',
        'current_participants',
        'price_per_person',
        'discount',
        'starts_at',
        'ends_at',
        'location',
        'included_items',
        'status',
        'is_featured'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price_per_person' => 'decimal:2',
        'discount' => 'decimal:2',
        'max_participants' => 'integer',
        'current_participants' => 'integer'
    ];
    
    public function promotionBookings()
{
    return $this->hasMany(\App\Models\PromotionBooking::class, 'promotion_id');
}
        public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active')
              ->where('starts_at', '<=', now())
              ->where('ends_at', '>=', now());
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'promotion_user')
                    ->withTimestamps();
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->status === 'active' &&
               $this->starts_at <= $now &&
               $this->ends_at >= $now &&
               (!$this->max_uses || $this->used_count < $this->max_uses) &&
               (!$this->max_users || $this->users()->count() < $this->max_users);
    }

    public function isAvailableForUser(User $user): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // ตรวจสอบว่าผู้ใช้เคยใช้โปรโมชั่นนี้หรือไม่
        if ($this->users()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }

    public function getRemainingUsesAttribute()
    {
        if (!$this->max_uses) {
            return 'Unlimited';
        }
        return max(0, $this->max_uses - $this->used_count);
    }

    public function getRemainingUsersAttribute()
    {
        if (!$this->max_users) {
            return 'Unlimited';
        }
        return max(0, $this->max_users - $this->users()->count());
    }

    public function getUsagePercentAttribute()
    {
        if (!$this->max_uses) {
            return 0;
        }
        return ($this->used_count / $this->max_uses) * 100;
    }
    public function getCreatedAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}

public function getUpdatedAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}

public function getStartsAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}

public function getEndsAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}
 // ความสัมพันธ์กับการจอง
    public function bookings()
{
    return $this->hasMany(\App\Models\PromotionBooking::class, 'promotion_id');
}

    // เช็คว่ายังรับจองได้อยู่ไหม
    public function isAvailableForBooking(): bool
    {
        return !$this->isExpired() && 
               $this->status === 'active' && 
               $this->starts_at->isFuture() && 
               $this->current_participants < $this->max_participants;
    }

    // คำนวณจำนวนที่นั่งที่เหลือ
    public function getRemainingSlots(): int
    {
        return max(0, $this->max_participants - $this->current_participants);
    }

    // คำนวณราคาหลังหักส่วนลด
    public function calculatePrice(int $participants): array
    {
        $totalPrice = $this->price_per_person * $participants;
        $discountAmount = 0;

        if ($this->discount > 0) {
            $discountAmount = ($totalPrice * $this->discount) / 100;
        }

        return [
            'total_price' => $totalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $totalPrice - $discountAmount
        ];
    }
     public function isExpired(): bool
    {
        return $this->ends_at->isPast() || 
               $this->status === 'inactive' || 
               $this->current_participants >= $this->max_participants;
    }
    public function hasAvailableSlots(): bool
    {
        return $this->current_participants < $this->max_participants;
    }
    public function calculateDiscount($participants)
{
    if ($this->discount <= 0) {
        return 0;
    }
    
    $totalPrice = $this->price_per_person * $participants;
    return ($totalPrice * $this->discount) / 100;
}
 public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return null;
    }
}