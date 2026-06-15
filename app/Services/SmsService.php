<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.sms.ir/v1';

    public function __construct()
    {
        $this->apiKey = (string) (config('services.smsir.api_key') ?? '');
    }

    /**
     * ارسال OTP از طریق وب‌سرویس SMS.ir
     * endpoint: POST /v1/send/verify
     * headers: x-api-key, Content-Type: application/json, Accept: text/plain
     */
    public function sendOtp(string $mobile, string $code): bool
    {
        // حالت توسعه: بدون API key لاگ می‌زند
        if (empty($this->apiKey)) {
            Log::info("[SMS-DEV] OTP for {$mobile}: {$code}");
            return true;
        }

        $templateId = (int) (config('services.smsir.otp_template_id') ?? 238380);

        $payload = [
            'mobile'     => $mobile,
            'templateId' => $templateId,
            'parameters' => [
                [
                    'name'  => 'Code',
                    'value' => $code,
                ],
            ],
        ];

        Log::info("[SMS] Sending OTP to {$mobile} with template {$templateId}");

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-api-key'    => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'text/plain',
                ])
                ->post("{$this->baseUrl}/send/verify", $payload);

            $statusCode = $response->status();
            $body       = $response->body();

            Log::info("[SMS] Response status={$statusCode} body={$body}");

            // SMS.ir در موفقیت status 200 و MessageId برمی‌گردونه
            if ($response->successful()) {
                $data = $response->json();
                // بعضی نسخه‌ها status:1 برمی‌گردونن
                if (is_array($data)) {
                    if (isset($data['status']) && $data['status'] === 1) {
                        Log::info("[SMS] OTP sent successfully to {$mobile}");
                        return true;
                    }
                    // اگر MessageId وجود داشت یعنی موفق بوده
                    if (isset($data['data']['messageId']) || isset($data['messageId'])) {
                        Log::info("[SMS] OTP sent successfully to {$mobile}");
                        return true;
                    }
                }
                // اگر body عدد بود (MessageId مستقیم)
                if (is_numeric(trim($body)) && (int) trim($body) > 0) {
                    Log::info("[SMS] OTP sent successfully to {$mobile}");
                    return true;
                }
                Log::warning("[SMS] Unexpected response: {$body}");
                return false;
            }

            Log::error("[SMS] HTTP error {$statusCode} for {$mobile}: {$body}");
            return false;
        } catch (\Exception $e) {
            Log::error("[SMS] Exception for {$mobile}: " . $e->getMessage());
            return false;
        }
    }
}
