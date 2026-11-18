<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::cachedActive();
        $featuredProducts = Product::active()->with('variants')->take(4)->get();

        return view('pages.home', compact('activePo', 'featuredProducts'));
    }
}

