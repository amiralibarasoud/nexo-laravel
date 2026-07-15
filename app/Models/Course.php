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
        'price_text',
        'price_audio',
        'price_both',
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
        return $this->getStartingEffectivePrice();
    }

    public function getBasePriceForContentType(string $contentType): int
    {
        return match ($contentType) {
            'text' => (int) ($this->price_text ?? $this->price),
            'audio' => (int) ($this->price_audio ?? $this->price),
            'both' => (int) ($this->price_both ?? $this->price),
            default => (int) $this->price,
        };
    }

    public function getEffectivePriceForContentType(string $contentType): int
    {
        $basePrice = $this->getBasePriceForContentType($contentType);

        if ($this->is_discounted && $this->price > 0) {
            $ratio = $this->discounted_price / $this->price;

            return (int) max(0, round($basePrice * $ratio));
        }

        return $basePrice;
    }

    public function getAvailableContentTypes(): array
    {
        $types = [];

        if ($this->has_text) {
            $types[] = 'text';
        }

        if ($this->has_audio) {
            $types[] = 'audio';
        }

        if ($this->has_text && $this->has_audio) {
            $types[] = 'both';
        }

        return $types;
    }

    public function isContentTypeAvailable(string $contentType): bool
    {
        return in_array($contentType, $this->getAvailableContentTypes(), true);
    }

    public function getContentTypePrices(): array
    {
        $prices = [];

        foreach ($this->getAvailableContentTypes() as $type) {
            $basePrice = $this->getBasePriceForContentType($type);
            $effectivePrice = $this->getEffectivePriceForContentType($type);

            $prices[$type] = [
                'price' => $basePrice,
                'effective_price' => $effectivePrice,
                'is_discounted' => $this->is_discounted && $basePrice > $effectivePrice,
            ];
        }

        return $prices;
    }

    public function getStartingPriceAttribute(): int
    {
        $prices = array_map(
            fn (string $type) => $this->getBasePriceForContentType($type),
            $this->getAvailableContentTypes()
        );

        if (empty($prices)) {
            return (int) $this->price;
        }

        return min($prices);
    }

    public function getStartingEffectivePrice(): int
    {
        $prices = array_map(
            fn (string $type) => $this->getEffectivePriceForContentType($type),
            $this->getAvailableContentTypes()
        );

        if (empty($prices)) {
            return $this->is_discounted ? (int) $this->discounted_price : (int) $this->price;
        }

        return min($prices);
    }

    public function getHasVariablePricingAttribute(): bool
    {
        $prices = array_map(
            fn (string $type) => $this->getBasePriceForContentType($type),
            $this->getAvailableContentTypes()
        );

        if (count($prices) <= 1) {
            return false;
        }

        return count(array_unique($prices)) > 1;
    }

    public function getIsDiscountedAttribute(): bool
    {
        return $this->discounted_price !== null
            && $this->discount_expires_at !== null
            && $this->discount_expires_at->isFuture();
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_discounted || $this->starting_price == 0) {
            return 0;
        }

        return (int) round(100 - ($this->starting_effective_price / $this->starting_price * 100));
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
