<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('User.auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'ข้อมูลที่ระบุไม่ถูกต้อง'])
            ->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('User.auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('user.dashboard')
            ->with('success', 'ลงทะเบียนสำเร็จ');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')
            ->with('success', 'ออกจากระบบสำเร็จ');
    }

    /**
     * Redirect to Google
     */
   public function redirectToGoogle()
{
    try {
        return Socialite::driver('google')->redirect();
    } catch (Exception $e) {
        Log::error('Google redirect error: ' . $e->getMessage());
        return redirect()->route('user.login')
                       ->with('error', 'ไม่สามารถเชื่อมต่อกับ Google ได้');
    }
}

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                    'password' => bcrypt(str()->random(24))
                ]
            );

            Auth::login($user);

            return redirect()->route('user.dashboard')
                           ->with('success', 'เข้าสู่ระบบด้วย Google สำเร็จ');

        } catch (Exception $e) {
            Log::error('Google callback error: ' . $e->getMessage());
            return redirect()->route('user.login')
                           ->with('error', 'การเข้าสู่ระบบด้วย Google ไม่สำเร็จ');
        }
    }

    /**
     * Redirect to LINE
     */
    public function redirectToLine()
    {
        return Socialite::driver('line')->redirect();
    }

    /**
     * Handle LINE callback
     */
    public function handleLineCallback()
    {
        try {
            $lineUser = Socialite::driver('line')->user();
            
            $user = User::updateOrCreate(
                ['line_id' => $lineUser->id],
                [
                    'name' => $lineUser->name,
                    'email' => $lineUser->email ?? $lineUser->id . '@line.user',
                    'avatar' => $lineUser->avatar,
                    'password' => Hash::make(str()->random(24)),
                    'email_verified_at' => now(),
                ]
            );

            Auth::login($user);

            return redirect()->route('user.dashboard')
                ->with('success', 'เข้าสู่ระบบด้วย LINE สำเร็จ');

        } catch (Exception $e) {
            return redirect()->route('user.login')
                ->with('error', 'การเข้าสู่ระบบด้วย LINE ไม่สำเร็จ');
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('User.auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}