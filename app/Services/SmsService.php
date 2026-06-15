<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $baseUrl = 'https://api.sms.ir/v1';

    private function getApiKey(): string
    {
        // اولویت: تنظیمات DB > .env
        $dbKey = Setting::get('sms_api_key', '');
        if (!empty($dbKey)) return $dbKey;
        return (string) (config('services.smsir.api_key') ?? '');
    }

    private function getTemplateId(): int
    {
        $dbTemplate = Setting::get('sms_template_id', '');
        if (!empty($dbTemplate)) return (int) $dbTemplate;
        return (int) (config('services.smsir.otp_template_id') ?? 238380);
    }

    public function sendOtp(string $mobile, string $code): bool
    {
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            Log::info("[SMS-DEV] OTP for {$mobile}: {$code}");
            return true;
        }

        $templateId = $this->getTemplateId();
        $payload = [
            'mobile'     => $mobile,
            'templateId' => $templateId,
            'parameters' => [['name' => 'Code', 'value' => $code]],
        ];

        Log::info("[SMS] Sending OTP to {$mobile} template={$templateId}");

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-api-key'    => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'text/plain',
                ])
                ->post("{$this->baseUrl}/send/verify", $payload);

            $statusCode = $response->status();
            $body       = $response->body();
            Log::info("[SMS] Response {$statusCode}: {$body}");

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    if (isset($data['status']) && $data['status'] === 1) return true;
                    if (isset($data['data']['messageId']) || isset($data['messageId'])) return true;
                }
                if (is_numeric(trim($body)) && (int) trim($body) > 0) return true;
                Log::warning("[SMS] Unexpected response: {$body}");
                return false;
            }

            Log::error("[SMS] HTTP {$statusCode}: {$body}");
            return false;
        } catch (\Exception $e) {
            Log::error("[SMS] Exception: " . $e->getMessage());
            return false;
        }
    }
}
