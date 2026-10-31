<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;

class ProductDetailController extends Controller
{
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $activePo = PoPeriod::active()->first();

        return view('pages.product-detail', compact('product', 'activePo'));
    }
}

