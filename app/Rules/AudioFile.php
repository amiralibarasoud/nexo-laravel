<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AudioFile implements ValidationRule
{
    public const EXTENSIONS = [
        'mp3', 'mpga', 'mp2', 'wav', 'wave', 'ogg', 'oga', 'opus',
        'm4a', 'aac', 'flac', 'wma', 'webm', 'weba', 'aif', 'aiff',
        'amr', '3gp', '3gpp', 'mp4',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || $value === []) {
            return;
        }

        if (is_string($value)) {
            $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));

            if ($ext !== '' && ! in_array($ext, self::EXTENSIONS, true)) {
                $fail('فرمت فایل صوتی پشتیبانی نمی‌شود.');
            }

            return;
        }

        if (! $value instanceof UploadedFile) {
            return;
        }

        $ext = strtolower($value->getClientOriginalExtension() ?: $value->guessExtension() ?: '');

        if ($ext === '' || ! in_array($ext, self::EXTENSIONS, true)) {
            $fail('فرمت فایل صوتی پشتیبانی نمی‌شود.');
        }
    }
}
