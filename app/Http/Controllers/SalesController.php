<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function create()
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

        return view('record-sale', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:Cash,GCash,Card'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Keep inventory deductions and transaction rows atomic if any item is invalid.
        $transaction = DB::transaction(function () use ($validated) {
            $items = [];
            $total = 0;

            foreach ($validated['items'] as $index => $item) {
                $product = Product::with('category')->lockForUpdate()->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'The selected product is no longer available.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Only {$product->stock} in stock for {$product->name}.",
                    ]);
                }

                $product->decrement('stock', $item['quantity']);
                // Store sale-time item details so future catalog edits do not alter old receipts.
                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'qty' => $item['quantity'],
                ];
                $total += $product->price * $item['quantity'];
            }

            $transaction = Transaction::create([
                'invoice' => 'INV-'.Str::upper((string) Str::ulid()),
                'customer_name' => $validated['customer_name'] ?: 'Walk-in Customer',
                'payment_method' => $validated['payment_method'],
                'total' => $total,
                'sold_at' => now()->toDateString(),
            ]);
            $transaction->items()->createMany($items);

            return $transaction;
        });

        return redirect()->route('sales.create')
            ->with('transaction_status', "Transaction saved successfully. Invoice: {$transaction->invoice}");
    }

    public function transactions()
    {
        $transactions = Transaction::with('items')->orderByDesc('sold_at')->orderByDesc('id')->get()
            ->map(fn (Transaction $transaction) => [
                'invoice' => $transaction->invoice,
                'customer' => $transaction->customer_name,
                'items' => $transaction->items->map(fn (TransactionItem $item) => $item->name.' x'.$item->qty)->implode(', '),
                'payment' => $transaction->payment_method,
                'total' => $transaction->total,
                'date' => $transaction->sold_at->format('Y-m-d'),
            ])->all();

        return view('transactions', compact('transactions'));
    }
}
