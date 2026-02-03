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
        Schema::table('orders', function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable()->after('paid_total');
            $table->dateTime('ready_at')->nullable()->after('received_at');
            $table->boolean('is_priority')->default(false)->after('ready_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['received_at', 'ready_at', 'is_priority']);
        });
    }
};
