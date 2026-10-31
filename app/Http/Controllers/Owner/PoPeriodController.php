<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PoPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // If newly created PO is opened, we can optionally close other open POs so there's only 1 active cycle
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

        return redirect()->route('owner.po-periods.index')
            ->with('success', 'Periode Pre-Order baru "' . $validated['name'] . '" berhasil dibuat.');
    }

    public function toggleStatus($id)
    {
        $poPeriod = PoPeriod::findOrFail($id);

        if ($poPeriod->isOpen()) {
            $poPeriod->update(['status' => PoPeriod::STATUS_CLOSED]);
            $msg = "Periode '{$poPeriod->name}' resmi DITUTUP. Pelanggan tidak dapat checkout lagi pada batch ini.";
        } else {
            // Close any currently open PO first
            PoPeriod::where('status', PoPeriod::STATUS_OPEN)->update(['status' => PoPeriod::STATUS_CLOSED]);
            $poPeriod->update(['status' => PoPeriod::STATUS_OPEN]);
            $msg = "Periode '{$poPeriod->name}' resmi DIBUKA. Pelanggan sekarang dapat melakukan pemesanan.";
        }

        return redirect()->route('owner.po-periods.index')->with('success', $msg);
    }
}

