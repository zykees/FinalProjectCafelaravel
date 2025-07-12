<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionBooking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

 public function index(Request $request)
{
    $query = Promotion::query();

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter by is_featured (cast เป็น int)
    if ($request->filled('is_featured')) {
        $query->where('is_featured', (int)$request->is_featured);
    }

    // Filter by start date
    if ($request->filled('starts_at')) {
        $query->whereDate('starts_at', '>=', $request->starts_at);
    }

    // Filter by end date
    if ($request->filled('ends_at')) {
        $query->whereDate('ends_at', '<=', $request->ends_at);
    }

    // Filter by search (ชื่อกิจกรรม)
    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $promotions = $query->latest()->paginate(10)->appends($request->query());

    $stats = [
        'total'    => Promotion::count(),
        'active'   => Promotion::where('status', 'active')->count(),
        'inactive' => Promotion::where('status', 'inactive')->count(),
        'featured' => Promotion::where('is_featured', 1)->count(),
    ];

    return view('admin.promotions.index', compact('promotions', 'stats'));
}

    public function create()
    {
        return view('admin.promotions.create');
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'description' => 'required|string',
        'activity_details' => 'required|string',
        'max_participants' => 'required|integer|min:1',
        'price_per_person' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0|max:100',
        'starts_at' => 'required|date',
        'ends_at' => 'required|date|after:starts_at',
        'location' => 'required|string',
        'included_items' => 'nullable|string',
        'status' => 'required|in:active,inactive'
    ]);

    try {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            // อัปโหลดไป Cloudinary
            $publicId = \Storage::disk('cloudinary')->putFile('Projectcafe_Promotions', $file);
            if ($publicId) {
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
                $validated['image'] = $imageUrl;
            } else {
                return back()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
            }
        } else {
            return back()->with('error', 'กรุณาเลือกรูปภาพที่ถูกต้อง');
        }

        $validated['is_featured'] = $request->has('is_featured');
        $validated['discount'] = $request->filled('discount') ? (int)$request->discount : 0;
        Promotion::create($validated);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'สร้างกิจกรรมสำเร็จ');
    } catch (\Exception $e) {
        Log::error('Error creating promotion: ' . $e->getMessage());
        return back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

    public function show(Promotion $promotion)
{
    $stats = [
        'total_bookings' => $promotion->bookings()->count(),
        'confirmed_bookings' => $promotion->bookings()->where('status', 'confirmed')->count(),
        'total_participants' => $promotion->current_participants,
        'total_revenue' => $promotion->bookings()
            ->where('status', 'confirmed')
            ->sum('final_price'),
        'remaining_slots' => $promotion->getRemainingSlots()
    ];

    $recentBookings = $promotion->bookings()
        ->with('user')
        ->latest()
        ->take(5)
        ->get();

    return view('admin.promotions.show', compact('promotion', 'stats', 'recentBookings'));
}

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'description' => 'required|string',
        'activity_details' => 'required|string',
        'max_participants' => 'required|integer|min:1',
        'price_per_person' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0|max:100',
        'starts_at' => 'required|date',
        'ends_at' => 'required|date|after:starts_at',
        'location' => 'required|string',
        'included_items' => 'nullable|string',
        'status' => 'required|in:active,inactive'
    ]);
    $validated['is_featured'] = $request->has('is_featured');
    $validated['discount'] = $request->filled('discount') ? (int)$request->discount : 0;

    try {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            // ลบไฟล์เดิมออกจาก Cloudinary (ถ้ามี)
            if ($promotion->image) {
                $publicId = basename(parse_url($promotion->image, PHP_URL_PATH));
                \Storage::disk('cloudinary')->delete('Projectcafe_Promotions/' . $publicId);
            }
            // อัปโหลดไฟล์ใหม่
            $newPublicId = \Storage::disk('cloudinary')->putFile('Projectcafe_Promotions', $file);
            if ($newPublicId) {
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$newPublicId}";
                $validated['image'] = $imageUrl;
            } else {
                return back()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
            }
        }
        $promotion->update($validated);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'อัพเดทโปรโมชั่นกิจกรรมสำเร็จ');
    } catch (\Exception $e) {
        return back()->withInput()->withErrors(['error' => 'ไม่สามารถอัพเดทโปรโมชั่นกิจกรรมได้']);
    }
}

    public function destroy(Promotion $promotion)
    {
        try {
            if ($promotion->orders()->exists()) {
                return back()->withErrors([
                    'error' => 'Cannot delete promotion with associated orders'
                ]);
            }

            $promotion->forceDelete();
            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete promotion']);
        }
    }
    public function viewBookings(Promotion $promotion)
{
    $bookings = $promotion->bookings()
        ->with('user')
        ->latest()
        ->paginate(10);

    $stats = [
        'total_bookings' => $bookings->total(),
        'total_participants' => $promotion->bookings()->sum('number_of_participants'),
        'total_revenue' => $promotion->bookings()
            ->where('status', 'confirmed')
            ->sum('final_price'),
        'available_slots' => $promotion->max_participants - $promotion->current_participants
    ];

    return view('admin.promotions.bookings', compact('promotion', 'bookings', 'stats'));
}

public function downloadQuotation($bookingId)
{
    $booking = PromotionBooking::with(['user', 'promotion'])->findOrFail($bookingId);
    
    $pdf = PDF::loadView('user.promotion_bookings.quotation', [
        'booking' => $booking,
        'promotion' => $booking->promotion
    ]);

    return $pdf->download('ใบเสนอราคา_' . $booking->booking_code . '.pdf');
}
}