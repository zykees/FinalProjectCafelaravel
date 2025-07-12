<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'user_id',
    'order_code',
    'total_amount',
    'shipping_name',
    'shipping_address',
    'shipping_phone',
    'payment_method',
    'payment_slip',
    'payment_date',
    'payment_amount',
    'payment_status',
    'status'
];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment Status Constants
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';

    protected $casts = [
        'payment_date' => 'datetime',
        'total_amount' => 'float',
        'discount_amount' => 'float',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    // Helper Methods
    public function calculateTotal()
    {
        $subtotal = $this->items->sum(function($item) {
            return $item->quantity * $item->price;
        });

        $this->total_amount = $subtotal - ($this->discount_amount ?? 0);
        return $this->total_amount;
    }

    public function getStatusColor(): string
    {
        return [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getPaymentStatusColor(): string
    {
        return [
            self::PAYMENT_PENDING => 'warning',
            self::PAYMENT_PAID => 'success',
            self::PAYMENT_FAILED => 'danger'
        ][$this->payment_status] ?? 'secondary';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    // Mutators & Accessors
    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedDiscountAttribute()
    {
        return number_format($this->discount_amount, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_code = 'ORD' . time() . rand(1000, 9999);
        });
    }
     // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
     public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return [
            'pending' => 'รอดำเนินการ',
            'processing' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก'
        ][$this->status] ?? 'ไม่ระบุ';
    }

    public function getPaymentStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger'
        ][$this->payment_status] ?? 'secondary';
    }
    public function getCalculatedTotalAttribute()
{
    return $this->items->sum(function($item) {
        $discountPercent = $item->product->discount_percent ?? 0;
        $originalPrice = $item->product->price;
        $discountedPrice = $discountPercent > 0
            ? round($originalPrice * (1 - $discountPercent/100), 2)
            : $originalPrice;
        return $discountedPrice * $item->quantity;
    });
}

public function getCalculatedDiscountAttribute()
{
    return $this->items->sum(function($item) {
        $discountPercent = $item->product->discount_percent ?? 0;
        $originalPrice = $item->product->price;
        $discountedPrice = $discountPercent > 0
            ? round($originalPrice * (1 - $discountPercent/100), 2)
            : $originalPrice;
        return ($originalPrice - $discountedPrice) * $item->quantity;
    });
}

public function getCalculatedSubtotalAttribute()
{
    return $this->items->sum(function($item) {
        return $item->product->price * $item->quantity;
    });
}
}