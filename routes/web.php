<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StaffAccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::view('/login', 'login')->name('login');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::post('/products/{product}/stock', [ProductController::class, 'addStock'])->name('products.stock');
Route::get('/record-sale', [SalesController::class, 'create'])->name('sales.create');
Route::post('/record-sale', [SalesController::class, 'store'])->name('sales.store');
Route::get('/transactions', [SalesController::class, 'transactions'])->name('transactions');
Route::get('/sales-reports', [ReportController::class, 'sales'])->name('reports');
Route::get('/monthly-records', [ReportController::class, 'monthly'])->name('monthly');
Route::get('/price-records', [ReportController::class, 'priceRecords'])->name('price-records');
Route::get('/staff-accounts', [StaffAccountController::class, 'index'])->name('staff');
Route::post('/staff-accounts', [StaffAccountController::class, 'store'])->name('staff.store');
Route::patch('/staff-accounts/{user}/status', [StaffAccountController::class, 'updateStatus'])->name('staff.status');
Route::patch('/staff-accounts/{user}/password', [StaffAccountController::class, 'resetPassword'])->name('staff.password');
