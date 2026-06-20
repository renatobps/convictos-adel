<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'price',
        'hide_price',
        'available_for_sale',
        'image',
        'sizes',
        'stock',
        'active',
        'featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sizes' => 'array',
        'hide_price' => 'boolean',
        'available_for_sale' => 'boolean',
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    public const CATEGORIES = [
        'jersey' => 'Camisa Oficial',
        'dryfit' => 'Dry Fit',
        'tee' => 'Camiseta',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function isPurchasable(): bool
    {
        return $this->active && $this->available_for_sale;
    }

    public function getPriceLabelAttribute(): string
    {
        if ($this->hide_price) {
            return 'Sob consulta';
        }

        return 'R$ ' . number_format((float) $this->price, 2, ',', '.');
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return asset('assets/logos/convictos-cor.png');
        }

        if (Str::startsWith($this->image, ['http://', 'https://', '/'])) {
            return $this->image;
        }

        // Imagens fixas (seed) ficam em public/assets; uploads do admin vao para o disco public (storage).
        if (Str::startsWith($this->image, 'assets/')) {
            return asset($this->image);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image);
    }
}
