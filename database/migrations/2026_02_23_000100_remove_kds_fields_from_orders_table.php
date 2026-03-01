<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->whereIn('status', ['queued', 'received', 'preparing', 'ready'])
            ->update(['status' => 'served']);

        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'received_at')) {
                $table->dropColumn('received_at');
            }

            if (Schema::hasColumn('orders', 'ready_at')) {
                $table->dropColumn('ready_at');
            }

            if (Schema::hasColumn('orders', 'is_priority')) {
                $table->dropColumn('is_priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'received_at')) {
                $table->dateTime('received_at')->nullable()->after('paid_total');
            }

            if (! Schema::hasColumn('orders', 'ready_at')) {
                $table->dateTime('ready_at')->nullable()->after('received_at');
            }

            if (! Schema::hasColumn('orders', 'is_priority')) {
                $table->boolean('is_priority')->default(false)->after('paid_total');
            }
        });
    }
};
