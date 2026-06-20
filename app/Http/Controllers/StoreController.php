<?php

namespace App\Http\Controllers;

use App\Models\Product;

class StoreController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        return view('store.index', [
            'products' => $products,
            'categories' => Product::CATEGORIES,
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->active, 404);

        $related = Product::query()
            ->where('active', true)
            ->where('id', '!=', $product->id)
            ->where('category', $product->category)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        return view('store.show', compact('product', 'related'));
    }
}
