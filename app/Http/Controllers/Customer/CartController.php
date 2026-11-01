<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::active()->first();
        $cart = session()->get('cart', []);
        $customer = Auth::user()->customer;

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('customer.cart', compact('activePo', 'cart', 'customer', 'total'));
    }

    public function add(Request $request)
    {
        $activePo = PoPeriod::active()->first();
        if (!$activePo || !$activePo->isOpen()) {
            return redirect()->back()->with('error', 'Pemesanan tidak dapat dilakukan karena periode Pre-Order sedang DITUTUP.');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size_ml' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Pricing logic: dynamic price by size from official In Parfume tier
        $unitPrice = $product->getPriceForSize((int)$validated['size_ml']);

        $cartKey = $product->id . '_' . $validated['size_ml'];
        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int)$validated['quantity'];
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

        return redirect()->route('customer.cart')->with('success', "{$product->name} ({$validated['size_ml']}ml) berhasil ditambahkan ke keranjang pre-order.");
    }

    public function remove($key)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }
        return redirect()->route('customer.cart')->with('info', 'Item berhasil dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $activePo = PoPeriod::active()->first();
        if (!$activePo || !$activePo->isOpen()) {
            return redirect()->route('customer.cart')->with('error', 'Pemesanan gagal: Periode Pre-Order sedang DITUTUP.');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.cart')->with('error', 'Keranjang Anda kosong. Silakan pilih parfum di katalog terlebih dahulu.');
        }

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_phone.required' => 'Nomor WhatsApp penerima wajib diisi.',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
        ]);

        $customer = Auth::user()->customer;
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Profil pelanggan tidak ditemukan.');
        }

        $order = DB::transaction(function () use ($validated, $customer, $activePo, $cart) {
            $totalAmount = 0;
            foreach ($cart as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Generate unique collision-safe order number: UJ-PO{po_id}-{YYYYMMDD}-{sequence}
            $today = date('Ymd');
            $seq = Order::whereDate('created_at', today())->count() + 1;
            do {
                $orderNumber = sprintf('UJ-PO%d-%s-%03d', $activePo->id, $today, $seq);
                $exists = Order::where('order_number', $orderNumber)->exists();
                if ($exists) {
                    $seq++;
                }
            } while ($exists);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'po_period_id' => $activePo->id,
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_WAITING_PAYMENT,
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'shipping_address' => $validated['shipping_address'],
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            foreach ($cart as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'size_ml' => $item['size_ml'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'BCA Merchant',
                'amount' => $totalAmount,
                'verification_status' => Payment::STATUS_PENDING,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => null,
                'new_status' => Order::STATUS_WAITING_PAYMENT,
                'remarks' => 'Pesanan berhasil dibuat oleh pelanggan pada ' . $activePo->name . '.',
                'changed_by' => Auth::id(),
            ]);

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Pesanan #' . $order->order_number . ' berhasil dibuat! Silakan lanjutkan pembayaran melalui BCA Merchant.');
    }
}

