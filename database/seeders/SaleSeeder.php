<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sales = [
            ['days_ago' => 0, 'customer' => 'Walk-in Customer', 'payment' => 'Cash', 'items' => [['Rice', 4], ['Tomatoes', 2]]],
            ['days_ago' => 1, 'customer' => 'Maria Santos', 'payment' => 'GCash', 'items' => [['Dog Food 1kg', 2]]],
            ['days_ago' => 1, 'customer' => 'Walk-in Customer', 'payment' => 'Cash', 'items' => [['Corn', 10]]],
            ['days_ago' => 2, 'customer' => 'Walk-in Customer', 'payment' => 'Card', 'items' => [['Onions', 3], ['Rice', 5]]],
            ['days_ago' => 3, 'customer' => 'Juan Dela Cruz', 'payment' => 'Cash', 'items' => [['Tomatoes', 6]]],
            ['days_ago' => 4, 'customer' => 'Walk-in Customer', 'payment' => 'GCash', 'items' => [['Dog Food 1kg', 1], ['Corn', 4]]],
            ['days_ago' => 5, 'customer' => 'Walk-in Customer', 'payment' => 'Cash', 'items' => [['Rice', 12]]],
            ['days_ago' => 6, 'customer' => 'Ana Reyes', 'payment' => 'Cash', 'items' => [['Onions', 2]]],
        ];

        foreach ($sales as $index => $sale) {
            $items = [];
            $total = 0;

            foreach ($sale['items'] as [$name, $qty]) {
                $product = Product::where('name', $name)->first();

                if (! $product) {
                    continue;
                }

                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'qty' => $qty,
                ];
                $total += $product->price * $qty;
            }

            if ($items === []) {
                continue;
            }

            $transaction = Transaction::updateOrCreate(
                ['invoice' => sprintf('INV-%s%05d', now()->format('y'), $index + 1)],
                [
                    'customer_name' => $sale['customer'],
                    'payment_method' => $sale['payment'],
                    'total' => $total,
                    'sold_at' => now()->subDays($sale['days_ago'])->toDateString(),
                ],
            );

            $transaction->items()->delete();
            $transaction->items()->createMany($items);
        }
    }
}
