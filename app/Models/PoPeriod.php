<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class PoPeriod extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'Dibuka';
    public const STATUS_CLOSED = 'Ditutup';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isWithinOrderWindow(): bool
    {
        $todayWib = now('Asia/Jakarta')->toDateString();

        return $this->start_date
            && $this->end_date
            && $this->start_date->toDateString() <= $todayWib
            && $this->end_date->toDateString() >= $todayWib;
    }

    public function isOrderableNow(): bool
    {
        return $this->isOpen() && $this->isWithinOrderWindow();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        $todayWib = now('Asia/Jakarta')->toDateString();

        return $query->where('status', self::STATUS_OPEN)
            ->whereDate('start_date', '<=', $todayWib)
            ->whereDate('end_date', '>=', $todayWib);
    }

    public static function cachedActive(): ?self
    {
        return Cache::remember('po.active-window', now()->addSeconds(30), fn () => static::active()->first());
    }

    public static function clearActiveCache(): void
    {
        Cache::forget('po.active-window');
    }
}

