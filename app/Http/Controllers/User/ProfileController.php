<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use App\Models\UserProfile;
use Exception;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get counts and totals
        $bookingCount = $user->bookings()->count();
        $orderCount = $user->orders()->count();
        $totalSpent = $user->orders()->sum('total_amount');

        return view('User.profile.index', compact(
            'bookingCount',
            'orderCount',
            'totalSpent'
        ));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('User.profile.edit', compact('user'));
    }

    public function update(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:500',
        'avatar' => 'nullable|image|max:1024'
    ]);

    if ($request->hasFile('avatar')) {
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $validated['avatar'] = $path;
    }

    $user->update($validated);

    // อัพเดทหรือสร้าง UserProfile
    $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
        ['address' => $validated['address']]
    );

    return redirect()->route('user.profile.index')
        ->with('success', 'อัพเดทโปรไฟล์สำเร็จ');
}

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('user.profile.index')
            ->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ');
    }
    public function social()
{
    $user = Auth::user();
    $socialAccounts = $user->socialAccounts;
    
    return view('User.profile.social', compact('user', 'socialAccounts'));
}

public function connectLine()
{
    try {
        return Socialite::driver('line')
            ->with(['prompt' => 'consent'])
            ->redirect();
    } catch (Exception $e) {
        return redirect()->route('user.profile.social')
            ->with('error', 'ไม่สามารถเชื่อมต่อกับ LINE ได้');
    }
}

public function handleLineCallback()
{
    try {
        $lineUser = Socialite::driver('line')->user();
        $user = Auth::user();

        // Check if LINE account is already linked to another user
        $existingAccount = SocialAccount::where('provider_name', 'line')
            ->where('provider_id', $lineUser->getId())
            ->first();

        if ($existingAccount && $existingAccount->user_id !== $user->id) {
            return redirect()->route('user.profile.social')
                ->with('error', 'บัญชี LINE นี้ถูกเชื่อมต่อกับบัญชีอื่นแล้ว');
        }

        // อัพเดท line_id ในตาราง users
        $user->update([
            'line_id' => $lineUser->getId()
        ]);

        // Create or update social account
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider_name' => 'line'
            ],
            [
                'provider_id' => $lineUser->getId(),
                'provider_token' => $lineUser->token,
                'avatar' => $lineUser->getAvatar()
            ]
        );

        return redirect()->route('user.profile.social')
            ->with('success', 'เชื่อมต่อบัญชี LINE สำเร็จ');

    } catch (Exception $e) {
        return redirect()->route('user.profile.social')
            ->with('error', 'ไม่สามารถเชื่อมต่อบัญชี LINE ได้');
    }
}

public function disconnectSocial($provider)
{
    try {
        $user = Auth::user();
        
        if ($provider === 'line') {
            // ลบ line_id จากตาราง users
            $user->update(['line_id' => null]);
        }
        
        // ลบข้อมูลจากตาราง social_accounts
        $user->socialAccounts()
            ->where('provider_name', $provider)
            ->delete();

        return redirect()->route('user.profile.social')
            ->with('success', 'ยกเลิกการเชื่อมต่อบัญชีสำเร็จ');
    } catch (Exception $e) {
        return redirect()->route('user.profile.social')
            ->with('error', 'ไม่สามารถยกเลิกการเชื่อมต่อบัญชีได้');
    }
}
}