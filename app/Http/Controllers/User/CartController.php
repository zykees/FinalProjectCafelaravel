<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Cart;

class CartController extends Controller
{
    public function index()
    {
        return view('User.shop.cart');
    }

    public function addToCart(Request $request, Product $product)
{
    // ตรวจสอบสถานะสินค้า
    if ($product->status !== 'available' || $product->stock <= 0) {
        return back()->with('error', 'สินค้าไม่พร้อมขายในขณะนี้');
    }

    $quantity = $request->quantity ?? 1;

    // เช็คยอดในตะกร้าปัจจุบัน
    $cartItem = \Cart::get($product->id);
    $currentInCart = $cartItem ? $cartItem->quantity : 0;

    if ($quantity + $currentInCart > $product->stock) {
        return back()->with('error', 'จำนวนสินค้าที่เลือกเกินจำนวนคงเหลือในสต็อก');
    }

    // เพิ่มสินค้าลงตะกร้า
    \Cart::add([
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
        'quantity' => $quantity,
        'attributes' => [
            'image' => $product->image,
            'stock' => $product->stock,
            'discount_percent' => $product->discount_percent ?? 0,
        ]
    ]);

    return back()->with('success', 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว');
}
    public function updateCart(Request $request)
    {
        foreach($request->quantity as $id => $quantity) {
            $product = Product::find($id);

            if (!$product || $quantity > $product->stock) {
                return back()->with('error', 'จำนวนสินค้าไม่เพียงพอ');
            }

            // อัปเดตจำนวนและ attributes (stock, discount_percent)
            Cart::update($id, [
                'quantity' => [
                    'relative' => false,
                    'value' => $quantity
                ],
                'attributes' => [
                    'image' => $product->image,
                    'stock' => $product->stock,
                    'discount_percent' => $product->discount_percent ?? 0,
                ]
            ]);
        }

        return back()->with('success', 'อัพเดทตะกร้าเรียบร้อยแล้ว');
    }

    public function removeFromCart($id)
    {
        try {
            Cart::remove($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ลบสินค้าเรียบร้อยแล้ว'
                ]);
            }

            return redirect()->back()->with('success', 'ลบสินค้าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function clearCart()
    {
        Cart::clear();
        return back()->with('success', 'ล้างตะกร้าเรียบร้อยแล้ว');
    }
}