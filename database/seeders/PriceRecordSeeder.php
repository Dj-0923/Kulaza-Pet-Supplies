<?php

namespace Database\Seeders;

use App\Models\PriceRecord;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PriceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::orderBy('id')->get()->values();

        foreach ($products as $index => $product) {
            if (PriceRecord::where('product_id', $product->id)->exists()) {
                continue;
            }

            PriceRecord::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category->name,
                'old_price' => round($product->price * 0.95, 2),
                'new_price' => $product->price,
                'changed_by' => 'Admin',
                'changed_at' => now()->subDays($index)->toDateString(),
            ]);
        }
    }
}
