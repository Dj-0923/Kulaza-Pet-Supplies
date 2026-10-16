<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Rice', 'category' => 'Grains', 'price' => 55, 'stock' => 100, 'weight' => null],
            ['name' => 'Tomatoes', 'category' => 'Vegetables', 'price' => 60, 'stock' => 40, 'weight' => null],
            ['name' => 'Onions', 'category' => 'Vegetables', 'price' => 120, 'stock' => 60, 'weight' => null],
            ['name' => 'Corn', 'category' => 'Grains', 'price' => 48, 'stock' => 75, 'weight' => null],
            ['name' => 'Dog Food 1kg', 'category' => 'Pet Food', 'price' => 180, 'stock' => 30, 'weight' => 1],
        ];

        foreach ($products as $row) {
            $category = Category::firstOrCreate(['name' => $row['category']]);

            Product::firstOrCreate(
                ['category_id' => $category->id, 'name' => $row['name']],
                ['price' => $row['price'], 'stock' => $row['stock'], 'weight' => $row['weight']],
            );
        }
    }
}
