<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('variants')->latest()->paginate(10);
        return view('owner.products.index', compact('products'));
    }

    public function create()
    {
        return view('owner.products.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request, false);
        $variants = $this->normalizeVariants($validated['variants']);
        if ($variants === []) {
            return back()->withErrors(['variants' => 'Isi harga minimal untuk satu ukuran parfum.'])->withInput();
        }

        $imagePath = $request->file('image')?->store('products', 'public');
        if ($request->hasFile('image') && !$imagePath) {
            return back()->withErrors(['image' => 'Gambar produk gagal disimpan. Silakan coba kembali.'])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $variants, $imagePath): void {
                $primary = $variants[0];
                $product = Product::create([
                    'name' => $validated['name'],
                    'brand' => $validated['brand'],
                    'category' => $validated['category'] ?? null,
                    'size_ml' => $primary['size_ml'],
                    'price' => $primary['price'],
                    'description' => $validated['description'] ?? null,
                    'image_path' => $imagePath,
                    'is_active' => true,
                ]);
                $product->variants()->createMany($variants);
                AuditLog::record('product.created', $product, null, [
                    'name' => $product->name,
                    'variants' => $product->variants()->get(['size_ml', 'price', 'cost_price'])->toArray(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $exception;
        }

        return redirect()->route('owner.products.index')
            ->with('success', 'Produk parfum baru "' . $validated['name'] . '" berhasil ditambahkan ke katalog.');
    }

    public function edit($id)
    {
        $product = Product::with('variants')->findOrFail($id);
        return view('owner.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $this->validateProduct($request, true);
        $variants = $this->normalizeVariants($validated['variants']);
        if ($variants === []) {
            return back()->withErrors(['variants' => 'Isi harga minimal untuk satu ukuran parfum.'])->withInput();
        }

        $previousImagePath = $product->image_path;
        $imagePath = $request->file('image')?->store('products', 'public') ?? $previousImagePath;
        if ($request->hasFile('image') && !$imagePath) {
            return back()->withErrors(['image' => 'Gambar produk gagal disimpan. Silakan coba kembali.'])->withInput();
        }

        try {
            DB::transaction(function () use ($product, $validated, $variants, $imagePath): void {
                $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
                $before = [
                    'name' => $lockedProduct->name,
                    'variants' => $lockedProduct->variants()->get(['size_ml', 'price', 'cost_price'])->toArray(),
                ];
                $primary = $variants[0];
                $lockedProduct->update([
                    'name' => $validated['name'],
                    'brand' => $validated['brand'],
                    'category' => $validated['category'] ?? null,
                    'size_ml' => $primary['size_ml'],
                    'price' => $primary['price'],
                    'description' => $validated['description'] ?? null,
                    'image_path' => $imagePath,
                    'is_active' => (bool) $validated['is_active'],
                ]);

                $sizes = array_column($variants, 'size_ml');
                $lockedProduct->variants()->whereNotIn('size_ml', $sizes)->delete();
                foreach ($variants as $variant) {
                    ProductVariant::updateOrCreate(
                        ['product_id' => $lockedProduct->id, 'size_ml' => $variant['size_ml']],
                        ['price' => $variant['price'], 'cost_price' => $variant['cost_price']],
                    );
                }
                AuditLog::record('product.updated', $lockedProduct, $before, [
                    'name' => $lockedProduct->name,
                    'variants' => $lockedProduct->variants()->get(['size_ml', 'price', 'cost_price'])->toArray(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($imagePath !== $previousImagePath && $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $exception;
        }

        if ($imagePath !== $previousImagePath && $previousImagePath && !str_starts_with($previousImagePath, 'http')) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return redirect()->route('owner.products.index')
            ->with('success', 'Informasi produk parfum "' . $product->name . '" berhasil diperbarui.');
    }

    public function toggle($id)
    {
        $product = DB::transaction(function () use ($id) {
            $product = Product::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            $product->update(['is_active' => !$product->is_active]);
            return $product;
        });

        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('owner.products.index')->with('info', "Produk {$product->name} berhasil {$statusStr}.");
    }

    private function validateProduct(Request $request, bool $updating): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'variants' => ['required', 'array:35,60,100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:1', 'max:9999999999'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => $updating ? ['required', 'boolean'] : ['prohibited'],
        ]);
    }

    /** @return array<int, array{size_ml:int, price:float, cost_price:float|null}> */
    private function normalizeVariants(array $input): array
    {
        $variants = [];
        foreach ([35, 60, 100] as $size) {
            $price = $input[$size]['price'] ?? null;
            if ($price === null || $price === '') {
                continue;
            }
            $cost = $input[$size]['cost_price'] ?? null;
            $variants[] = [
                'size_ml' => $size,
                'price' => (float) $price,
                'cost_price' => $cost === null || $cost === '' ? null : (float) $cost,
            ];
        }
        return $variants;
    }
}
