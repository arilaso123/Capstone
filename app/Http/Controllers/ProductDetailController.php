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

        $relatedProducts = collect();
        if ($product->category) {
            $keyword = explode(' ', trim($product->category))[0];
            $relatedProducts = Product::active()
                ->with('variants')
                ->where('id', '!=', $product->id)
                ->where('category', 'like', '%' . $keyword . '%')
                ->take(4)
                ->get();
        }

        if ($relatedProducts->count() < 4) {
            $excludeIds = $relatedProducts->pluck('id')->push($product->id)->all();
            $fallback = Product::active()
                ->with('variants')
                ->whereNotIn('id', $excludeIds)
                ->latest()
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($fallback);
        }

        return view('pages.product-detail', compact('product', 'activePo', 'relatedProducts'));
    }
}
