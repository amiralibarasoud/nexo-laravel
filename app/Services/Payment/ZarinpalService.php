<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalService
{
    private string $merchantId;
    private bool $sandbox;
    private string $apiBase;
    private string $paymentUrl;

    public function __construct()
    {
        $this->merchantId = (string) (config('services.zarinpal.merchant_id') ?? '');
        $this->sandbox = (bool) (config('services.zarinpal.sandbox') ?? true);
        $this->apiBase = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment'
            : 'https://api.zarinpal.com/pg/v4/payment';
        $this->paymentUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay'
            : 'https://www.zarinpal.com/pg/StartPay';
    }

    public function request(int $amount, string $description, string $callbackUrl, string $mobile = ''): array
    {
        try {
            $response = Http::post("{$this->apiBase}/request.json", [
                'merchant_id' => $this->merchantId,
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
                    'url' => $this->paymentUrl . '/' . $data['data']['authority'],
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
        try {
            $response = Http::post("{$this->apiBase}/verify.json", [
                'merchant_id' => $this->merchantId,
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
