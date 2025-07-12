<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Models\PromotionBooking;

class PromotionController extends Controller
{
  public function index(Request $request)
{
    $query = Promotion::where('status', 'active')
        ->where('ends_at', '>', now());

    // Filter: ค้นหาด้วยชื่อโปรโมชั่น
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('title', 'like', "%{$search}%");
    }

    // Filter: ประเภท (discount, special)
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // Sort
    if ($request->get('sort') === 'popular') {
        $query->withCount('bookings')->orderByDesc('bookings_count');
    } else {
        $query->orderByDesc('starts_at');
    }

    $promotions = $query->paginate(12)->appends($request->query());

    return view('User.promotion.index', compact('promotions'));
}

     public function show(Promotion $promotion)
    {
        if ($promotion->status === 'inactive') {
            abort(404);
        }

        return view('User.promotion.show', compact('promotion'));
    }

   public function book(Request $request, Promotion $promotion)
{
    if (!$promotion->hasAvailableSlots()) {
        return back()->with('error', 'กิจกรรมนี้เต็มแล้ว');
    }

    $validated = $request->validate([
        'activity_date' => 'required|date|after:today',
        'activity_time' => 'required',
        'number_of_participants' => [
            'required',
            'integer',
            'min:1',
            'max:' . ($promotion->max_participants - $promotion->current_participants)
        ],
        'special_requests' => 'nullable|string|max:500'
    ]);

    $totalPrice = $promotion->price_per_person * $validated['number_of_participants'];
    $discountAmount = ($promotion->discount / 100) * $totalPrice;
    $finalPrice = $totalPrice - $discountAmount;

    $booking = $request->user()->promotionBookings()->create([
        'promotion_id' => $promotion->id,
        'number_of_participants' => $validated['number_of_participants'],
        'total_price' => $totalPrice,
        'discount_amount' => $discountAmount,
        'final_price' => $finalPrice,
        'activity_date' => $validated['activity_date'],
        'activity_time' => $validated['activity_time'],
        'special_requests' => $validated['special_requests'],
        'booking_code' => 'PB' . time() . rand(1000, 9999)
    ]);

    $promotion->increment('current_participants', $validated['number_of_participants']);

    return redirect()->route('user.promotion-bookings.show', $booking)
        ->with('success', 'จองกิจกรรมสำเร็จ');
}

public function downloadQuotation(PromotionBooking $booking)
{
    $this->authorize('view', $booking);
    return $booking->generateQuotation();
}
}