<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'poPeriod', 'payment', 'orderDetails']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
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

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'new_status' => ['required', 'string', 'in:' . implode(',', Order::$statuses)],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $previousStatus = $order->status;
        $newStatus = $request->input('new_status');

        if ($previousStatus === $newStatus) {
            return redirect()->back()->with('warning', 'Status yang dipilih sama dengan status pesanan saat ini.');
        }

        if ($previousStatus === Order::STATUS_CANCELLED) {
            return redirect()->back()->with('error', 'Pesanan yang telah dibatalkan tidak dapat diubah statusnya lagi.');
        }

        if ($previousStatus === Order::STATUS_COMPLETED && $newStatus !== Order::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Pesanan yang telah selesai tidak dapat diubah kembali ke status sebelumnya.');
        }

        DB::transaction(function () use ($order, $previousStatus, $newStatus, $request) {
            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'remarks' => $request->input('remarks') ?: "Status diperbarui oleh Admin: $newStatus",
                'changed_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi: {$newStatus}");
    }
}

