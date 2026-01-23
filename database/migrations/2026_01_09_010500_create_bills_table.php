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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->string('bill_no', 20);
            $table->decimal('subtotal', 12, 2)->unsigned()->default(0);
            $table->decimal('discount_total', 12, 2)->unsigned()->default(0);
            $table->decimal('tax_total', 12, 2)->unsigned()->default(0);
            $table->decimal('service_total', 12, 2)->unsigned()->default(0);
            $table->decimal('grand_total', 12, 2)->unsigned()->default(0);
            $table->decimal('paid_total', 12, 2)->unsigned()->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->timestamps();

            $table->unique(['order_id', 'bill_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
