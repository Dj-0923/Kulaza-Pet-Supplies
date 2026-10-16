<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $products = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('price_records', 'price_records.product_id', '=', 'products.id')
            ->whereNull('price_records.id')
            ->select('products.id', 'products.name', 'products.price', 'products.created_at', 'categories.name as category')
            ->get();

        foreach ($products as $product) {
            DB::table('price_records')->insert([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category,
                'old_price' => $product->price,
                'new_price' => $product->price,
                'changed_by' => 'Admin',
                'changed_at' => $product->created_at
                    ? substr($product->created_at, 0, 10)
                    : $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Initial-price rows are indistinguishable from legitimate same-price records, so retain them on rollback.
    }
};
