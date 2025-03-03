<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->timestamps();
        });

        // Create items table
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type'); // Item type (e.g., Raw Material, Finished Good)
            $table->string('category')->nullable(); // Category of item
            $table->string('unit'); // Unit of measurement
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // Storage location
            $table->string('supplier')->nullable(); // Supplier information
            $table->timestamps();
        });

        // Create transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('expense_type', ['necessary', 'unnecessary', 'neutral'])->nullable();
            $table->timestamps();
        });

        // Create stock_records table
        Schema::create('stock_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('number_of_bags');
            $table->decimal('average_quantity', 10, 2);
            $table->decimal('total_quantity', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        // Create shop_stock_records table
        Schema::create('shop_stock_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('number_of_bags');
            $table->decimal('average_quantity', 10, 2);
            $table->decimal('total_quantity', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('shop_stock_records');
        Schema::dropIfExists('stock_records');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
    }
};
