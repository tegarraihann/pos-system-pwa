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
            $table->foreignId('cashier_session_id')
                ->nullable()
                ->after('created_by')
                ->constrained('cashier_sessions')
                ->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('cashier_session_id')
                ->nullable()
                ->after('order_id')
                ->constrained('cashier_sessions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cashier_session_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cashier_session_id');
        });
    }
};
