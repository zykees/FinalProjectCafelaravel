<?php

namespace Database\Seeders;

use App\Models\PromotionBooking;
use App\Models\User;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PromotionBookingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $promotions = Promotion::all();
        $statuses = ['pending', 'confirmed', 'cancelled'];
        $payment_statuses = ['pending', 'paid', 'rejected'];

        foreach (range(1, 30) as $index) {
            $user = $users->random();
            $promotion = $promotions->random();

            $participants = rand(1, 6);
            $activity_date = Carbon::now('Asia/Bangkok')->addDays(rand(1, 30));
            $activity_time = sprintf('%02d:00:00', rand(9, 18));

            // คำนวณราคาและส่วนลด
            $total_price = $promotion->price_per_person * $participants;
            $discount = $promotion->discount ?? 0;
            $discount_amount = round($total_price * ($discount / 100), 2);
            $final_price = $total_price - $discount_amount;

            PromotionBooking::create([
                'user_id' => $user->id,
                'promotion_id' => $promotion->id,
                'booking_code' => strtoupper(Str::random(8)),
                'number_of_participants' => $participants,
                'total_price' => $total_price,
                'discount_amount' => $discount_amount,
                'final_price' => $final_price,
                'activity_date' => $activity_date->format('Y-m-d'),
                'activity_time' => $activity_time,
                'note' => 'Test booking notes',
                'payment_slip' => null,
                'payment_status' => $payment_statuses[array_rand($payment_statuses)],
                'status' => $statuses[array_rand($statuses)],
                'admin_comment' => null,
                'created_at' => Carbon::now('Asia/Bangkok')->subDays(rand(0, 30)),
                'updated_at' => Carbon::now('Asia/Bangkok')->subDays(rand(0, 30)),
            ]);
        }
    }
}