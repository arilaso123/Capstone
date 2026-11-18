<?php

namespace App\Http\Controllers\Customer;

use App\Actions\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::cachedActive();
        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }

        $customer = Auth::user()->customer;
        $productIds = collect($cart)
            ->filter(fn ($item) => is_array($item)
                && isset($item['product_id'])
                && filter_var($item['product_id'], FILTER_VALIDATE_INT) !== false)
            ->pluck('product_id')
            ->unique()
            ->values();
        $products = Product::active()->with('variants')->whereIn('id', $productIds)->get()->keyBy('id');
        $cartChanged = false;

        $total = 0;
        foreach ($cart as $key => $item) {
            if (!is_array($item)
                || !isset($item['product_id'], $item['size_ml'], $item['quantity'])
                || filter_var($item['product_id'], FILTER_VALIDATE_INT) === false
                || filter_var($item['size_ml'], FILTER_VALIDATE_INT) === false
                || !in_array((int) $item['size_ml'], [35, 60, 100], true)
                || filter_var($item['quantity'], FILTER_VALIDATE_INT) === false
                || (int) $item['quantity'] < 1
                || (int) $item['quantity'] > 50
                || !$products->has((int) $item['product_id'])) {
                unset($cart[$key]);
                $cartChanged = true;
                continue;
            }

            $product = $products->get((int) $item['product_id']);
            $size = (int) $item['size_ml'];
            $variant = $product->variantForSize($size);
            if (!$variant) {
                unset($cart[$key]);
                $cartChanged = true;
                continue;
            }
            $quantity = (int) $item['quantity'];
            $price = (float) $variant->price;
            $cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'size_ml' => $size,
                'price' => $price,
                'quantity' => $quantity,
                'image_path' => $product->image_path,
            ];
            $total += $price * $quantity;
        }

        if ($cartChanged) {
            session()->put('cart', $cart);
            $this->rotateCheckoutToken();
            session()->now('warning', 'Item yang tidak tersedia atau memiliki rincian tidak valid telah dihapus dari keranjang. Harga item yang tersisa diperbarui.');
        } elseif (session()->get('cart') !== $cart) {
            session()->put('cart', $cart);
        }

        $checkoutToken = null;
        if ($cart !== []) {
            $checkoutToken = session('checkout_token');
            if (!is_string($checkoutToken) || !Str::isUuid($checkoutToken)) {
                $checkoutToken = (string) Str::uuid();
                session()->put('checkout_token', $checkoutToken);
            }
        }

        return view('customer.cart', compact('activePo', 'cart', 'customer', 'total', 'checkoutToken'));
    }

    public function add(Request $request)
    {
        $activePo = PoPeriod::active()->first();
        if (!$activePo || !$activePo->isOrderableNow()) {
            return redirect()->back()->with('error', 'Pemesanan tidak dapat dilakukan karena periode Pre-Order sedang DITUTUP.');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'size_ml' => ['required', 'integer', 'in:35,60,100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::active()->with('variants')->findOrFail($validated['product_id']);
        $variant = $product->variantForSize((int) $validated['size_ml']);
        if (!$variant) {
            return back()->withInput()->with('error', 'Ukuran yang dipilih sedang tidak tersedia. Silakan pilih ukuran lain.');
        }
        $unitPrice = (float) $variant->price;

        $cartKey = $product->id . '_' . $validated['size_ml'];
        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }

        if (isset($cart[$cartKey])) {
            $newQuantity = (int)($cart[$cartKey]['quantity'] ?? 0) + (int)$validated['quantity'];
            if ($newQuantity > 50) {
                return redirect()->route('customer.cart')->with('error', 'Jumlah untuk setiap varian parfum maksimal 50 botol.');
            }
            $cart[$cartKey]['quantity'] = $newQuantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'size_ml' => (int)$validated['size_ml'],
                'price' => $unitPrice,
                'quantity' => (int)$validated['quantity'],
                'image_path' => $product->image_path,
            ];
        }

        session()->put('cart', $cart);
        $this->rotateCheckoutToken();

        return redirect()->route('customer.cart')->with('success', "{$product->name} ({$validated['size_ml']}ml) berhasil ditambahkan ke keranjang pre-order.");
    }

    public function remove($key)
    {
        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
            $this->rotateCheckoutToken();
        }
        return redirect()->route('customer.cart')->with('info', 'Item berhasil dihapus dari keranjang.');
    }

    public function update(Request $request, string $key)
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:50']]);
        $cart = session()->get('cart', []);
        if (!is_array($cart) || !isset($cart[$key]) || !is_array($cart[$key])) {
            return redirect()->route('customer.cart')->with('warning', 'Item keranjang tidak ditemukan. Muat ulang halaman.');
        }

        $cart[$key]['quantity'] = (int) $validated['quantity'];
        session()->put('cart', $cart);
        $this->rotateCheckoutToken();

        return redirect()->route('customer.cart')->with('success', 'Jumlah item keranjang berhasil diperbarui.');
    }

    public function checkout(CheckoutRequest $request, CreateOrderAction $createOrder)
    {
        $validated = $request->validated();
        $checkoutToken = $validated['checkout_token'];

        $customer = Auth::user()->customer;
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $existingOrder = Order::where('checkout_token', $checkoutToken)->first();
        if ($existingOrder) {
            abort_unless($existingOrder->customer_id === $customer->id, 403, 'Token checkout tidak valid.');

            return redirect()->route('customer.orders.show', $existingOrder->id)
                ->with('info', 'Checkout ini sudah tercatat; pesanan yang sama ditampilkan kembali.');
        }

        if (!hash_equals((string) session('checkout_token', ''), $checkoutToken)) {
            return redirect()->route('customer.cart')->with('warning', 'Sesi checkout sudah berubah. Muat ulang keranjang sebelum membuat pesanan.');
        }

        $cart = session()->get('cart', []);
        if (!is_array($cart) || empty($cart)) {
            return redirect()->route('customer.cart')->with('error', 'Keranjang Anda kosong. Silakan pilih parfum di katalog terlebih dahulu.');
        }

        $checkoutResult = $createOrder->execute($customer, $cart, $checkoutToken, $validated, Auth::id());

        if (isset($checkoutResult['error'])) {
            return redirect()->route('customer.cart')->with('error', $checkoutResult['error']);
        }
        $order = $checkoutResult;

        if (hash_equals((string) session('checkout_token', ''), $checkoutToken)) {
            session()->forget(['cart', 'checkout_token']);
        }

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Pesanan #' . $order->order_number . ' berhasil dibuat! Silakan lanjutkan pembayaran melalui BCA Merchant.');
    }

    private function rotateCheckoutToken(): void
    {
        session()->put('checkout_token', (string) Str::uuid());
    }
}

