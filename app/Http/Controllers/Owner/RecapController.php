<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'po_period_id' => ['nullable', 'integer', 'exists:po_periods,id'],
        ]);

        $poPeriods = PoPeriod::withCount('orders')->latest()->get();
        $selectedPoPeriod = $this->resolvePeriod($validated['po_period_id'] ?? null, $poPeriods);

        [$recapData, $summary] = $this->buildRecap($selectedPoPeriod);

        return view('owner.recap.index', compact('poPeriods', 'selectedPoPeriod', 'recapData', 'summary'));
    }

    public function exportCsv(Request $request)
    {
        $validated = $request->validate([
            'po_period_id' => ['nullable', 'integer', 'exists:po_periods,id'],
        ]);

        $poPeriods = PoPeriod::withCount('orders')->latest()->get();
        $selectedPoPeriod = $this->resolvePeriod($validated['po_period_id'] ?? null, $poPeriods);

        if (!$selectedPoPeriod) {
            return redirect()->route('owner.recap.index')->with('warning', 'Tidak ada periode PO yang tersedia untuk diekspor.');
        }

        [$recapData, $summary] = $this->buildRecap($selectedPoPeriod);

        $safeSlug = trim((string) preg_replace('/[^A-Za-z0-9\-_]+/', '_', $selectedPoPeriod->name), '_');
        if ($safeSlug === '') {
            $safeSlug = 'PO_' . $selectedPoPeriod->id;
        }
        $filename = 'Rekap_Belanja_UyuyJastip_' . $safeSlug . '_' . now('Asia/Jakarta')->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($selectedPoPeriod, $recapData, $summary) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['REKAPITULASI BELANJA VENDOR IN PARFUME — UYUY JASTIP']);
            fputcsv($handle, ['Periode PO', $this->sanitizeCsvCell($selectedPoPeriod->name)]);
            fputcsv($handle, ['Jadwal Batch', $selectedPoPeriod->start_date->format('d M Y') . ' s.d. ' . $selectedPoPeriod->end_date->format('d M Y')]);
            fputcsv($handle, ['Status PO', $this->sanitizeCsvCell($selectedPoPeriod->status)]);
            fputcsv($handle, ['Diekspor Pada', now('Asia/Jakarta')->format('d M Y H:i') . ' WIB']);
            fputcsv($handle, []);

            fputcsv($handle, ['No', 'Nama Varian Parfum', 'Kategori Aroma', 'Ukuran (ml)', 'Unit Terverifikasi (Botol)', 'Modal Tercatat (Rp)', 'Nilai Pesanan Valid (Rp)']);

            foreach ($recapData as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $this->sanitizeCsvCell((string) $item['product_name']),
                    $this->sanitizeCsvCell((string) $item['category']),
                    $item['size_ml'],
                    (int) $item['total_quantity'],
                    (float) $item['total_cost'],
                    (float) $item['total_subtotal'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['', 'TOTAL KESELURUHAN', '', '', (int) $summary['total_items'], (float) $summary['total_cost'], (float) $summary['total_revenue']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function sanitizeCsvCell(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    private function resolvePeriod(?int $selectedPoPeriodId, $poPeriods): ?PoPeriod
    {
        if ($selectedPoPeriodId) {
            return PoPeriod::find($selectedPoPeriodId);
        }

        return PoPeriod::active()->first() ?? $poPeriods->first();
    }

    private function buildRecap(?PoPeriod $selectedPoPeriod): array
    {
        $recapData = collect();
        $summary = [
            'total_orders' => 0,
            'total_items' => 0,
            'total_revenue' => 0,
            'total_cost' => 0,
            'cost_tracked_items' => 0,
            'total_customers' => 0,
            'verified_orders' => 0,
            'unpaid_orders' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
        ];

        if ($selectedPoPeriod) {
            $ordersQuery = Order::where('po_period_id', $selectedPoPeriod->id);
            $verifiedOrdersQuery = (clone $ordersQuery)
                ->whereHas('payment', fn ($payments) => $payments->where('verification_status', Payment::STATUS_VALID));

            // Procurement quantities include only orders whose payment was manually verified.
            $recapData = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_details.product_id')
                ->where('orders.po_period_id', $selectedPoPeriod->id)
                ->whereExists(function ($payments) {
                    $payments->selectRaw('1')->from('payments')
                        ->whereColumn('payments.order_id', 'orders.id')
                        ->where('payments.verification_status', Payment::STATUS_VALID);
                })
                ->selectRaw('order_details.product_id, order_details.size_ml, MIN(order_details.product_name) AS product_name, COALESCE(MIN(products.category), ?) AS category', ['General'])
                ->selectRaw('SUM(order_details.quantity) AS total_quantity, SUM(order_details.subtotal) AS total_subtotal')
                ->selectRaw('SUM(CASE WHEN order_details.cost_price IS NOT NULL THEN order_details.cost_price * order_details.quantity ELSE 0 END) AS total_cost')
                ->selectRaw('SUM(CASE WHEN order_details.cost_price IS NOT NULL THEN order_details.quantity ELSE 0 END) AS cost_tracked_quantity')
                ->selectRaw('COUNT(DISTINCT order_details.order_id) AS order_count')
                ->groupBy('order_details.product_id', 'order_details.size_ml')
                ->orderByDesc('total_quantity')
                ->get()
                ->map(fn ($row) => (array) $row);

            $summary['total_orders'] = (clone $ordersQuery)->count();
            $summary['total_items'] = $recapData->sum('total_quantity');
            $summary['total_cost'] = $recapData->sum('total_cost');
            $summary['cost_tracked_items'] = $recapData->sum('cost_tracked_quantity');
            $summary['total_revenue'] = Payment::whereHas('order', fn ($orders) => $orders->where('po_period_id', $selectedPoPeriod->id))
                ->where('verification_status', Payment::STATUS_VALID)
                ->sum('amount');
            $summary['total_customers'] = (clone $verifiedOrdersQuery)->distinct('customer_id')->count('customer_id');
            $summary['verified_orders'] = (clone $verifiedOrdersQuery)->count();
            $summary['unpaid_orders'] = (clone $ordersQuery)
                ->where('status', Order::STATUS_WAITING_PAYMENT)
                ->whereDoesntHave('payment', fn ($payments) => $payments->where('verification_status', Payment::STATUS_VALID))
                ->count();
            $summary['completed_orders'] = (clone $ordersQuery)->where('status', Order::STATUS_COMPLETED)->count();
            $summary['cancelled_orders'] = (clone $ordersQuery)->where('status', Order::STATUS_CANCELLED)->count();
        }

        return [$recapData, $summary];
    }
}
