<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Barryvdh\DomPDF\Facade\Pdf;

class PromotionBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'booking_code',
        'number_of_participants',
        'activity_date',
        'activity_end_date',
        'activity_time',
        'note',
        'status',
        'total_price',
        'discount_amount',
        'final_price',
        'payment_slip',
        'payment_status',
        'payment_date',
        'seats',
        'total_amount',
        'payment_amount',
        'admin_comment',
        'note',
    'payment_date',
    'payment_amount',
    'payment_slip',
    'status'
    ];

protected $casts = [
    'activity_date' => 'date',
    'activity_time' => 'datetime',
    'total_price' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'final_price' => 'decimal:2',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'payment_date' => 'datetime'
    

];  

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

  public function promotion()
{
    return $this->belongsTo(Promotion::class);
}

    // Accessors
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'info',
            'no_show' => 'secondary'
        ][$this->status] ?? 'secondary';
    }

    public function getPaymentStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'paid' => 'success',
            'rejected' => 'danger',
            'refunded' => 'info'
        ][$this->payment_status] ?? 'secondary';
    }

    // Methods
    public function generateQuotation()
    {
        $pdf = PDF::loadView('user.promotion-bookings.quotation', [
            'booking' => $this,
            'user' => $this->user,
            'promotion' => $this->promotion
        ]);

        return $pdf->download('quotation-'.$this->booking_code.'.pdf');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return $this->status === 'pending' || $this->status === 'confirmed';
    }
}