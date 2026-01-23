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
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')
                ->constrained('stock_movements')
                ->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->decimal('qty', 12, 3)->unsigned();
            $table->string('unit', 50)->nullable();
            $table->decimal('cost', 12, 2)->unsigned()->nullable();
            $table->timestamps();

            $table->unique(['stock_movement_id', 'item_type', 'item_id'], 'stock_movement_items_unique');
            $table->index(['item_type', 'item_id'], 'stock_movement_items_morph_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};
