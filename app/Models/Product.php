<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'category',
        'size_ml',
        'price',
        'description',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'size_ml' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get price dynamically for size (35ml, 60ml, 100ml) based on official In Parfume tier.
     */
    public function getPriceForSize(int $sizeMl): float
    {
        if ($sizeMl === 35) {
            return (float) ($this->price >= 110000 ? 85000 : ($this->price >= 90000 ? 65000 : 55000));
        }
        if ($sizeMl === 100) {
            return (float) ($this->price >= 110000 ? 180000 : ($this->price >= 90000 ? 140000 : 120000));
        }
        return (float) $this->price;
    }
}

