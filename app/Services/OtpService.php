<?php

namespace App\Services;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const OTP_EXPIRY_MINUTES = 5;
    private const OTP_RESEND_SECONDS = 90;

    public function __construct(private SmsService $smsService) {}

    public function send(string $mobile): array
    {
        $mobile = normalizeMobile($mobile);

        // بررسی rate limit
        $lastOtp = OtpCode::where('mobile', $mobile)
            ->where('created_at', '>=', Carbon::now()->subSeconds(self::OTP_RESEND_SECONDS))
            ->latest()
            ->first();

        if ($lastOtp) {
            $secondsLeft = (int) max(1, self::OTP_RESEND_SECONDS - Carbon::now()->diffInSeconds($lastOtp->created_at));
            return [
                'success'     => false,
                'message'     => toFarsiNumber($secondsLeft) . ' ثانیه دیگر دوباره امتحان کنید.',
                'retry_after' => $secondsLeft,
            ];
        }

        $code = $this->generateCode();

        OtpCode::create([
            'mobile'     => $mobile,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        $sent = $this->smsService->sendOtp($mobile, $code);

        if (!$sent) {
            // اگر SMS ارسال نشد، OTP را پاک کن تا کاربر دوباره تلاش کند
            OtpCode::where('mobile', $mobile)->where('code', $code)->delete();
            Log::error("[OTP] Failed to send to {$mobile}");
            return [
                'success' => false,
                'message' => 'خطا در ارسال پیامک. لطفاً چند لحظه دیگر دوباره تلاش کنید.',
            ];
        }

        Log::info("[OTP] Sent to {$mobile}");
        return [
            'success' => true,
            'message' => 'کد تأیید به شماره شما ارسال شد.',
        ];
    }

    public function verify(string $mobile, string $code): bool
    {
        $mobile = normalizeMobile($mobile);
        $code   = persianToEnglishNumber(trim($code));

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
