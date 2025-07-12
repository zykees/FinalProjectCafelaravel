<?php


namespace App\Notifications;

use App\Models\PromotionBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingStatusChanged extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct(PromotionBooking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('สถานะการจองเปลี่ยนแปลง')
            ->greeting('สวัสดี ' . $notifiable->name)
            ->line('สถานะการจอง #' . $this->booking->booking_code . ' ได้เปลี่ยนเป็น: ' . __('bookings.status.' . $this->booking->status))
            ->line('กิจกรรม: ' . $this->booking->promotion->title)
            ->line('วันที่: ' . $this->booking->activity_date->format('d/m/Y'))
            ->action('ดูรายละเอียด', route('user.promotion-bookings.show', $this->booking))
            ->line('ขอบคุณที่ใช้บริการ');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'message' => 'สถานะการจอง #' . $this->booking->booking_code . ' เปลี่ยนเป็น: ' . __('bookings.status.' . $this->booking->status)
        ];
    }
}