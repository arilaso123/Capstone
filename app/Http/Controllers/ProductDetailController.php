<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;

class ProductDetailController extends Controller
{
    public function show($id)
    {
        $product = Product::active()->with('variants')->findOrFail($id);
        $activePo = PoPeriod::cachedActive();

        return view('pages.product-detail', compact('product', 'activePo'));
    }
}

