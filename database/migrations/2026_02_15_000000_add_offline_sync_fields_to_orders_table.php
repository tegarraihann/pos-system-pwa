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
            $table->string('payment_method', 20)
                ->nullable()
                ->after('status');
            $table->string('sync_status', 20)
                ->default('synced')
                ->after('payment_method');
            $table->string('client_txn_id', 100)
                ->nullable()
                ->after('sync_status');
            $table->timestamp('synced_at')
                ->nullable()
                ->after('client_txn_id');
            $table->text('sync_error')
                ->nullable()
                ->after('synced_at');

            $table->index('payment_method');
            $table->index('sync_status');
            $table->unique('client_txn_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['client_txn_id']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['sync_status']);
            $table->dropColumn([
                'payment_method',
                'sync_status',
                'client_txn_id',
                'synced_at',
                'sync_error',
            ]);
        });
    }
};

