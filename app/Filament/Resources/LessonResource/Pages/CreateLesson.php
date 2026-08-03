<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Filament\Resources\LessonResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['audio_path'] = $this->normalizeAudioPath($data['audio_path'] ?? null);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function normalizeAudioPath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = Arr::first(Arr::flatten($value));
        }

        return is_string($value) && $value !== '' ? ltrim(str_replace('\\', '/', $value), '/') : null;
    }
}
