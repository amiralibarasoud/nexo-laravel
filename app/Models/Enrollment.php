<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'content_type',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function booted(): void
    {
        static::created(function (Enrollment $enrollment) {
            Course::where('id', $enrollment->course_id)->increment('students_count');
        });

        static::deleted(function (Enrollment $enrollment) {
            Course::where('id', $enrollment->course_id)
                ->where('students_count', '>', Course::BASE_STUDENTS_COUNT)
                ->decrement('students_count');
        });
    }

    public function canAccessText(): bool
    {
        return in_array($this->content_type, ['text', 'both']);
    }

    public function canAccessAudio(): bool
    {
        return in_array($this->content_type, ['audio', 'both']);
    }
}
