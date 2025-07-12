<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionBooking;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;

class DashboardController extends Controller
{
    public function index()
{
    $user = Auth::user();

    // Get activity booking statistics
    $activityStats = [
        'total' => $user->promotionBookings()->count(),
        'pending' => $user->promotionBookings()->where('status', 'pending')->count(),
        'confirmed' => $user->promotionBookings()->where('status', 'confirmed')->count(),
        'cancelled' => $user->promotionBookings()->where('status', 'cancelled')->count()
    ];

    // Get order statistics
    $orderStats = [
        'total' => $user->orders()->count(),
        'pending' => $user->orders()->where('status', 'pending')->count(),
        'completed' => $user->orders()->where('status', 'completed')->count(),
        'cancelled' => $user->orders()->where('status', 'cancelled')->count()
    ];

    // Get upcoming activities for next 30 days
    $upcomingActivities = $user->promotionBookings()
        ->with(['promotion' => function ($query) {
            $query->withTrashed();
        }])
        ->whereDate('activity_date', '>=', now())
        ->whereDate('activity_date', '<=', now()->addDays(30))
        ->whereNotIn('status', ['cancelled', 'completed'])
        ->orderBy('activity_date')
        ->orderBy('activity_time')
        ->take(5)
        ->get();

    // Get recent notifications - fixed query
   $notifications = $user->notifications()
        ->whereNull('read_at')  // ดึงเฉพาะที่ยังไม่ได้อ่าน
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    $news = \App\Models\News::where('status', 'published')
        ->orderByDesc('published_at')
        ->take(5)
        ->get();

    return view('User.dashboard.index', compact(
        'activityStats',
        'orderStats',
        'upcomingActivities',
        'notifications',
        'news'
    ));
}

    /**
     * Display user bookings
     */
    public function bookings(Request $request)
    {
        $query = Auth::user()->promotionBookings()
            ->with(['promotion' => function ($query) {
                $query->withTrashed();
            }]);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->whereDate('activity_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('activity_date', '<=', $request->date_to);
        }

        $bookings = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return view('User.dashboard.bookings', compact('bookings'));
    }

    /**
     * Display user orders
     */
    public function orders(Request $request)
    {
        $query = Auth::user()->orders();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return view('User.dashboard.orders', compact('orders'));
    }

    /**
     * Display user notifications
     */
    public function notifications()
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(15);

        // Mark all as read when viewing notifications
        Auth::user()->unreadNotifications->markAsRead();

        return view('User.dashboard.notifications', compact('notifications'));
    }

    /**
     * Mark specific notification as read
     */
    public function markNotificationAsRead($id)
    {
        try {
            $notification = Auth::user()
                ->notifications()
                ->findOrFail($id);

            $notification->markAsRead();

            return back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'ไม่พบการแจ้งเตือนที่ระบุ');
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'ทำเครื่องหมายว่าอ่านทั้งหมดแล้ว');
    }
}