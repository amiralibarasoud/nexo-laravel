<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'order_number',
        'amount',
        'content_type',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function enrollment(): ?Enrollment
    {
        return Enrollment::where('order_id', $this->id)->first();
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}
