<?php

namespace App\Services\Payment;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalService
{
    private function config(): array
    {
        return Setting::paymentConfig()['zarinpal'];
    }

    private function apiBase(bool $sandbox): string
    {
        return $sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment'
            : 'https://api.zarinpal.com/pg/v4/payment';
    }

    private function paymentUrl(bool $sandbox): string
    {
        return $sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay'
            : 'https://www.zarinpal.com/pg/StartPay';
    }

    public function request(int $amount, string $description, string $callbackUrl, string $mobile = ''): array
    {
        $config  = $this->config();
        $apiBase = $this->apiBase($config['sandbox']);
        $payUrl  = $this->paymentUrl($config['sandbox']);

        try {
            $response = Http::post("{$apiBase}/request.json", [
                'merchant_id' => $config['merchant_id'],
                'amount' => $amount * 10, // Toman to Rial
                'description' => $description,
                'callback_url' => $callbackUrl,
                'metadata' => ['mobile' => $mobile],
            ]);

            $data = $response->json();

            if (isset($data['data']['authority'])) {
                return [
                    'success' => true,
                    'authority' => $data['data']['authority'],
                    'url' => $payUrl . '/' . $data['data']['authority'],
                ];
            }

            Log::error('Zarinpal request failed: ' . json_encode($data));
            return ['success' => false, 'message' => 'خطا در اتصال به درگاه پرداخت.'];
        } catch (\Exception $e) {
            Log::error('Zarinpal exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در اتصال به درگاه پرداخت.'];
        }
    }

    public function verify(string $authority, int $amount): array
    {
        $config  = $this->config();
        $apiBase = $this->apiBase($config['sandbox']);

        try {
            $response = Http::post("{$apiBase}/verify.json", [
                'merchant_id' => $config['merchant_id'],
                'authority' => $authority,
                'amount' => $amount * 10, // Toman to Rial
            ]);

            $data = $response->json();

            if (isset($data['data']['ref_id'])) {
                return [
                    'success' => true,
                    'ref_id' => (string) $data['data']['ref_id'],
                    'card_number' => $data['data']['card_pan'] ?? null,
                    'data' => $data['data'],
                ];
            }

            Log::error('Zarinpal verify failed: ' . json_encode($data));
            return ['success' => false, 'message' => 'پرداخت تأیید نشد.'];
        } catch (\Exception $e) {
            Log::error('Zarinpal verify exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در تأیید پرداخت.'];
        }
    }
}
