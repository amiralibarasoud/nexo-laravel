<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZibalService
{
    private string $merchant;
    private string $apiBase = 'https://gateway.zibal.ir/v1';
    private string $paymentUrl = 'https://gateway.zibal.ir/start';

    public function __construct()
    {
        $this->merchant = config('services.zibal.merchant', 'zibal');
    }

    public function request(int $amount, string $callbackUrl, string $mobile = '', string $description = ''): array
    {
        try {
            $response = Http::post("{$this->apiBase}/request", [
                'merchant' => $this->merchant,
                'amount' => $amount * 10, // Toman to Rial
                'callbackUrl' => $callbackUrl,
                'mobile' => $mobile,
                'description' => $description,
            ]);

            $data = $response->json();

            if (isset($data['trackId']) && $data['result'] == 100) {
                return [
                    'success' => true,
                    'track_id' => (string) $data['trackId'],
                    'url' => "{$this->paymentUrl}/{$data['trackId']}",
                ];
            }

            Log::error('Zibal request failed: ' . json_encode($data));
            return ['success' => false, 'message' => 'خطا در اتصال به درگاه پرداخت.'];
        } catch (\Exception $e) {
            Log::error('Zibal exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در اتصال به درگاه پرداخت.'];
        }
    }

    public function verify(string $trackId): array
    {
        try {
            $response = Http::post("{$this->apiBase}/verify", [
                'merchant' => $this->merchant,
                'trackId' => $trackId,
            ]);

            $data = $response->json();

            if (isset($data['result']) && $data['result'] == 100) {
                return [
                    'success' => true,
                    'ref_number' => (string) ($data['refNumber'] ?? $trackId),
                    'card_number' => $data['cardNumber'] ?? null,
                    'data' => $data,
                ];
            }

            Log::error('Zibal verify failed: ' . json_encode($data));
            return ['success' => false, 'message' => 'پرداخت تأیید نشد.'];
        } catch (\Exception $e) {
            Log::error('Zibal verify exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در تأیید پرداخت.'];
        }
    }
}
