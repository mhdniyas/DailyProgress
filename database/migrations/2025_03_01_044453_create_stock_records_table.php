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
    Schema::create('stock_records', function (Blueprint $table) {
        $table->id();
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
    public function down(): void
    {
        Schema::dropIfExists('stock_records');
    }
};
