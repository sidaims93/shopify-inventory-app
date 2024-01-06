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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->bigInteger('id');
            $table->unsignedBigInteger('store_id');
            $table->mediumText('title')->nullable();
            $table->longText('body_html')->nullable();
            $table->mediumText('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('created_at')->nullable();
            $table->string('handle')->nullable();
            $table->string('updated_at')->nullable();
            $table->string('published_at')->nullable();
            $table->string('template_suffix')->nullable();
            $table->string('published_scope')->nullable();
            $table->string('status')->nullable();
            $table->string('admin_graphql_api_id')->nullable();
            $table->longText('image')->nullable();
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
        Schema::dropIfExists('products');
    }
};
