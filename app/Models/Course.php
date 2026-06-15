<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'cover_image',
        'preview_video',
        'instructor_name',
        'instructor_avatar',
        'instructor_bio',
        'price',
        'discounted_price',
        'discount_expires_at',
        'has_text',
        'has_audio',
        'students_count',
        'duration_minutes',
        'lessons_count',
        'rating',
        'ratings_count',
        'level',
        'status',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'has_text' => 'boolean',
        'has_audio' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'discount_expires_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class)->where('is_approved', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getEffectivePriceAttribute(): int
    {
        if ($this->discounted_price && $this->discount_expires_at && $this->discount_expires_at->isFuture()) {
            return $this->discounted_price;
        }
        return $this->price;
    }

    public function getIsDiscountedAttribute(): bool
    {
        return $this->discounted_price !== null
            && $this->discount_expires_at !== null
            && $this->discount_expires_at->isFuture();
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_discounted || $this->price == 0) {
            return 0;
        }
        return (int) round(100 - ($this->discounted_price / $this->price * 100));
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'beginner' => 'مقدماتی',
            'intermediate' => 'متوسط',
            'advanced' => 'پیشرفته',
            default => 'همه سطوح',
        };
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
