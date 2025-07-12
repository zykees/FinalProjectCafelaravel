<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Bangkok');

        $promotions = [
            [
                'title' => 'ส่วนลดประจำวัน',
                'description' => 'ลด 10% สำหรับเครื่องดื่มทุกชนิด',
                'activity_details' => 'รับส่วนลด 10% สำหรับเครื่องดื่มทุกเมนูในร้าน',
                'max_participants' => 100,
                'current_participants' => 0,
                'price_per_person' => 50.00,
                'discount' => 10.00, // เปอร์เซ็นต์
                'starts_at' => $now->copy()->startOfDay(),
                'ends_at' => $now->copy()->addDays(7)->endOfDay(),
                'location' => 'ร้าน Project Cafe',
                'included_items' => 'เครื่องดื่มทุกชนิด',
                'status' => 'active',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'title' => 'ซื้อครบ 500 ลด 100',
                'description' => 'ส่วนลด 100 บาท เมื่อซื้อครบ 500 บาท',
                'activity_details' => 'เมื่อซื้อสินค้าครบ 500 บาท รับส่วนลดทันที 100 บาท',
                'max_participants' => 50,
                'current_participants' => 0,
                'price_per_person' => 500.00,
                'discount' => 20.00, // เปอร์เซ็นต์
                'starts_at' => $now->copy()->startOfDay(),
                'ends_at' => $now->copy()->addMonth()->endOfDay(),
                'location' => 'ร้าน Project Cafe',
                'included_items' => 'สินค้าทุกประเภท',
                'status' => 'active',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'title' => 'โปรโมชั่นสมาชิกใหม่',
                'description' => 'ส่วนลด 15% สำหรับสมาชิกใหม่',
                'activity_details' => 'สมาชิกใหม่รับส่วนลด 15% สำหรับการซื้อครั้งแรก',
                'max_participants' => 1,
                'current_participants' => 0,
                'price_per_person' => 200.00,
                'discount' => 15.00, // เปอร์เซ็นต์
                'starts_at' => $now->copy()->startOfDay(),
                'ends_at' => $now->copy()->addMonths(3)->endOfDay(),
                'location' => 'ร้าน Project Cafe',
                'included_items' => 'ทุกเมนู',
                'status' => 'active',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        foreach ($promotions as $promotion) {
            Promotion::create($promotion);
        }
    }
}