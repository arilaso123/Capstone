<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $customer = $user->customer;

        $activePo = PoPeriod::active()->first();

        $ordersQuery = $customer ? $customer->orders() : Order::whereNull('id');

        $totalOrders = (clone $ordersQuery)->count();
        $waitingPayment = (clone $ordersQuery)->where('status', Order::STATUS_WAITING_PAYMENT)->count();
        $processing = (clone $ordersQuery)->whereIn('status', [Order::STATUS_PAYMENT_VERIFIED, Order::STATUS_PROCESSING])->count();
        $completed = (clone $ordersQuery)->where('status', Order::STATUS_COMPLETED)->count();

        $recentOrders = (clone $ordersQuery)->with(['poPeriod', 'payment'])->latest()->take(5)->get();

        return view('customer.dashboard', compact(
            'customer',
            'activePo',
            'totalOrders',
            'waitingPayment',
            'processing',
            'completed',
            'recentOrders'
        ));
    }
}

