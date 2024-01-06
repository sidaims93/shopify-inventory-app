<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopify_stores', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id')->nullable();
            $table->string('myshopify_domain')->unique();
            $table->mediumText('accessToken')->nullable();
            $table->mediumText('name')->nullable();
            $table->mediumText('plan_name')->nullable();
            $table->mediumText('currency')->nullable();
            $table->mediumText('shop_owner')->nullable();
            $table->mediumText('email')->nullable();
            $table->mediumText('customer_email')->nullable();
            $table->mediumText('phone')->nullable();
            $table->bigInteger('primary_location_id')->nullable();
            $table->tinyInteger('eligible_for_card_reader_giveaway')->nullable();
            $table->tinyInteger('eligible_for_payments')->nullable();
            $table->tinyInteger('finances')->nullable();
            $table->tinyInteger('force_ssl')->nullable();
            $table->tinyInteger('has_discounts')->nullable();
            $table->tinyInteger('has_gift_cards')->nullable();
            $table->tinyInteger('has_storefront')->nullable();
            $table->tinyInteger('multi_location_enabled')->nullable();
            $table->tinyInteger('password_enabled')->nullable();
            $table->tinyInteger('pre_launch_enabled')->nullable();
            $table->tinyInteger('requires_extra_payments_agreement')->nullable();
            $table->tinyInteger('setup_required')->nullable();
            $table->tinyInteger('transactional_sms_disabled')->nullable();
            $table->tinyInteger('checkout_api_supported')->nullable();
            $table->mediumText('iana_timezone')->nullable();
            $table->mediumText('address1')->nullable();
            $table->mediumText('address2')->nullable();
            $table->mediumText('city')->nullable();
            $table->mediumText('country')->nullable();
            $table->mediumText('country_code')->nullable();
            $table->mediumText('country_name')->nullable();
            $table->mediumText('province')->nullable();
            $table->mediumText('province_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_stores');
    }
};
