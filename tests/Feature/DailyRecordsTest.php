<?php

use App\Models\Category;
use App\Models\PriceRecord;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSaleProduct(int $stock = 5): Product
{
    $category = Category::create(['name' => 'Pet Food']);

    return Product::create([
        'category_id' => $category->id,
        'name' => 'Dog Food',
        'price' => 12.50,
        'stock' => $stock,
    ]);
}

test('daily records show only dates with saved sales and aggregate transaction totals', function () {
    $product = createSaleProduct();

    $this->get(route('monthly'))
        ->assertOk()
        ->assertSee('No sales records for this month and selection.');

    foreach ([2, 1] as $quantity) {
        $this->post(route('sales.store'), [
            'customer_name' => 'Walk-in Customer',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => $quantity],
            ],
        ])->assertRedirect(route('sales.create'));
    }

    $this->get(route('sales.create'))
        ->assertOk()
        ->assertSee('Transaction saved successfully. Invoice: INV-', false);

    $product->refresh();
    expect($product->stock)->toBe(2);
    expect(Transaction::count())->toBe(2);

    $this->get(route('monthly'))
        ->assertOk()
        ->assertSee(now()->format('Y-m-d'))
        ->assertSee('2', false)
        ->assertSee('37.50');
});

test('an invalid stock quantity does not create a sale', function () {
    $product = createSaleProduct(1);

    $this->from(route('sales.create'))->post(route('sales.store'), [
        'payment_method' => 'Cash',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ])->assertRedirect(route('sales.create'))
        ->assertSessionHasErrors('items.0.quantity');

    expect(Transaction::count())->toBe(0);
    expect($product->fresh()->stock)->toBe(1);
});

test('category filters are hidden without products and shown when products exist', function () {
    $this->get(route('products'))
        ->assertOk()
        ->assertSee('id="cats" style="display:none"', false);

    $this->get(route('sales.create'))
        ->assertOk()
        ->assertSee('id="scats" style="display:none"', false);

    createSaleProduct();

    $this->get(route('products'))
        ->assertOk()
        ->assertSee('id="cats" style="display:flex"></div>', false);

    $this->get(route('sales.create'))
        ->assertOk()
        ->assertSee('id="scats" style="display:flex"></div>', false);
});

test('monthly records month picker spans from 2020 through the current year', function () {
    $year = now()->format('Y');

    $this->get(route('monthly', ['month' => '2020-06']))
        ->assertOk()
        ->assertSee('value="2020-06" min="2020-01" max="'.$year.'-12"', false);

    $this->get(route('monthly', ['month' => '2019-06']))
        ->assertOk()
        ->assertSee('value="'.now()->format('Y-m').'" min="2020-01" max="'.$year.'-12"', false);
});

test('admin can add a product that appears in product and sale pages', function () {
    $category = Category::create(['name' => 'Pet Accessories']);

    $this->post(route('products.store'), [
        'name' => 'Travel Carrier',
        'category' => $category->name,
        'price' => '49.95',
        'stock' => '8',
    ])->assertRedirect(route('products'))
        ->assertSessionHas('status', 'Product added successfully.');

    $product = Product::where('name', 'Travel Carrier')->firstOrFail();
    expect($product->price)->toBe(49.95)
        ->and($product->stock)->toBe(8)
        ->and($product->weight)->toBeNull();

    $this->get(route('products'))
        ->assertOk()
        ->assertSee('Travel Carrier')
        ->assertSee('Pet Accessories');

    $this->get(route('sales.create'))
        ->assertOk()
        ->assertSee('"name":"Travel Carrier"', false)
        ->assertSee('"category":"Pet Accessories"', false);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Products')
        ->assertSee('1');
});

test('admin can add a product and category together with optional weight', function () {
    $this->post(route('products.store'), [
        'name' => 'Rabbit Hay',
        'category' => '__new_category__',
        'new_category' => 'Small Pets',
        'price' => '120',
        'stock' => '4',
        'weight' => '2.5',
    ])->assertRedirect(route('products'));

    $product = Product::with('category')->where('name', 'Rabbit Hay')->firstOrFail();
    expect($product->category->name)->toBe('Small Pets')
        ->and($product->weight)->toBe(2.5);

    $this->get(route('products'))
        ->assertOk()
        ->assertSee('Small Pets')
        ->assertSee('2.5 kg');
});

