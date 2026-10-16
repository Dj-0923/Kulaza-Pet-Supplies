<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PriceRecord;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderBy('name')->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'weight' => $product->weight,
            ])->all();

        return view('products', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'new_category' => ['required_if:category,__new_category__', 'nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['category'] === '__new_category__') {
                $category = Category::firstOrCreate([
                    'name' => trim($validated['new_category']),
                ]);
            } else {
                $category = Category::where('name', $validated['category'])->first();

                if (! $category) {
                    throw ValidationException::withMessages([
                        'category' => 'Choose an existing category or add a new one.',
                    ]);
                }
            }

            if (Product::where('category_id', $category->id)->where('name', $validated['name'])->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'This product already exists in the selected category.',
                ]);
            }

            $product = Product::create([
                'category_id' => $category->id,
                'name' => trim($validated['name']),
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'weight' => $validated['weight'] ?? null,
            ]);

            PriceRecord::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $category->name,
                'old_price' => $product->price,
                'new_price' => $product->price,
                'changed_by' => 'Admin',
                'changed_at' => now()->toDateString(),
            ]);
        });

        return redirect()->route('products')->with('status', 'Product added successfully.');
    }

    public function addStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'between:0,4294967295'],
        ]);

        DB::transaction(function () use ($product, $validated) {
            $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $quantity = (int) $validated['quantity'];

            $lockedProduct->update(['stock' => $quantity]);
        });

        $quantity = (int) $validated['quantity'];
        return redirect()->route('products')->with('stock_status', "Stock updated for {$product->name}: {$quantity} remaining.");
    }
}
