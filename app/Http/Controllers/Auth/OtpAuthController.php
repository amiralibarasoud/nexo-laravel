<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OtpAuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
        ]);

        $result = $this->otpService->send($request->mobile);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'code' => ['required', 'string', 'min:4', 'max:6'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'code.required' => 'کد تأیید الزامی است.',
        ]);

        $mobile = normalizeMobile($request->mobile);
        $verified = $this->otpService->verify($mobile, $request->code);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'کد تأیید اشتباه یا منقضی شده است.',
            ], 422);
        }

        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            [
                'name' => 'کاربر ' . substr($mobile, -4),
                'mobile_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (!$user->mobile_verified_at) {
            $user->update(['mobile_verified_at' => now()]);
        }

        Auth::login($user, remember: true);

        return response()->json([
                'success' => true,
                'message' => 'خوش آمدید!',
                'redirect' => session()->pull('url.intended', route('dashboard.index')),
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
