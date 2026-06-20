<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected Cart $cart)
    {
    }

    public function index()
    {
        return view('cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:20'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::where('active', true)->findOrFail($data['product_id']);

        $this->cart->add($product, $data['size'] ?? null, $data['quantity'] ?? 1);

        return redirect()->route('cart.index')
            ->with('success', "{$product->name} adicionado ao carrinho.");
    }

    public function update(Request $request, string $rowId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $this->cart->update($rowId, $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function remove(string $rowId)
    {
        $this->cart->remove($rowId);

        return redirect()->route('cart.index')->with('success', 'Item removido do carrinho.');
    }
}
