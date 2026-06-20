<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class Cart
{
    protected const KEY = 'cart';

    public function items(): array
    {
        return Session::get(self::KEY, []);
    }

    public function add(Product $product, ?string $size = null, int $quantity = 1): void
    {
        $items = $this->items();
        $rowId = $this->rowId($product->id, $size);

        if (isset($items[$rowId])) {
            $items[$rowId]['quantity'] += $quantity;
        } else {
            $items[$rowId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'size' => $size,
                'quantity' => $quantity,
                'image' => $product->image_url,
            ];
        }

        Session::put(self::KEY, $items);
    }

    public function update(string $rowId, int $quantity): void
    {
        $items = $this->items();

        if (! isset($items[$rowId])) {
            return;
        }

        if ($quantity <= 0) {
            unset($items[$rowId]);
        } else {
            $items[$rowId]['quantity'] = $quantity;
        }

        Session::put(self::KEY, $items);
    }

    public function remove(string $rowId): void
    {
        $items = $this->items();
        unset($items[$rowId]);
        Session::put(self::KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function total(): float
    {
        $total = 0;
        foreach ($this->items() as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function isEmpty(): bool
    {
        return count($this->items()) === 0;
    }

    public function rowId(int $productId, ?string $size = null): string
    {
        return md5($productId . '|' . ($size ?? ''));
    }
}
