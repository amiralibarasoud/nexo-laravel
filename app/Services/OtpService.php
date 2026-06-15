<?php

namespace App\Services;

use App\Models\OtpCode;
use Carbon\Carbon;

class OtpService
{
    private const OTP_EXPIRY_MINUTES = 5;
    private const OTP_RESEND_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private SmsService $smsService) {}

    public function send(string $mobile): array
    {
        $mobile = normalizeMobile($mobile);

        $lastOtp = OtpCode::where('mobile', $mobile)
            ->where('created_at', '>=', Carbon::now()->subSeconds(self::OTP_RESEND_SECONDS))
            ->latest()
            ->first();

        if ($lastOtp) {
            $secondsLeft = self::OTP_RESEND_SECONDS - Carbon::now()->diffInSeconds($lastOtp->created_at);
            return [
                'success' => false,
                'message' => "لطفاً {$secondsLeft} ثانیه دیگر دوباره تلاش کنید.",
                'retry_after' => $secondsLeft,
            ];
        }

        $code = $this->generateCode();

        OtpCode::create([
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        $sent = $this->smsService->sendOtp($mobile, $code);

        return [
            'success' => $sent,
            'message' => $sent ? 'کد تأیید ارسال شد.' : 'خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.',
        ];
    }

    public function verify(string $mobile, string $code): bool
    {
        $mobile = normalizeMobile($mobile);
        $code = persianToEnglishNumber($code);

        $otp = OtpCode::where('mobile', $mobile)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['is_used' => true]);

        OtpCode::where('mobile', $mobile)->where('id', '!=', $otp->id)->delete();

        return true;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
    }
}
