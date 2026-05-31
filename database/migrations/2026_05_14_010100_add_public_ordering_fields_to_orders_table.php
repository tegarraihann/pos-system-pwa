<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('order_source', 20)
                ->default('pos')
                ->after('customer_type');
            $table->foreignId('ordering_qr_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('ordering_qrs')
                ->nullOnDelete();
            $table->string('guest_name', 120)
                ->nullable()
                ->after('ordering_qr_id');
            $table->string('guest_phone', 50)
                ->nullable()
                ->after('guest_name');

            $table->index(['order_source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['order_source', 'status']);
            $table->dropConstrainedForeignId('ordering_qr_id');
            $table->dropColumn([
                'order_source',
                'guest_name',
                'guest_phone',
            ]);
        });
    }
};
