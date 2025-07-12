<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
     use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'booking_date',
        'booking_time',
        'number_of_guests',
        'status',
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }
    public function getCreatedAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}

public function getUpdatedAtAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}

public function getBookingTimeAttribute($value)
{
    return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
}
}