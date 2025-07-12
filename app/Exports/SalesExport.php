<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SalesExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders->map(function ($order) {
            return [
                'รหัสออเดอร์' => $order->order_code,
                'วันที่' => $order->created_at->format('d/m/Y H:i'),
                'ลูกค้า' => $order->user->name,
                'จำนวนรายการ' => $order->items->count(),
                'ยอดรวม' => number_format($order->total_amount, 2),
                'ส่วนลด' => number_format($order->discount_amount, 2),
                'ยอดสุทธิ' => number_format($order->final_price, 2),
                'สถานะ' => $order->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระ'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'รหัสออเดอร์',
            'วันที่',
            'ลูกค้า',
            'จำนวนรายการ',
            'ยอดรวม',
            'ส่วนลด',
            'ยอดสุทธิ',
            'สถานะ'
        ];
    }
}