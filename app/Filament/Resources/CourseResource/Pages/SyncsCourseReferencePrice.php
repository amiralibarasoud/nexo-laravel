<?php

namespace App\Filament\Resources\CourseResource\Pages;

use Illuminate\Support\Arr;

trait SyncsCourseReferencePrice
{
    protected function syncCourseReferencePrice(array $data): array
    {
        $data = $this->normalizeNullableIntegers($data, [
            'price',
            'price_text',
            'price_audio',
            'price_both',
            'discounted_price',
        ]);

        $data['cover_image'] = $this->normalizeUploadPath($data['cover_image'] ?? null);
        $data['instructor_avatar'] = $this->normalizeUploadPath($data['instructor_avatar'] ?? null);

        if (array_key_exists('discount_expires_at', $data) && blank($data['discount_expires_at'])) {
            $data['discount_expires_at'] = null;
        }

        $hasText = (bool) ($data['has_text'] ?? false);
        $hasAudio = (bool) ($data['has_audio'] ?? false);

        if (!$hasText) {
            $data['price_text'] = null;
        }

        if (!$hasAudio) {
            $data['price_audio'] = null;
        }

        if (!$hasText || !$hasAudio) {
            $data['price_both'] = null;
        }

        $prices = [];

        if ($hasText) {
            $prices[] = (int) ($data['price_text'] ?? 0);
        }

        if ($hasAudio) {
            $prices[] = (int) ($data['price_audio'] ?? 0);
        }

        if ($hasText && $hasAudio) {
            $prices[] = (int) ($data['price_both'] ?? 0);
        }

        $data['price'] = !empty($prices) ? min($prices) : 0;

        if (blank($data['discounted_price'] ?? null)) {
            $data['discounted_price'] = null;
        }

        return $data;
    }

    protected function normalizeNullableIntegers(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if ($value === '' || $value === null) {
                $data[$field] = null;
                continue;
            }

            if (is_string($value) && function_exists('persianToEnglishNumber')) {
                $value = persianToEnglishNumber($value);
            }

            $data[$field] = is_numeric($value) ? (int) $value : null;
        }

        return $data;
    }

    protected function normalizeUploadPath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $path = Arr::first(Arr::flatten($value));

            return is_string($path) ? $path : null;
        }

        return null;
    }
}
