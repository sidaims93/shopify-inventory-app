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
        Schema::create('store_locations', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id')->nullable();
            $table->bigInteger('store_id');
            $table->mediumText('name')->nullable();
            $table->tinyInteger('legacy')->nullable();
            $table->tinyInteger('active')->nullable();
            $table->mediumText('address1')->nullable();
            $table->mediumText('address2')->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('phone')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->string('province_code')->nullable();
            $table->string('admin_graphql_api_id')->nullable();
            $table->string('localized_country_name')->nullable();
            $table->string('localized_province_name')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at_date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_locations');
    }
};
