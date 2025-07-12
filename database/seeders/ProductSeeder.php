<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ตัวอย่าง: สมมติ category_id 1 = coffee, 2 = dessert
        $products = [
            [
                'name' => 'Americano',
                'slug' => Str::slug('Americano'),
                'description' => 'Classic espresso drink with hot water',
                'price' => 45.00,
                'discount_percent' => 0,
                'category_id' => 1,
                'image' => 'products/americano.jpg',
                'status' => 'available',
                'stock' => rand(50, 200),
                'minimum_stock' => 5,
                'featured' => false,
            ],
            [
                'name' => 'Latte',
                'slug' => Str::slug('Latte'),
                'description' => 'Espresso with steamed milk',
                'price' => 55.00,
                'discount_percent' => 0,
                'category_id' => 1,
                'image' => 'products/latte.jpg',
                'status' => 'available',
                'stock' => rand(50, 200),
                'minimum_stock' => 5,
                'featured' => false,
            ],
            [
                'name' => 'Chocolate Cake',
                'slug' => Str::slug('Chocolate Cake'),
                'description' => 'Rich chocolate layer cake',
                'price' => 65.00,
                'discount_percent' => 0,
                'category_id' => 2,
                'image' => 'products/chocolate-cake.jpg',
                'status' => 'available',
                'stock' => rand(20, 100),
                'minimum_stock' => 5,
                'featured' => false,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}