test('adding a product records its initial category and price in price records', function () {
    $this->post(route('products.store'), [
        'name' => 'Freshwater Fish Food',
        'category' => '__new_category__',
        'new_category' => 'Aquarium',
        'price' => '87.50',
        'stock' => '6',
    ])->assertRedirect(route('products'));

    $this->assertDatabaseHas('price_records', [
        'product_name' => 'Freshwater Fish Food',
        'category' => 'Aquarium',
        'old_price' => 87.50,
        'new_price' => 87.50,
        'changed_by' => 'Admin',
        'changed_at' => now()->toDateString(),
    ]);

    $this->get(route('price-records'))
        ->assertOk()
        ->assertSee('Freshwater Fish Food')
        ->assertSee('Aquarium')
        ->assertSee('Initial price')
        ->assertSee('87.50');
});

test('invalid product data is rejected without creating product or category', function () {
    $this->from(route('products'))->post(route('products.store'), [
        'name' => 'Broken Product',
        'category' => '__new_category__',
        'new_category' => 'Never Saved',
        'price' => '-1',
        'stock' => 'not-a-number',
    ])->assertRedirect(route('products'))
        ->assertSessionHasErrors(['price', 'stock']);

    expect(Product::count())->toBe(0);
    expect(Category::where('name', 'Never Saved')->exists())->toBeFalse();
});

test('admin can edit stock for an individual product', function () {
    $product = createSaleProduct(5);
    PriceRecord::create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'category' => 'Pet Food',
        'old_price' => 12.50,
        'new_price' => 12.50,
        'changed_by' => 'Admin',
        'changed_at' => now()->toDateString(),
    ]);

    $this->get(route('products'))
        ->assertOk()
        ->assertSee('Edit Stock')
        ->assertSee('Quantity')
        ->assertDontSee('Stock action')
        ->assertSee(route('products.stock', $product), false);

    $this->get(route('products'))
        ->assertSee('data-stock="5"', false);

    $this->post(route('products.stock', $product), ['quantity' => 7])
        ->assertRedirect(route('products'))
        ->assertSessionHas('stock_status', 'Stock updated for Dog Food: 7 remaining.');

    expect($product->fresh()->stock)->toBe(7);
    $this->post(route('products.stock', $product), ['quantity' => 3])
        ->assertRedirect(route('products'))
        ->assertSessionHas('stock_status', 'Stock updated for Dog Food: 3 remaining.');

    expect($product->fresh()->stock)->toBe(3);
    $this->post(route('products.stock', $product), ['quantity' => 0])
        ->assertRedirect(route('products'))
        ->assertSessionHas('stock_status', 'Stock updated for Dog Food: 0 remaining.');

    expect($product->fresh()->stock)->toBe(0);
    expect(PriceRecord::count())->toBe(1);
});

test('stock edits reject negative, invalid, or overflowing quantities', function () {
    $product = createSaleProduct(5);

    $this->from(route('products'))->post(route('products.stock', $product), ['quantity' => 'not-a-number'])
        ->assertRedirect(route('products'))
        ->assertSessionHasErrors('quantity');

    $this->from(route('products'))->post(route('products.stock', $product), ['quantity' => -1])
        ->assertRedirect(route('products'))
        ->assertSessionHasErrors('quantity');

    $this->from(route('products'))->post(route('products.stock', $product), ['quantity' => 4294967296])
        ->assertRedirect(route('products'))
        ->assertSessionHasErrors('quantity');

    expect($product->fresh()->stock)->toBe(5);
});

test('backend-backed pages render successfully', function () {
    foreach (['dashboard', 'products', 'sales.create', 'transactions', 'reports', 'monthly', 'price-records'] as $route) {
        $this->get(route($route))->assertOk();
    }
});

test('price records page renders records and chart data', function () {
    $product = createSaleProduct();

    PriceRecord::create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'category' => 'Pet Food',
        'old_price' => 10,
        'new_price' => 12.5,
        'changed_by' => 'Admin',
        'changed_at' => now()->toDateString(),
    ]);

    $this->get(route('price-records'))
        ->assertOk()
        ->assertSee('Dog Food')
        ->assertSee('Pet Food')
        ->assertSee('role="listbox"', false)
        ->assertSee('max-height:288px;overflow-y:auto', false)
        ->assertSee('Admin')
        ->assertSee('Average price per category over time')
        ->assertSee('"product":"Dog Food"', false);

    $this->get(route('price-records', ['month' => 'all']))
        ->assertOk()
        ->assertSee('"start":"'.now()->startOfMonth()->subMonths(5)->toDateString().'"', false)
        ->assertSee('"end":"'.now()->toDateString().'"', false)
        ->assertSee('"allTime":true', false)
        ->assertSee("{month: 'short', timeZone: 'UTC'}", false)
        ->assertSee('May '.now()->year)
        ->assertSee('October '.now()->year);
});
