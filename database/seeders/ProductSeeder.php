<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $faker = Faker::create();



        foreach (range(1, 5000) as $index) {
            $price = rand(2, 5);
            $cost = rand(1, $price - 1);
            $name = $faker->word();
            Product::create([
                'name' => $name,
                'sort_order' => 1,
                'is_active' => $faker->boolean(80),
                'cost' => $cost,
                // 'sale_price' => $price,
                'retailsale_price' => $price,
                'in_stock' => rand(100, 150),
                'track_stock' => true,
                'continue_selling_when_out_of_stock' => false,
                'description' => $faker->paragraph(),
                'category_id' => Category::inRandomOrder()->first()->id,
            ]);
        }
    }
}
