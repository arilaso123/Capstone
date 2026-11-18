<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PoPeriod;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    /** @return Order|array{error:string} */
    public function execute(Customer $customer, array $cart, string $checkoutToken, array $validated, ?int $actorId): Order|array
    {
        return DB::transaction(function () use ($customer, $cart, $checkoutToken, $validated, $actorId) {
            $nowWib = now('Asia/Jakarta');
            $todayWib = $nowWib->toDateString();
            $openPeriods = PoPeriod::query()
                ->where('status', PoPeriod::STATUS_OPEN)
                ->whereDate('start_date', '<=', $todayWib)
                ->whereDate('end_date', '>=', $todayWib)
                ->sharedLock()
                ->get();

            // Shared locks allow concurrent orders while blocking an owner close until commit.
            $existingOrder = Order::query()->where('checkout_token', $checkoutToken)->lockForUpdate()->first();
            if ($existingOrder) {
                abort_unless($existingOrder->customer_id === $customer->id, 403, 'Token checkout tidak valid.');
                return $existingOrder;
            }

            if ($openPeriods->count() !== 1) {
                return ['error' => 'Periode PO tidak sedang aktif. Periksa kembali jadwal pemesanan.'];
            }

            $activePo = $openPeriods->first();
            $productIds = collect($cart)
                ->filter(fn ($item) => is_array($item) && isset($item['product_id'])
                    && filter_var($item['product_id'], FILTER_VALIDATE_INT) !== false)
                ->pluck('product_id')->unique()->values();
            $products = Product::active()->with('variants')->whereIn('id', $productIds)->get()->keyBy('id');
            $resolvedItems = [];
            $totalAmount = 0;

            foreach ($cart as $item) {
                if (!is_array($item)
                    || !isset($item['product_id'], $item['size_ml'], $item['quantity'])
                    || filter_var($item['product_id'], FILTER_VALIDATE_INT) === false
                    || filter_var($item['size_ml'], FILTER_VALIDATE_INT) === false
                    || filter_var($item['quantity'], FILTER_VALIDATE_INT) === false
                    || !in_array((int) $item['size_ml'], [35, 60, 100], true)
                    || (int) $item['quantity'] < 1 || (int) $item['quantity'] > 50
                    || !$products->has((int) $item['product_id'])) {
                    return ['error' => 'Ada item dengan rincian tidak valid di keranjang. Muat ulang keranjang sebelum checkout.'];
                }

                $product = $products->get((int) $item['product_id']);
                $size = (int) $item['size_ml'];
                $variant = $product->variantForSize($size);
                if (!$variant) {
                    return ['error' => "Varian {$product->name} ukuran {$size} ml sudah tidak tersedia. Perbarui keranjang Anda."];
                }

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $variant->price;
                $subtotal = round($unitPrice * $quantity, 2);
                $totalAmount += $subtotal;
                $resolvedItems[] = [
                    'product' => $product,
                    'size_ml' => $size,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_price' => $variant->cost_price === null ? null : (float) $variant->cost_price,
                    'subtotal' => $subtotal,
                ];
            }

            // ULID removes the shared counter query and its write-contention hotspot.
            $orderNumber = sprintf(
                'UJ-PO%d-%s-%s',
                $activePo->id,
                $nowWib->format('Ymd'),
                strtoupper((string) Str::ulid()),
            );
            $order = Order::create([
                'order_number' => $orderNumber,
                'checkout_token' => $checkoutToken,
                'customer_id' => $customer->id,
                'po_period_id' => $activePo->id,
                'total_amount' => $totalAmount,
                'payment_deadline_at' => $nowWib->copy()->addHours(max(1, (int) config('jastip.payment_deadline_hours', 24)))->utc(),
                'status' => Order::STATUS_WAITING_PAYMENT,
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? $customer->postal_code,
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            foreach ($resolvedItems as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'size_ml' => $item['size_ml'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
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
                'changed_by' => $actorId,
            ]);

            return $order;
        }, 3);
    }
}
