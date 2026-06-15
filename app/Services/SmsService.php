<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;
    private string $lineNumber;
    private string $baseUrl = 'https://api.sms.ir/v1';

    public function __construct()
    {
        $this->apiKey = config('services.smsir.api_key', '');
        $this->lineNumber = config('services.smsir.line_number', '');
    }

    public function sendOtp(string $mobile, string $code): bool
    {
        if (app()->isLocal() && empty($this->apiKey)) {
            Log::info("OTP for {$mobile}: {$code}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/send/verify", [
                'mobile' => $mobile,
                'templateId' => config('services.smsir.otp_template_id', 100000),
                'parameters' => [
                    ['name' => 'Code', 'value' => $code],
                ],
            ]);

            if ($response->successful()) {
                Log::info("OTP sent to {$mobile}");
                return true;
            }

            Log::error("SMS.ir error for {$mobile}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("SMS.ir exception: " . $e->getMessage());
            return false;
        }
    }
}
