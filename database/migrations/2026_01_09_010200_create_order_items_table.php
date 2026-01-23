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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('menu_variant_id')
                ->constrained('menu_variants')
                ->restrictOnDelete();
            $table->string('item_name_snapshot');
            $table->decimal('price', 12, 2)->unsigned();
            $table->decimal('qty', 12, 3)->unsigned();
            $table->decimal('discount_amount', 12, 2)->unsigned()->default(0);
            $table->decimal('total', 12, 2)->unsigned()->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
