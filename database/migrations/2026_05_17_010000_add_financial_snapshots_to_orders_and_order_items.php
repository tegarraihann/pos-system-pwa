<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('cogs_total', 12, 2)->default(0)->after('paid_total');
            $table->decimal('gross_profit_total', 12, 2)->default(0)->after('cogs_total');
            $table->timestamp('cost_accounted_at')->nullable()->after('gross_profit_total');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('net_sales_snapshot', 12, 2)->default(0)->after('total');
            $table->decimal('cost_snapshot', 12, 2)->default(0)->after('net_sales_snapshot');
            $table->decimal('gross_profit_snapshot', 12, 2)->default(0)->after('cost_snapshot');
            $table->decimal('margin_percent_snapshot', 7, 2)->default(0)->after('gross_profit_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'net_sales_snapshot',
                'cost_snapshot',
                'gross_profit_snapshot',
                'margin_percent_snapshot',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'cogs_total',
                'gross_profit_total',
                'cost_accounted_at',
            ]);
        });
    }
};
