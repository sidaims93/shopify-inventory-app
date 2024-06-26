<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id');
            $table->bigInteger('store_id');
            $table->string('cancel_reason')->nullable();
            $table->string('cancelled_at')->nullable();
            $table->string('cart_token')->nullable();
            $table->string('checkout_id')->nullable();
            $table->string('checkout_token')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('created_at')->nullable();
            $table->timestamp('order_created_at')->nullable();
            $table->string('currency')->nullable();
            $table->string('discount_codes')->nullable();
            $table->string('email')->nullable();
            $table->string('financial_status')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('gateway')->nullable();
            $table->string('name')->nullable();
            $table->string('note')->nullable();
            $table->string('order_number')->nullable();
            $table->string('order_status_url')->nullable();
            $table->string('phone')->nullable();
            $table->longText('tax_lines')->nullable();
            $table->longText('subtotal_price_set')->nullable();
            $table->string('subtotal_price')->nullable();
            $table->longText('total_line_items_price')->nullable();
            $table->longText('total_discounts_set')->nullable();
            $table->string('taxes_included')->nullable();
            $table->string('test')->nullable();
            $table->string('total_discounts')->nullable();
            $table->string('total_price')->nullable();
            $table->string('total_price_usd')->nullable();
            $table->string('total_tax')->nullable();
            $table->string('total_tip_received')->nullable();
            $table->string('updated_at')->nullable();
            $table->longText('billing_address')->nullable();
            $table->longText('customer')->nullable();
            $table->longText('fulfillments')->nullable();
            $table->longText('line_items')->nullable();
            $table->longText('shipping_address')->nullable();
            $table->longText('shipping_lines')->nullable();
            $table->longText('payment_details')->nullable();
            $table->string('ship_province')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('closed_at')->nullable();
            $table->string('number')->nullable();
            $table->string('total_weight')->nullable();
            $table->string('location_id')->nullable();
            $table->string('processed_at')->nullable();
            $table->string('processing_method')->nullable();
            $table->string('tags')->nullable();
            $table->string('discount_applications')->nullable();
            $table->string('total_shipping_price_set')->nullable();
            $table->string('total_price_set')->nullable();
            $table->string('total_tax_set')->nullable();
            $table->string('refunds')->nullable();
            $table->string('payment_gateway_names')->nullable();
            $table->unique(['store_id', 'id']);
            $table->timestamp('created_at_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
