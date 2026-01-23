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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->dateTime('ordered_at')->nullable();
            $table->string('order_type', 20)->default('dine_in');
            $table->string('status', 20)->default('draft');
            $table->string('customer_type', 20)->default('walk_in');
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();
            $table->foreignId('stock_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete();
            $table->string('table_number', 50)->nullable();
            $table->unsignedInteger('queue_number')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2)->unsigned()->default(0);
            $table->decimal('discount_total', 12, 2)->unsigned()->default(0);
            $table->decimal('tax_total', 12, 2)->unsigned()->default(0);
            $table->decimal('service_total', 12, 2)->unsigned()->default(0);
            $table->decimal('grand_total', 12, 2)->unsigned()->default(0);
            $table->decimal('paid_total', 12, 2)->unsigned()->default(0);
            $table->string('cancel_reason')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['order_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
