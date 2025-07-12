<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\PromotionBooking; // เพิ่มตรงนี้

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        if (!auth()->guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $data = [
            'totalUsers' => User::withoutTrashed()->count(),
            'totalOrders' => Order::withoutTrashed()->count(),
            'totalProducts' => Product::withoutTrashed()->count(),
            'totalBookings' => PromotionBooking::withoutTrashed()->count(), // เปลี่ยนเป็น PromotionBooking
            // ออเดอร์ล่าสุด 5 รายการ
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
            // การจองโปรโมชั่นล่าสุด 5 รายการ
            'recentBookings' => PromotionBooking::with('user')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', $data);
    }
}