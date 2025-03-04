<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add nullable user_id columns first
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
        });

        Schema::table('stock_records', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
        });

        Schema::table('shop_stock_records', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
        });

        // // Update existing records with user_id = 1
        // DB::table('items')->whereNull('user_id')->update(['user_id' => 1]);
        // DB::table('stock_records')->whereNull('user_id')->update(['user_id' => 1]);
        // DB::table('shop_stock_records')->whereNull('user_id')->update(['user_id' => 1]);
        // DB::table('categories')->whereNull('user_id')->update(['user_id' => 1]);
        // DB::table('transactions')->whereNull('user_id')->update(['user_id' => 1]);

        // Now make the columns non-nullable
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('stock_records', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('shop_stock_records', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        // Your existing down method remains the same
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('stock_records', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('shop_stock_records', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
