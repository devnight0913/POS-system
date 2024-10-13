<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('image_path')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(1);
            $table->float('in_stock')->default(0);
            $table->boolean('track_stock')->default(false);
            $table->boolean('continue_selling_when_out_of_stock')->default(false);
            // $table->float('sale_price', 12, 2)->default(0);
            $table->float('wholesale_price', 12, 2)->default(0);
            $table->float('retailsale_price', 12, 2)->default(0);
            $table->float('cost', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            // $table->string('barcode')->nullable();
            $table->string('wholesale_barcode')->nullable();
            $table->string('retail_barcode')->nullable();
            // $table->string('sku')->nullable();
            $table->string('wholesale_sku')->nullable();
            $table->string('retail_sku')->nullable();
            $table->longText('description')->nullable();
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
