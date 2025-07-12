<?php

namespace App\Notifications;

use App\Models\PromotionBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentStatusChanged extends Notification
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
            ->subject('สถานะการชำระเงินเปลี่ยนแปลง')
            ->greeting('สวัสดี ' . $notifiable->name)
            ->line('สถานะการชำระเงินของการจอง #' . $this->booking->booking_code . ' ได้เปลี่ยนเป็น: ' . __('bookings.payment_status.' . $this->booking->payment_status))
            ->line('กิจกรรม: ' . $this->booking->promotion->title)
            ->line('ยอดชำระ: ฿' . number_format($this->booking->final_price, 2))
            ->action('ดูรายละเอียด', route('user.promotion-bookings.show', $this->booking))
            ->line('ขอบคุณที่ใช้บริการ');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'payment_status' => $this->booking->payment_status,
            'message' => 'สถานะการชำระเงินของการจอง #' . $this->booking->booking_code . ' เปลี่ยนเป็น: ' . __('bookings.payment_status.' . $this->booking->payment_status)
        ];
    }
}