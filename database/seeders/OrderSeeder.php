<?php


namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $promotions = Promotion::all();

        $shipping_names = ['สมชาย ใจดี', 'สมหญิง รักดี', 'John Doe', 'Jane Smith'];
        $shipping_phones = ['0812345678', '0898765432', '0999999999', '0888888888'];
        $shipping_addresses = [
            '123/4 ถนนสุขใจ แขวงสุขสันต์ เขตสุขุม กรุงเทพฯ',
            '456/7 หมู่บ้านร่มเย็น ต.กลางเมือง อ.เมือง จ.เชียงใหม่',
            '789/10 ซอยสุขสบาย ต.ในเมือง อ.เมือง จ.ขอนแก่น',
            '11/22 หมู่ 5 ต.บางรัก อ.เมือง จ.ชลบุรี'
        ];
        $payment_methods = ['cash', 'credit_card', 'bank_transfer', 'promptpay'];
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $payment_statuses = ['pending', 'paid', 'failed'];

        foreach (range(1, 50) as $index) {
            $orderDate = Carbon::now('Asia/Bangkok')->subDays(rand(0, 30));
            $user = $users->random();
            $promotion = $promotions->isNotEmpty() && rand(0, 1) ? $promotions->random() : null;

            $items = rand(1, 5);
            $total = 0;

            $order = Order::create([
                'user_id' => $user->id,
                'promotion_id' => $promotion?->id,
                'order_code' => strtoupper(Str::random(10)),
                'total_amount' => 0, // จะอัปเดตทีหลัง
                'shipping_name' => $shipping_names[array_rand($shipping_names)],
                'shipping_address' => $shipping_addresses[array_rand($shipping_addresses)],
                'shipping_phone' => $shipping_phones[array_rand($shipping_phones)],
                'payment_method' => $payment_methods[array_rand($payment_methods)],
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'หมายเหตุสำหรับออเดอร์',
                'payment_status' => $payment_statuses[array_rand($payment_statuses)],
                'payment_slip' => null,
                'payment_date' => null,
                'payment_amount' => null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate
            ]);

            // สร้างรายการสินค้าในออเดอร์
            for ($i = 0; $i < $items; $i++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $price = $product->price;
                $subtotal = $price * $quantity;
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    // 'subtotal' => $subtotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate
                ]);
            }

            // อัปเดตยอดรวมของออเดอร์
            $order->update([
                'total_amount' => $total,
                'payment_amount' => $order->payment_status === 'paid' ? $total : null
            ]);
        }
    }
}