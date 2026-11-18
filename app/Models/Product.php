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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('size_ml');
    }

    public function variantForSize(int $sizeMl): ?ProductVariant
    {
        if ($this->relationLoaded('variants')) {
            return $this->getRelation('variants')->firstWhere('size_ml', $sizeMl);
        }

        return $this->variants()->where('size_ml', $sizeMl)->first();
    }

    public function availableSizes(): array
    {
        $sizes = $this->relationLoaded('variants')
            ? $this->getRelation('variants')->pluck('size_ml')
            : $this->variants()->pluck('size_ml');

        return $sizes->map(fn ($size) => (int) $size)->all();
    }

    public function startingPrice(): ?float
    {
        $prices = $this->relationLoaded('variants')
            ? $this->getRelation('variants')->pluck('price')
            : $this->variants()->pluck('price');

        return $prices->isEmpty() ? null : (float) $prices->min();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getPriceForSize(int $sizeMl): float
    {
        $variant = $this->variantForSize($sizeMl);

        if (!$variant) {
            throw new \InvalidArgumentException("Varian ukuran {$sizeMl} ml tidak tersedia untuk produk ini.");
        }

        return (float) $variant->price;
    }
}

