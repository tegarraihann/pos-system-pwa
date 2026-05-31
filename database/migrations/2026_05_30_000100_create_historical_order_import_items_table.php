<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_order_import_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('historical_order_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('raw_item_name');
            $table->string('normalized_item_name')->nullable();
            $table->decimal('listed_qty', 10, 3)->default(1);
            $table->decimal('inferred_qty', 10, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_total_inferred', 12, 2)->nullable();
            $table->string('mapping_status')->default('unmatched');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_order_import_items');
    }
};
