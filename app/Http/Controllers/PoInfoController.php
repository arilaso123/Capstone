<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;

class PoInfoController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::active()->first();
        $pastPoPeriods = PoPeriod::where('status', PoPeriod::STATUS_CLOSED)
            ->latest()
            ->take(5)
            ->get();

        return view('pages.po-info', compact('activePo', 'pastPoPeriods'));
    }
}

