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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('reminder_stock', 12, 3)
                ->nullable()
                ->after('purchase_price');
        });

        Schema::table('menu_variants', function (Blueprint $table) {
            $table->decimal('reminder_stock', 12, 3)
                ->nullable()
                ->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('reminder_stock');
        });

        Schema::table('menu_variants', function (Blueprint $table) {
            $table->dropColumn('reminder_stock');
        });
    }
};

