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
        Schema::create('product_collections', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id')->nullable();
            $table->bigInteger('store_id');
            $table->string('collection_type')->nullable();
            $table->mediumText('handle')->nullable();
            $table->mediumText('title')->nullable();
            $table->mediumText('sort_order')->nullable();
            $table->mediumText('admin_graphql_api_id')->nullable();  
            $table->mediumText('image')->nullable();  
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
        Schema::dropIfExists('product_collections');
    }
};
