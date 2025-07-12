<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    protected $fillable = [
        'report_type',
        'start_date',
        'end_date', 
        'generated_by',
        'data'
    ];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
        'data' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    const TYPE_SALES = 'sales';
    const TYPE_BOOKINGS = 'bookings';
    const TYPE_PROMOTIONS = 'promotions';

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'generated_by');
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Bangkok') : null;
    }

    public function getFormattedDateRangeAttribute()
    {
        return $this->start_date->format('Y-m-d') . ' - ' . $this->end_date->format('Y-m-d');
    }
}