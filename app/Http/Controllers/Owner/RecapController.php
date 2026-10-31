<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        $poPeriods = PoPeriod::withCount('orders')->latest()->get();

        $selectedPoPeriodId = $request->input('po_period_id');
        if ($selectedPoPeriodId) {
            $selectedPoPeriod = PoPeriod::find($selectedPoPeriodId);
        } else {
            $selectedPoPeriod = PoPeriod::active()->first() ?? $poPeriods->first();
        }

        $recapData = collect();
        $summary = [
            'total_orders' => 0,
            'total_items' => 0,
            'total_revenue' => 0,
            'total_customers' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
        ];

        if ($selectedPoPeriod) {
            $ordersQuery = Order::where('po_period_id', $selectedPoPeriod->id);
            $nonCancelledOrdersQuery = (clone $ordersQuery)->where('status', '!=', Order::STATUS_CANCELLED);
            $nonCancelledOrderIds = (clone $nonCancelledOrdersQuery)->pluck('id');

            // Aggregate order details by product_id and size_ml
            $recapData = OrderDetail::whereIn('order_id', $nonCancelledOrderIds)
                ->with('product')
                ->get()
                ->groupBy(function ($item) {
                    return $item->product_id . '_' . $item->size_ml;
                })
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'product_name' => $first->product_name ?? $first->product?->name ?? 'Parfum',
                        'category' => $first->product?->category ?? 'General',
                        'size_ml' => $first->size_ml,
                        'total_quantity' => $items->sum('quantity'),
                        'total_subtotal' => $items->sum('subtotal'),
                        'order_count' => $items->pluck('order_id')->unique()->count(),
                    ];
                })
                ->sortByDesc('total_quantity')
                ->values();

            $summary['total_orders'] = (clone $ordersQuery)->count();
            $summary['total_items'] = $recapData->sum('total_quantity');
            $summary['total_revenue'] = (clone $nonCancelledOrdersQuery)->sum('total_amount');
            $summary['total_customers'] = (clone $ordersQuery)->distinct('customer_id')->count('customer_id');
            $summary['completed_orders'] = (clone $ordersQuery)->where('status', Order::STATUS_COMPLETED)->count();
            $summary['cancelled_orders'] = (clone $ordersQuery)->where('status', Order::STATUS_CANCELLED)->count();
        }

        return view('owner.recap.index', compact('poPeriods', 'selectedPoPeriod', 'recapData', 'summary'));
    }
}
