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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->dateTime('movement_date');
            $table->foreignId('from_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete();
            $table->foreignId('to_location_id')
                ->nullable()
                ->constrained('stock_locations')
                ->nullOnDelete();
            $table->string('adjustment_type', 20)->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
