<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'description',
        'sort_order',
        'text_content',
        'audio_path',
        'audio_duration_seconds',
        'is_preview',
        'is_published',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    protected static function booted(): void
    {
        $sync = function (Lesson $lesson) {
            if ($lesson->course_id) {
                Course::where('id', $lesson->course_id)->update([
                    'lessons_count' => Lesson::where('course_id', $lesson->course_id)->count(),
                ]);
            }

            if ($lesson->wasChanged('course_id') && $lesson->getOriginal('course_id')) {
                $oldCourseId = $lesson->getOriginal('course_id');
                Course::where('id', $oldCourseId)->update([
                    'lessons_count' => Lesson::where('course_id', $oldCourseId)->count(),
                ]);
            }
        };

        static::saved($sync);
        static::deleted(function (Lesson $lesson) {
            if ($lesson->course_id) {
                Course::where('id', $lesson->course_id)->update([
                    'lessons_count' => Lesson::where('course_id', $lesson->course_id)->count(),
                ]);
            }
        });
    }

    public function getAudioDurationFormattedAttribute(): string
    {
        if (!$this->audio_duration_seconds) {
            return '۰:۰۰';
        }
        $minutes = intdiv($this->audio_duration_seconds, 60);
        $seconds = $this->audio_duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Resolve the absolute filesystem path for the lesson audio file.
     * Supports both current and legacy storage layouts.
     */
    public function resolveAudioAbsolutePath(): ?string
    {
        if (blank($this->audio_path)) {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', (string) $this->audio_path), '/');

        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        $candidates = [
            storage_path('app/private/'.$relative),
            storage_path('app/'.$relative),
        ];

        if (str_starts_with($relative, 'private/')) {
            $withoutPrivate = substr($relative, strlen('private/'));
            $candidates[] = storage_path('app/private/'.$withoutPrivate);
            $candidates[] = storage_path('app/'.$relative);
        } else {
            $candidates[] = storage_path('app/private/private/'.$relative);
        }

        foreach (array_unique($candidates) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function audioMimeType(?string $absolutePath = null): string
    {
        $path = $absolutePath ?: $this->resolveAudioAbsolutePath() ?: (string) $this->audio_path;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'mp3', 'mpga', 'mp2' => 'audio/mpeg',
            'wav', 'wave' => 'audio/wav',
            'ogg', 'oga' => 'audio/ogg',
            'opus' => 'audio/ogg',
            'm4a', 'aac', 'mp4' => 'audio/mp4',
            'flac' => 'audio/flac',
            'wma' => 'audio/x-ms-wma',
            'webm', 'weba' => 'audio/webm',
            'aif', 'aiff' => 'audio/aiff',
            'amr' => 'audio/amr',
            '3gp', '3gpp' => 'audio/3gpp',
            default => is_file($path)
                ? (mime_content_type($path) ?: 'application/octet-stream')
                : 'application/octet-stream',
        };
    }
}
