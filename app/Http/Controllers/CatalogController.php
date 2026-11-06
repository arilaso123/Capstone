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

        $categories = Product::active()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Setiap parfum dijual dalam 35, 60, dan 100 ml di halaman detail.
        // Kolom products.size_ml hanya menyimpan ukuran acuan (60 ml), jadi
        // pilihan ukuran di katalog mengubah harga yang ditampilkan, bukan menyembunyikan produk.
        $availableSizes = [35, 60, 100];
        $selectedSize = (int) $request->input('size_ml');
        if (! in_array($selectedSize, $availableSizes, true)) {
            $selectedSize = null;
        }

        $selectedCategory = $request->input('category');
        if (! $categories->contains($selectedCategory)) {
            $selectedCategory = null;
        }

        $query = Product::active();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        $products = $query->latest()->paginate(8)->withQueryString();

        return view('pages.catalog', compact(
            'activePo',
            'products',
            'categories',
            'availableSizes',
            'selectedSize',
            'selectedCategory'
        ));
    }
}

