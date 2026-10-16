<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Grains', 'Vegetables', 'Pet Food', 'Pet Accessories', 'Pet Medicine', 'Hygiene'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
