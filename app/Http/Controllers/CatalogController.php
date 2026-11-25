<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'size_ml' => ['nullable', 'integer', 'in:35,60,100'],
            'sort' => ['nullable', 'string', 'in:latest,price_asc,price_desc,name_asc'],
        ]);

        $activePo = PoPeriod::cachedActive();

        $query = Product::active()->with('variants');

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['category'])) {
            $query->where('category', 'like', '%' . $validated['category'] . '%');
        }

        if (!empty($validated['size_ml'])) {
            $query->whereHas('variants', fn ($variants) => $variants->where('size_ml', $validated['size_ml']));
        }

        $sort = $validated['sort'] ?? 'latest';
        $effectiveMinPriceSql = 'COALESCE((SELECT MIN(pv.price) FROM product_variants pv WHERE pv.product_id = products.id), products.price)';

        match ($sort) {
            'price_asc' => $query->orderByRaw($effectiveMinPriceSql . ' ASC')->orderBy('products.id', 'asc'),
            'price_desc' => $query->orderByRaw($effectiveMinPriceSql . ' DESC')->orderBy('products.id', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->orderBy('products.id', 'asc'),
        };

        $products = $query->paginate(8)->withQueryString();

        return view('pages.catalog', compact('activePo', 'products', 'sort'));
    }
}
