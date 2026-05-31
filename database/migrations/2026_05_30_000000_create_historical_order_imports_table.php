<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_order_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('source_file');
            $table->string('source_order_number')->unique();
            $table->string('outlet_name')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method_raw')->nullable();
            $table->string('payment_method_mapped')->nullable();
            $table->string('payment_channel_raw')->nullable();
            $table->text('operator_raw')->nullable();
            $table->text('raw_products');
            $table->json('normalized_products')->nullable();
            $table->decimal('unpaid_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('base_mapped_total', 12, 2)->default(0);
            $table->decimal('price_gap', 12, 2)->default(0);
            $table->string('mapping_status')->default('unmatched');
            $table->text('notes')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_order_imports');
    }
};
