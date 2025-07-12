<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
class SocialController extends Controller
{
    protected function getGuzzleClient()
    {
        return new Client([
            'verify' => false,
            'timeout' => 30,
        ]);
    }
     public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')
                ->setHttpClient($this->getGuzzleClient())
                ->stateless()
                ->with([
                    'prompt' => 'select_account',
                    'access_type' => 'online',
                    'response_type' => 'code'
                ])
                ->redirect();
        } catch (Exception $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect()->route('user.login')
                           ->with('error', 'ไม่สามารถเชื่อมต่อกับ Google ได้');
        }
    }
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->setHttpClient($this->getGuzzleClient())
                ->stateless()
                ->user();

        DB::beginTransaction();
            
        // ค้นหาผู้ใช้จาก google_id ก่อน
        $user = User::where('google_id', $googleUser->getId())
                   ->orWhere('email', $googleUser->getEmail())
                   ->first();

        if (!$user) {
            // สร้างผู้ใช้ใหม่
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now()
            ]);
        } else {
            // อัพเดตข้อมูล Google
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar()
            ]);
        }

        // สร้างหรืออัพเดต social account
        SocialAccount::updateOrCreate(
            [
                'provider_name' => 'google',
                'provider_id' => $googleUser->getId(),
            ],
            [
                'user_id' => $user->id,
                'provider_token' => $googleUser->token,
                'provider_refresh_token' => $googleUser->refreshToken,
                'avatar' => $googleUser->getAvatar()
            ]
        );

        DB::commit();

        // Log for debugging
        Log::info('Google login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'google_id' => $googleUser->getId()
        ]);

        Auth::login($user);
            
        return redirect()->route('user.main')
                        ->with('success', 'เข้าสู่ระบบด้วย Google สำเร็จ');

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Google callback error: ' . $e->getMessage(), [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('user.login')
                        ->with('error', 'การเข้าสู่ระบบด้วย Google ไม่สำเร็จ: ' . $e->getMessage());
    }
}
public function redirectToLine()
{
    try {
        $config = config('services.line');
        if (!$config['client_id'] || !$config['redirect']) {
            throw new Exception('LINE configuration is missing');
        }

        return Socialite::driver('line')
            ->setHttpClient($this->getGuzzleClient())
            ->stateless() // เพิ่ม stateless()
            ->with([
                'bot_prompt' => 'normal',
                'scope' => 'profile openid email'
            ])
            ->redirect();
    } catch (Exception $e) {
        Log::error('LINE redirect error: ' . $e->getMessage());
        return redirect()->route('user.profile.social')
                       ->with('error', 'ไม่สามารถเชื่อมต่อกับ LINE ได้: ' . $e->getMessage());
    }
}

    public function handleLineCallback()
{
    try {
        DB::beginTransaction();

        $lineUser = Socialite::driver('line')
            ->setHttpClient($this->getGuzzleClient())
            ->stateless()
            ->user();

        $userId = session('line_bind_user_id');
        if (!$userId) {
            return redirect()->route('user.login')->with('error', 'กรุณาเข้าสู่ระบบก่อนเชื่อม LINE');
        }
        $user = User::find($userId);
            
            // Debug log
            Log::info('LINE User Data:', [
                'line_id' => $lineUser->id, // เปลี่ยนจาก getId() เป็น id
                'user_id' => $user->id
            ]);

            // อัพเดท user ด้วยข้อมูล LINE
            $user->forceFill([
                'line_id' => $lineUser->id,
                'avatar' => $lineUser->avatar ?? $user->avatar
            ])->save();

            // สร้างหรืออัพเดต social account
            SocialAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider_name' => 'line'
                ],
                [
                    'provider_id' => $lineUser->id,
                    'provider_token' => $lineUser->token,
                    'provider_refresh_token' => $lineUser->refreshToken ?? null,
                    'avatar' => $lineUser->avatar
                ]
            );

            DB::commit();

            Log::info('LINE connection successful', [
                'user_id' => $user->id,
                'line_id' => $lineUser->id
            ]);

            return redirect()->route('user.profile.social')
                ->with('success', 'เชื่อมต่อบัญชี LINE สำเร็จ');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('LINE callback error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('user.profile.social')
                ->with('error', 'ไม่สามารถเชื่อมต่อบัญชี LINE ได้: ' . $e->getMessage());
        }
    }

    public function disconnectSocial($provider)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if ($provider === 'line') {
                $user->update(['line_id' => null]);
                $user->socialAccounts()->where('provider_name', 'line')->delete();
        }
        
        DB::commit();
        
        return redirect()->route('user.profile.social')
            ->with('success', 'ยกเลิกการเชื่อมต่อบัญชีสำเร็จ');
            
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Social disconnect error: ' . $e->getMessage());
        return redirect()->route('user.profile.social')
            ->with('error', 'ไม่สามารถยกเลิกการเชื่อมต่อบัญชีได้');
    }
}
public function disconnect($provider)
{
    return $this->disconnectSocial($provider);
}
}