<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $activePo = PoPeriod::active()->first();

        $query = Product::active();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        if ($request->filled('size_ml')) {
            $query->where('size_ml', $request->input('size_ml'));
        }

        $products = $query->latest()->paginate(8)->withQueryString();

        return view('pages.catalog', compact('activePo', 'products'));
    }
}

