<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        return view('dashboard', [
            'todaySale' => (float) Transaction::where('sold_at', $today)->sum('total'),
            'todayCount' => Transaction::where('sold_at', $today)->count(),
            'productCount' => Product::count(),
            'totalSale' => (float) Transaction::sum('total'),
            'latest' => Transaction::orderByDesc('sold_at')->orderByDesc('id')->first(),
        ]);
    }
}
