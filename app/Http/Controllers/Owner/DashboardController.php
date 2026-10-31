<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::active()->first();

        $totalOrdersCount = Order::count();
        $totalCustomersCount = Customer::count();
        $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount');
        $activeProductsCount = Product::active()->count();

        // Breakdown for the active PO period (or all if none)
        $batchOrdersQuery = $activePo ? Order::where('po_period_id', $activePo->id) : Order::query();

        $statusBreakdown = [
            'waiting' => (clone $batchOrdersQuery)->where('status', Order::STATUS_WAITING_PAYMENT)->count(),
            'verified' => (clone $batchOrdersQuery)->where('status', Order::STATUS_PAYMENT_VERIFIED)->count(),
            'processing' => (clone $batchOrdersQuery)->where('status', Order::STATUS_PROCESSING)->count(),
            'shipped' => (clone $batchOrdersQuery)->where('status', Order::STATUS_SHIPPED)->count(),
            'completed' => (clone $batchOrdersQuery)->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $batchOrdersQuery)->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('owner.dashboard', compact(
            'activePo',
            'totalOrdersCount',
            'totalCustomersCount',
            'totalRevenue',
            'activeProductsCount',
            'statusBreakdown'
        ));
    }
}

