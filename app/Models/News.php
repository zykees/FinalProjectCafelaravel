<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'image',
        'status',
        'published_at',
        'slug'
    ];

    protected $casts = [
        'published_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    public function getStatusColor(): string
    {
        return [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_ARCHIVED => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getImageUrl(): string
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/no-image.png');
    }
        public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getPublishedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }
}