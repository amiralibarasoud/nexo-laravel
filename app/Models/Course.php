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
        return $this->starting_effective_price;
    }

    public function getBasePriceForContentType(string $contentType): int
    {
        $fallback = (int) ($this->attributes['price'] ?? 0);

        return match ($contentType) {
            'text' => array_key_exists('price_text', $this->attributes) && $this->attributes['price_text'] !== null
                ? (int) $this->attributes['price_text']
                : $fallback,
            'audio' => array_key_exists('price_audio', $this->attributes) && $this->attributes['price_audio'] !== null
                ? (int) $this->attributes['price_audio']
                : $fallback,
            'both' => array_key_exists('price_both', $this->attributes) && $this->attributes['price_both'] !== null
                ? (int) $this->attributes['price_both']
                : $fallback,
            default => $fallback,
        };
    }

    public function getEffectivePriceForContentType(string $contentType): int
    {
        $basePrice = $this->getBasePriceForContentType($contentType);
        $referencePrice = (int) ($this->attributes['price'] ?? 0);

        if ($this->is_discounted && $referencePrice > 0 && $this->discounted_price !== null) {
            $ratio = ((int) $this->discounted_price) / $referencePrice;

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
            return (int) ($this->attributes['price'] ?? 0);
        }

        return (int) min($prices);
    }

    public function getStartingEffectivePriceAttribute(): int
    {
        $prices = array_map(
            fn (string $type) => $this->getEffectivePriceForContentType($type),
            $this->getAvailableContentTypes()
        );

        if (empty($prices)) {
            return $this->is_discounted
                ? (int) $this->discounted_price
                : (int) ($this->attributes['price'] ?? 0);
        }

        return (int) min($prices);
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
        if ($this->discounted_price === null || blank($this->discount_expires_at)) {
            return false;
        }

        try {
            return $this->discount_expires_at->isFuture();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDiscountPercentAttribute(): int
    {
        $startingPrice = $this->starting_price;

        if (!$this->is_discounted || $startingPrice <= 0) {
            return 0;
        }

        return (int) round(100 - ($this->starting_effective_price / $startingPrice * 100));
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
