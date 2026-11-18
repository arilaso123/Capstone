<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', Order::$statuses)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Order::with(['customer', 'poPeriod', 'payment', 'orderDetails']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['customer.user', 'poPeriod', 'orderDetails.product', 'payment', 'statusHistories.changer', 'cancellationRequest'])
            ->findOrFail($id);
        $nextStatuses = $order->nextOperationalStatuses();

        return view('admin.orders.show', compact('order', 'nextStatuses'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'new_status' => ['required', 'string', 'in:' . implode(',', Order::$statuses)],
            'remarks' => ['nullable', 'string', 'max:255'],
            'courier' => ['nullable', 'string', 'max:50'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
        ]);

        if ($validated['new_status'] === Order::STATUS_SHIPPED) {
            $request->validate([
                'courier' => ['required', 'string', 'max:50'],
                'tracking_number' => ['required', 'string', 'max:100'],
            ]);
        }

        $newStatus = $validated['new_status'];
        $order = DB::transaction(function () use ($id, $newStatus, $validated) {
            $order = Order::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if (!in_array($newStatus, $order->nextOperationalStatuses(), true)) {
                return null;
            }

            $previousStatus = $order->status;
            $before = ['status' => $previousStatus, 'courier' => $order->courier, 'tracking_number' => $order->tracking_number, 'shipping_cost' => $order->shipping_cost];
            $order->update([
                'status' => $newStatus,
                'courier' => $validated['courier'] ?? $order->courier,
                'tracking_number' => $validated['tracking_number'] ?? $order->tracking_number,
                'shipping_cost' => $validated['shipping_cost'] ?? $order->shipping_cost,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'remarks' => ($validated['remarks'] ?? null) ?: "Status diperbarui oleh Admin: $newStatus",
                'changed_by' => Auth::id(),
            ]);

            AuditLog::record('order.status_updated', $order, $before, [
                'status' => $order->status,
                'courier' => $order->courier,
                'tracking_number' => $order->tracking_number,
                'shipping_cost' => $order->shipping_cost,
            ]);

            return $order;
        });

        if (!$order) {
            return redirect()->back()->with('error', 'Transisi status pesanan ini tidak valid atau statusnya telah berubah. Muat ulang halaman lalu coba kembali.');
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi: {$newStatus}");
    }
}

