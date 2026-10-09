<?php

use Illuminate\Support\Facades\Route;

// TEMPORARY sample data for the front-end. Back-end: replace with database queries, e.g. Product::all()
function sample_products(): array
{
    return [
        ['id' => 1, 'name' => 'Rice', 'category' => 'Grains', 'price' => 55, 'stock' => 100],
        ['id' => 2, 'name' => 'Tomatoes', 'category' => 'Vegetables', 'price' => 60, 'stock' => 40],
        ['id' => 3, 'name' => 'Onions', 'category' => 'Vegetables', 'price' => 120, 'stock' => 60],
        ['id' => 4, 'name' => 'Corn', 'category' => 'Grains', 'price' => 48, 'stock' => 75],
        ['id' => 5, 'name' => 'Dog Food 1kg', 'category' => 'Pet Food', 'price' => 180, 'stock' => 30],
    ];
}

// TEMPORARY price history based on the current sample product catalog.
function sample_price_records(): array
{
    $records = [];
    foreach (sample_products() as $index => $product) {
        $records[] = [
            'date' => date('Y-m-d', strtotime('-' . $index . ' days')),
            'product' => $product['name'],
            'category' => $product['category'],
            'old_price' => round($product['price'] * 0.95, 2),
            'new_price' => $product['price'],
            'changed_by' => 'Admin',
        ];
    }

    return $records;
}

// TEMPORARY starting categories. The admin can type new ones in the Add Product popup (kept in the browser only).
// Back-end: replace with the categories table, e.g. Category::pluck('name')->all()
function sample_categories(): array
{
    return ['Grains', 'Vegetables', 'Pet Food', 'Pet Accessories', 'Pet Medicine', 'Hygiene'];
}

// TEMPORARY sample sales for the Sales Reports page. Back-end: replace with real transactions (use a real 'date').
function sample_sales(): array
{
    $i = fn ($name, $cat, $price, $qty) => ['name' => $name, 'category' => $cat, 'price' => $price, 'qty' => $qty];
    return [
        ['days_ago' => 0, 'payment' => 'Cash',  'items' => [$i('Rice', 'Grains', 55, 4), $i('Tomatoes', 'Vegetables', 60, 2)]],
        ['days_ago' => 1, 'payment' => 'GCash', 'items' => [$i('Dog Food 1kg', 'Pet Food', 180, 2)]],
        ['days_ago' => 1, 'payment' => 'Cash',  'items' => [$i('Corn', 'Grains', 48, 10)]],
        ['days_ago' => 2, 'payment' => 'Card',  'items' => [$i('Onions', 'Vegetables', 120, 3), $i('Rice', 'Grains', 55, 5)]],
        ['days_ago' => 3, 'payment' => 'Cash',  'items' => [$i('Tomatoes', 'Vegetables', 60, 6)]],
        ['days_ago' => 4, 'payment' => 'GCash', 'items' => [$i('Dog Food 1kg', 'Pet Food', 180, 1), $i('Corn', 'Grains', 48, 4)]],
        ['days_ago' => 5, 'payment' => 'Cash',  'items' => [$i('Rice', 'Grains', 55, 12)]],
        ['days_ago' => 6, 'payment' => 'Cash',  'items' => [$i('Onions', 'Vegetables', 120, 2)]],
    ];
}

// TEMPORARY role switch for the front-end demo (?role=staff shows the staff screens).
// Back-end: replace the body with the logged-in user's role, e.g. return auth()->user()->role === 'staff';
function is_staff(): bool
{
    return request('role') === 'staff';
}

Route::get('/', fn () => redirect()->route('login'));
Route::view('/login', 'login')->name('login');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::get('/products', fn () => view('products', ['products' => sample_products()]))->name('products');
Route::get('/record-sale', fn () => view('record-sale', ['products' => sample_products()]))->name('sales.create');
Route::view('/transactions', 'transactions')->name('transactions');
Route::get('/sales-reports', fn () => view('sales-reports', ['sales' => sample_sales()]))->name('reports');
Route::get('/monthly-records', fn () => view('monthly-records', ['sales' => sample_sales()]))->name('monthly');
Route::get('/price-records', fn () => view('price-records', ['records' => sample_price_records()]))->name('price-records');
Route::view('/staff-accounts', 'staff-accounts')->name('staff');