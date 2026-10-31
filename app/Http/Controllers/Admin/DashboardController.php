<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CancellationRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingPaymentsCount = Payment::where('verification_status', Payment::STATUS_PENDING)
            ->whereNotNull('proof_file_path')
            ->count();

        $pendingCancellationsCount = CancellationRequest::where('status', CancellationRequest::STATUS_PENDING)->count();

        $ordersToProcessCount = Order::where('status', Order::STATUS_PAYMENT_VERIFIED)->count();
        $ordersShippedCount = Order::where('status', Order::STATUS_SHIPPED)->count();

        $recentOrders = Order::with(['customer', 'poPeriod', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingPaymentsCount',
            'pendingCancellationsCount',
            'ordersToProcessCount',
            'ordersShippedCount',
            'recentOrders'
        ));
    }
}

