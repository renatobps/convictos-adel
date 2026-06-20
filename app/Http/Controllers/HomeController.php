<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::query()
            ->where('active', true)
            ->where('featured', true)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        return view('home', compact('featured'));
    }
}
