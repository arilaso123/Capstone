<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancellationRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancellationController extends Controller
{
    public function index(Request $request)
    {
        $query = CancellationRequest::with(['order.customer', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $cancellations = $query->latest()->paginate(10)->withQueryString();

        return view('admin.cancellations.index', compact('cancellations'));
    }

    public function show($id)
    {
        $cancellation = CancellationRequest::with(['order.customer.user', 'order.orderDetails'])->findOrFail($id);

        return view('admin.cancellations.show', compact('cancellation'));
    }

    public function decide(Request $request, $id)
    {
        $cancellation = CancellationRequest::with('order')->findOrFail($id);

        if ($cancellation->status !== CancellationRequest::STATUS_PENDING) {
            return redirect()->route('admin.cancellations.show', $cancellation->id)
                ->with('warning', 'Permohonan pembatalan ini sudah pernah diproses sebelumnya dengan putusan: ' . $cancellation->status . '.');
        }

        $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $request->input('status');
        $notes = $request->input('admin_notes');

        DB::transaction(function () use ($cancellation, $decision, $notes) {
            $cancellation->update([
                'status' => $decision,
                'admin_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($decision === CancellationRequest::STATUS_APPROVED) {
                $order = $cancellation->order;
                $prevStatus = $order->status;
                $order->update(['status' => Order::STATUS_CANCELLED]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'previous_status' => $prevStatus,
                    'new_status' => Order::STATUS_CANCELLED,
                    'remarks' => 'Pesanan dibatalkan atas persetujuan permohonan pembatalan oleh Admin: ' . ($notes ?: 'Disetujui'),
                    'changed_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('admin.cancellations.index')
            ->with('success', "Putusan permohonan pembatalan untuk pesanan #{$cancellation->order->order_number} berhasil disimpan ({$decision}).");
    }
}

