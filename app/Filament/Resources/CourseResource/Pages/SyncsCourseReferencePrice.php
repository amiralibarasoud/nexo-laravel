<?php

namespace App\Filament\Resources\CourseResource\Pages;

trait SyncsCourseReferencePrice
{
    protected function syncCourseReferencePrice(array $data): array
    {
        $prices = [];

        if ($data['has_text'] ?? false) {
            $prices[] = (int) ($data['price_text'] ?? 0);
        }

        if ($data['has_audio'] ?? false) {
            $prices[] = (int) ($data['price_audio'] ?? 0);
        }

        if (($data['has_text'] ?? false) && ($data['has_audio'] ?? false)) {
            $prices[] = (int) ($data['price_both'] ?? 0);
        }

        $data['price'] = !empty($prices) ? min($prices) : (int) ($data['price'] ?? 0);

        return $data;
    }
}
