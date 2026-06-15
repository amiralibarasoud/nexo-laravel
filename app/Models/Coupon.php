<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_amount',
        'min_order_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    public function isValidForUser(int $userId): bool
    {
        if (!$this->isValid()) return false;
        $userUsage = $this->usages()->where('user_id', $userId)->count();
        return $userUsage < $this->per_user_limit;
    }

    public function isValidForAmount(int $amount): bool
    {
        return $amount >= $this->min_order_amount;
    }

    /**
     * محاسبه مقدار تخفیف
     */
    public function calculateDiscount(int $amount): int
    {
        if ($this->type === 'percent') {
            $discount = (int) round($amount * $this->value / 100);
            if ($this->max_amount) {
                $discount = min($discount, $this->max_amount);
            }
            return $discount;
        }
        // fixed
        return min($this->value, $amount);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'percent'
            ? toFarsiNumber($this->value) . '٪'
            : price($this->value);
    }
}
