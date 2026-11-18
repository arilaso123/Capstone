<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoPeriodController extends Controller
{
    public function index()
    {
        $poPeriods = PoPeriod::withCount('orders')
            ->with('creator')
            ->latest()
            ->paginate(10);

        return view('owner.po-periods.index', compact('poPeriods'));
    }

    public function create()
    {
        return view('owner.po-periods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:Dibuka,Ditutup'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama periode PO wajib diisi.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal penutupan wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        DB::transaction(function () use ($validated) {
            // Lock the same owner row for all owner accounts, including when no PO is open yet.
            User::query()
                ->where('role', User::ROLE_OWNER)
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['status'] === PoPeriod::STATUS_OPEN) {
                PoPeriod::where('status', PoPeriod::STATUS_OPEN)->update(['status' => PoPeriod::STATUS_CLOSED]);
            }

            PoPeriod::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });
        PoPeriod::clearActiveCache();

        $statusMessage = $validated['status'] === PoPeriod::STATUS_OPEN
            ? ' Status administrasi Dibuka; checkout hanya tersedia dalam rentang tanggal PO WIB.'
            : '';

        return redirect()->route('owner.po-periods.index')
            ->with('success', 'Periode Pre-Order baru "' . $validated['name'] . '" berhasil dibuat.' . $statusMessage);
    }

    public function toggleStatus($id)
    {
        $msg = DB::transaction(function () use ($id) {
            User::query()
                ->where('role', User::ROLE_OWNER)
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();
            $poPeriod = PoPeriod::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($poPeriod->isOpen()) {
                $poPeriod->update(['status' => PoPeriod::STATUS_CLOSED]);
                return "Periode '{$poPeriod->name}' resmi DITUTUP. Pelanggan tidak dapat checkout lagi pada batch ini.";
            }

            PoPeriod::where('status', PoPeriod::STATUS_OPEN)->update(['status' => PoPeriod::STATUS_CLOSED]);
            $poPeriod->update(['status' => PoPeriod::STATUS_OPEN]);

            return $poPeriod->isOrderableNow()
                ? "Periode '{$poPeriod->name}' dibuka; checkout tersedia sampai tanggal akhir PO pukul 23.59 WIB."
                : "Status periode '{$poPeriod->name}' dibuka. Checkout hanya tersedia di antara tanggal mulai dan tanggal akhir PO (WIB).";
        });
        PoPeriod::clearActiveCache();

        return redirect()->route('owner.po-periods.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $poPeriod = PoPeriod::withCount('orders')->findOrFail($id);
        return view('owner.po-periods.edit', compact('poPeriod'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = DB::transaction(function () use ($id, $validated) {
            $period = PoPeriod::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            $hasOrders = $period->orders()->exists();
            if ($hasOrders && (
                $period->start_date->toDateString() !== $validated['start_date']
                || $period->end_date->toDateString() !== $validated['end_date']
            )) {
                return false;
            }

            $period->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'notes' => $validated['notes'] ?? null,
            ]);
            return true;
        });

        if (!$result) {
            return back()->withInput()->with('error', 'Tanggal periode tidak dapat diubah setelah periode tersebut memiliki pesanan. Nama dan catatan masih dapat diperbarui.');
        }

        PoPeriod::clearActiveCache();
        return redirect()->route('owner.po-periods.index')->with('success', 'Periode PO berhasil diperbarui.');
    }
}

