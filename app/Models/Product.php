<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'category_name', // ใช้แค่ฟิลด์นี้
        'status',
        'minimum_stock',
        'discount_percent',
        'featured'
    ];

    protected $attributes = [
        'status' => 'available',
        'stock' => 0,
        'minimum_stock' => 5,
        'featured' => false
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Status Constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_OUT_OF_STOCK = 'out_of_stock';

    // Relationships (ลบ category ออก)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    // Scopes (ลบ scopeByCategory ออก)
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                    ->where('stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock')
                    ->where('stock', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByPriceRange($query, $min, $max)
    {
        return $query->when($min, fn($q) => $q->where('price', '>=', $min))
                    ->when($max, fn($q) => $q->where('price', '<=', $max));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category_name', 'like', "%{$search}%");
        });
    }

    // Accessors & Mutators
    public function getStatusColorAttribute(): string
    {
        return [
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_UNAVAILABLE => 'danger',
            self::STATUS_OUT_OF_STOCK => 'warning'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute(): string
    {
        return [
            self::STATUS_AVAILABLE => 'พร้อมขาย',
            self::STATUS_UNAVAILABLE => 'ไม่พร้อมขาย',
            self::STATUS_OUT_OF_STOCK => 'สินค้าหมด'
        ][$this->status] ?? 'ไม่ระบุ';
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image 
            ? asset('storage/' . $this->image) 
            : asset('images/no-image.png');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock && $this->stock > 0;
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function updateStock(int $quantity, string $action = 'remove'): bool
    {
        if ($action === 'add') {
            $this->increment('stock', $quantity);
            return true;
        }

        if ($action === 'remove' && $this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            return true;
        }

        return false;
    }

    public function toggleStatus(): bool
    {
        $this->status = $this->status === self::STATUS_AVAILABLE 
            ? self::STATUS_UNAVAILABLE 
            : self::STATUS_AVAILABLE;
        return $this->save();
    }

    public function toggleFeatured(): bool
    {
        $this->featured = !$this->featured;
        return $this->save();
    }

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if ($product->stock <= 0) {
                $product->status = self::STATUS_OUT_OF_STOCK;
            }
        });
    }

    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percent > 0) {
            return round($this->price * (1 - $this->discount_percent / 100), 2);
        }
        return $this->price;
    }

    public function getFormattedDiscountedPriceAttribute()
    {
        return number_format($this->discounted_price, 2);
    }
}