<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DecideCancellationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecideCancellationRequest;
use App\Models\CancellationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', [CancellationRequest::STATUS_PENDING, CancellationRequest::STATUS_APPROVED, CancellationRequest::STATUS_REJECTED])],
        ]);

        $query = CancellationRequest::with(['order.customer', 'reviewer']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $cancellations = $query->latest()->paginate(10)->withQueryString();

        return view('admin.cancellations.index', compact('cancellations'));
    }

    public function show($id)
    {
        $cancellation = CancellationRequest::with(['order.customer.user', 'order.orderDetails', 'order.payment'])->findOrFail($id);

        return view('admin.cancellations.show', compact('cancellation'));
    }

    public function decide(DecideCancellationRequest $request, $id, DecideCancellationAction $decideCancellation)
    {
        $validated = $request->validated();
        $decision = $validated['status'];
        $result = $decideCancellation->execute($id, $validated, $request->file('refund_proof'), Auth::id());

        if (isset($result['upload_error'])) {
            return back()->withInput()->with('error', $result['upload_error']);
        }

        if (isset($result['warning'])) {
            return redirect()->route('admin.cancellations.show', $id)->with('warning', $result['warning']);
        }

        return redirect()->route('admin.cancellations.index')
            ->with('success', "Putusan permohonan pembatalan untuk pesanan #{$result['order_number']} berhasil disimpan ({$decision}).");
    }
}

