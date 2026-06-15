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

    public function getAudioDurationFormattedAttribute(): string
    {
        if (!$this->audio_duration_seconds) {
            return '۰:۰۰';
        }
        $minutes = intdiv($this->audio_duration_seconds, 60);
        $seconds = $this->audio_duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
