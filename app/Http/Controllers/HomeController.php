<?php

namespace App\Http\Controllers;

use App\Models\PoPeriod;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $activePo = PoPeriod::active()->first();
        $featuredProducts = Product::active()->take(4)->get();

        return view('pages.home', compact('activePo', 'featuredProducts'));
    }
}

