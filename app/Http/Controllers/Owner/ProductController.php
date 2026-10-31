<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('owner.products.index', compact('products'));
    }

    public function create()
    {
        return view('owner.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'size_ml' => ['required', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name' => $validated['name'],
            'brand' => $validated['brand'],
            'category' => $validated['category'] ?? null,
            'size_ml' => $validated['size_ml'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => true,
        ]);

        return redirect()->route('owner.products.index')
            ->with('success', 'Produk parfum baru "' . $validated['name'] . '" berhasil ditambahkan ke katalog.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('owner.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'size_ml' => ['required', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && !str_starts_with($imagePath, 'http')) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $validated['name'],
            'brand' => $validated['brand'],
            'category' => $validated['category'] ?? null,
            'size_ml' => $validated['size_ml'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('owner.products.index')
            ->with('success', 'Informasi produk parfum "' . $product->name . '" berhasil diperbarui.');
    }

    public function toggle($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('owner.products.index')
            ->with('info', "Produk {$product->name} berhasil {$statusStr}.");
    }
}

