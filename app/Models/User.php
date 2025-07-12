<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable
{
     use HasApiTokens, HasFactory, Notifiable, SoftDeletes;


protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'google_id',
    'line_id', // เพิ่ม line_id
    'avatar',
    'social_type',
    'email_verified_at',
    'address'   
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Accessor สำหรับรูปโปรไฟล์
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return $this->avatar;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }


        public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }
    // Orders Relationship
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Bookings Relationship
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function socialAccounts()
{
    return $this->hasMany(SocialAccount::class);
}

// public function notifications()
// {
//     return $this->hasMany(Notification::class);
// }

public function profile()
{
    return $this->hasOne(UserProfile::class);
}
public function promotionBookings()
{
    return $this->hasMany(\App\Models\PromotionBooking::class, 'user_id');
}
public function getLineUserIdAttribute()
{
    return $this->line_id;
}

}