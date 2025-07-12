<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()->latest()->paginate(10);
        return view('User.booking.index', compact('bookings'));
    }

    public function create()
    {
        return view('User.booking.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required',
            'number_of_guests' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500'
        ]);

        $booking = Auth::user()->bookings()->create([
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'number_of_guests' => $validated['number_of_guests'],
            'notes' => $validated['notes'],
            'status' => 'pending'
        ]);

        return redirect()->route('user.bookings.show', $booking)
            ->with('success', 'สร้างการจองสำเร็จ');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        return view('User.booking.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);
        
        if ($booking->status === 'pending') {
            $booking->update(['status' => 'cancelled']);
            return redirect()->route('user.bookings.index')
                ->with('success', 'ยกเลิกการจองสำเร็จ');
        }

        return back()->with('error', 'ไม่สามารถยกเลิกการจองได้');
    }
}