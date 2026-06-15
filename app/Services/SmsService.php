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
        $this->apiKey = config('services.smsir.api_key', '');
    }

    /**
     * Send OTP via SMS.ir verify (fast send) endpoint using template 238380.
     * No line number required — uses the template directly.
     */
    public function sendOtp(string $mobile, string $code): bool
    {
        if (empty($this->apiKey)) {
            Log::info("[SMS-DEV] OTP for {$mobile}: {$code}");
            return true;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/send/verify", [
                    'mobile'     => $mobile,
                    'templateId' => (int) config('services.smsir.otp_template_id', 238380),
                    'parameters' => [
                        ['name' => 'Code', 'value' => $code],
                    ],
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status']) && $body['status'] === 1) {
                Log::info("[SMS] OTP sent to {$mobile}");
                return true;
            }

            Log::error("[SMS] SMS.ir error for {$mobile}: " . json_encode($body));
            return false;
        } catch (\Exception $e) {
            Log::error("[SMS] SMS.ir exception: " . $e->getMessage());
            return false;
        }
    }
}
