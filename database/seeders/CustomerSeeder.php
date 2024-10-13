<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class CustomerSeeder extends Seeder
{

    const USER_ID = "82da6c32-366b-4095-a5e5-0933b7833a0f";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        foreach (range(1, 150) as $index) {
            $customer = new Customer();
            $customer->name = $faker->name();
            $customer->email = $faker->boolean() ? $faker->email() : null;
            $customer->mobile = $faker->boolean() ? $faker->phoneNumber() : null;
            $customer->save();
            $range = rand(5, 20);
            foreach (range(1, $range) as $index) {
                $this->createOrder($faker, $customer);
            }
        }

        foreach (range(1, 100) as $index) {
            $this->createOrder($faker);
        }
    }

    public function createOrder(Generator $faker, Customer $customer = null)
    {
        $subtotal =  $faker->numberBetween(15, 2000);
        $taxRate =  $faker->boolean(20) ? rand(10, 13) : 0;
        $discount = $faker->boolean(10) ? rand(1, $subtotal) : 0;
        $deliveryCharge = $faker->boolean(10) ? rand(10, 15) : 0;

        $vatType =  $faker->boolean() ? "exclude" : "add";

        if ($vatType == "add") {
            $taxRateValue = ($taxRate * $subtotal) / 100;
        } else {
            $taxRateValue = 0;
        }
        
        $total = $subtotal + $taxRateValue + $deliveryCharge - $discount;

        $tenderAmount = $faker->boolean(10) ? $total - ($subtotal - 5) : $total;


        $order = new Order();
        if ($customer) {
            $order->customer_id = $customer->id;
        }
        $order->user_id = $faker->boolean(40) ? "82da6c32-366b-3231-a5e5-8795t8944a0f" : self::USER_ID;
        $order->number = $faker->numberBetween();
        $order->delivery_charge = $deliveryCharge;
        $order->tax_rate = $taxRate;
        $order->vat_type = $faker->boolean() ? "exclude" : "add";
        $order->discount = $discount;
        $order->subtotal = $subtotal;
        $order->total = $total;
        $order->tender_amount = $tenderAmount;
        $order->change = 0;
        $created_at = $faker->dateTimeBetween('-2 years');
        $order->created_at = $created_at;
        $order->save();


        foreach (range(1, 10) as $index) {
            $product = Product::inRandomOrder()->first();
            $orderDetails = new OrderDetail();
            $orderDetails->order_id = $order->id;
            $orderDetails->product_id = $product->id;
            $qty = rand(1, 10);
            $orderDetails->quantity = $qty;
            $orderDetails->price = $product->price;
            $orderDetails->wholesale_price = $product->wholesale_price;
            $orderDetails->retailsale_price = $product->retailsale_price;
            $orderDetails->price_type = $product->price_type;
            $orderDetails->cost = $product->cost;
            $orderDetails->total = $product->price * $qty;
            $orderDetails->total_cost = $product->cost * $qty;
            $orderDetails->created_at = $created_at;
            $orderDetails->save();
        }
    }
}
