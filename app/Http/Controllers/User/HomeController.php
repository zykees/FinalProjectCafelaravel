<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        
    // โปรโมชั่นแนะนำ (active, ยังไม่หมดอายุ, และ is_featured = true)
    $promotions = Promotion::where('status', 'active')
        ->where('is_featured', true)
        ->whereDate('ends_at', '>=', now())
        ->orderBy('starts_at', 'desc')
        ->take(6)
        ->get();

    // สินค้าแนะนำ (active และ featured = true)
    $products = Product::where('status', 'available')
        ->where('featured', true)
        ->orderBy('created_at', 'desc')
        ->take(8)
        ->get();

    return view('User.main', compact('promotions', 'products'));
    }
}