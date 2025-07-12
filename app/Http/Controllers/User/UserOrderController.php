<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request; 
class UserOrderController extends Controller
{
       public function index()
    {
        $orders = auth()->user()->orders()
            ->latest()
            ->withCount('items')
            ->paginate(10);

        return view('User.orders.index', compact('orders'));
    }

public function show(Order $order)
{
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    // คำนวณยอดรวม, ส่วนลด, ยอดสุทธิหลังหักส่วนลด
    $orderTotal = 0;
    $orderDiscount = 0;
    $orderFinalTotal = 0;
    foreach ($order->items as $item) {
        $discountPercent = $item->product->discount_percent ?? 0;
        $originalPrice = $item->product->price;
        $discountedPrice = $discountPercent > 0
            ? round($originalPrice * (1 - $discountPercent/100), 2)
            : $originalPrice;
        $itemTotal = $discountedPrice * $item->quantity;
        $itemDiscount = ($originalPrice - $discountedPrice) * $item->quantity;
        $orderTotal += $originalPrice * $item->quantity;
        $orderDiscount += $itemDiscount;
        $orderFinalTotal += $itemTotal;
    }

    return view('User.orders.show', compact('order', 'orderTotal', 'orderDiscount', 'orderFinalTotal'));
}
 // Keep only this one uploadPayment method
    public function uploadPayment(Request $request, Order $order)
{
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    // คำนวณยอดสุทธิหลังหักส่วนลด
    $orderFinalTotal = 0;
    foreach ($order->items as $item) {
        $discountPercent = $item->product->discount_percent ?? 0;
        $originalPrice = $item->product->price;
        $discountedPrice = $discountPercent > 0
            ? round($originalPrice * (1 - $discountPercent/100), 2)
            : $originalPrice;
        $itemTotal = $discountedPrice * $item->quantity;
        $orderFinalTotal += $itemTotal;
    }

    $request->validate([
        'payment_slip' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'payment_date' => 'required|date',
        'payment_amount' => 'required|numeric|min:' . $orderFinalTotal
    ], [
        'payment_slip.required' => 'กรุณาเลือกไฟล์สลิปการโอนเงิน',
        'payment_slip.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
        'payment_slip.max' => 'ขนาดไฟล์ต้องไม่เกิน 2MB',
        'payment_date.required' => 'กรุณาระบุวันที่โอนเงิน',
        'payment_amount.required' => 'กรุณาระบุจำนวนเงิน',
        'payment_amount.min' => 'จำนวนเงินต้องไม่น้อยกว่ายอดที่ต้องชำระ'
    ]);

    try {
        if ($request->hasFile('payment_slip')) {
            $file = $request->file('payment_slip');
            $publicId = \Storage::disk('cloudinary')->putFile('Projectcafe_OrderSlips', $file);
            if ($publicId) {
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
            } else {
                return back()->with('error', 'อัปโหลดสลิปไป Cloudinary ไม่สำเร็จ');
            }
        } else {
            return back()->with('error', 'กรุณาเลือกไฟล์สลิปการโอนเงิน');
        }

        $order->update([
            'payment_slip' => $imageUrl,
            'payment_date' => $request->payment_date,
            'payment_amount' => $request->payment_amount,
            'payment_status' => 'pending'
        ]);

        return back()->with('success', 'อัพโหลดสลิปการโอนเงินเรียบร้อยแล้ว รอการตรวจสอบ');

    } catch (\Exception $e) {
        return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

    public function print(Order $order)
    {
        $pdf = PDF::loadView('user.orders.print', compact('order'));
        return $pdf->stream("order-{$order->order_code}.pdf");
    }

    public function downloadQuotation(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $pdf = PDF::loadView('User.orders.quotation', compact('order'));
        return $pdf->download("ใบเสนอราคา-{$order->order_code}.pdf");
    }

   
}