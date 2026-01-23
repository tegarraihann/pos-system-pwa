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
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')
                ->constrained('stock_locations')
                ->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->decimal('on_hand', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['location_id', 'item_type', 'item_id'], 'stock_levels_unique');
            $table->index(['item_type', 'item_id'], 'stock_levels_morph_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
