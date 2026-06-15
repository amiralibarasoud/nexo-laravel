<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const OTP_EXPIRY_MINUTES = 5;
    private const OTP_RESEND_SECONDS = 90;

    public function __construct(private SmsService $smsService) {}

    public function isSandbox(): bool
    {
        return Setting::getBool('sms_sandbox', true);
    }

    public function getSandboxCode(): string
    {
        return Setting::get('sms_sandbox_code', '12345');
    }

    public function send(string $mobile): array
    {
        $mobile = normalizeMobile($mobile);

        // بررسی rate limit
        $lastOtp = OtpCode::where('mobile', $mobile)
            ->where('created_at', '>=', Carbon::now()->subSeconds(self::OTP_RESEND_SECONDS))
            ->latest()->first();

        if ($lastOtp) {
            $left = (int) max(1, self::OTP_RESEND_SECONDS - Carbon::now()->diffInSeconds($lastOtp->created_at));
            return [
                'success'     => false,
                'message'     => toFarsiNumber($left) . ' ثانیه دیگر دوباره امتحان کنید.',
                'retry_after' => $left,
            ];
        }

        // در حالت sandbox کد ثابت استفاده می‌شه
        $code = $this->isSandbox()
            ? $this->getSandboxCode()
            : $this->generateCode();

        OtpCode::create([
            'mobile'     => $mobile,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        if ($this->isSandbox()) {
            Log::info("[OTP-SANDBOX] Mobile={$mobile} Code={$code}");
            return [
                'success' => true,
                'message' => 'حالت تست فعال است. کد: ' . $code,
                'sandbox' => true,
                'code'    => $code,
            ];
        }

        $sent = $this->smsService->sendOtp($mobile, $code);

        if (!$sent) {
            OtpCode::where('mobile', $mobile)->where('code', $code)->delete();
            return ['success' => false, 'message' => 'خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.'];
        }

        return ['success' => true, 'message' => 'کد تأیید ارسال شد.'];
    }

    public function verify(string $mobile, string $code): bool
    {
        $mobile = normalizeMobile($mobile);
        $code   = persianToEnglishNumber(trim($code));

        $otp = OtpCode::where('mobile', $mobile)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>=', Carbon::now())
            ->latest()->first();

        if (!$otp) return false;

        $otp->update(['is_used' => true]);
        OtpCode::where('mobile', $mobile)->where('id', '!=', $otp->id)->delete();
        return true;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
    }
}
