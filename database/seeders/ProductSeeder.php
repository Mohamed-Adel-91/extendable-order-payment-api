<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [];

        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                'name'        => "Product {$i}",
                'description' => "Dummy description for product {$i}",
                'price'       => rand(50, 500),
                'stock'       => rand(1, 50),
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        Product::query()->insert($products);
    }
}
