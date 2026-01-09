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
        Schema::create('menu_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->cascadeOnDelete();
            $table->string('kd_varian')->unique();
            $table->string('size_varian')->nullable();
            $table->string('temperature')->nullable();
            $table->string('sugar_level')->nullable();
            $table->string('ice_level')->nullable();
            $table->decimal('price', 12, 2)->unsigned();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_variants');
    }
};
