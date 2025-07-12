<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PromotionBooking;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionBookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any promotion bookings.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine if the user can view the promotion booking.
     */
    public function view(User $user, PromotionBooking $booking)
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine if the user can create promotion bookings.
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine if the user can update the promotion booking.
     */
    public function update(User $user, PromotionBooking $booking)
    {
        return $user->id === $booking->user_id && !$booking->isCancelled();
    }

    /**
     * Determine if the user can delete the promotion booking.
     */
    public function delete(User $user, PromotionBooking $booking)
    {
        return $user->id === $booking->user_id && $booking->canBeCancelled();
    }

    /**
     * Determine if the user can cancel the promotion booking.
     */
    public function cancel(User $user, PromotionBooking $booking)
    {
        return $user->id === $booking->user_id && $booking->canBeCancelled();
    }

    /**
     * Determine if the user can upload payment slip.
     */
  public function uploadPaymentSlip(User $user, PromotionBooking $booking)
{
    return $user->id === $booking->user_id && 
           $booking->payment_status === 'pending' && 
           $booking->status === 'pending';
}
    
}