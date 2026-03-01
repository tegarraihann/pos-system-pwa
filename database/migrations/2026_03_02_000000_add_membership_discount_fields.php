<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->decimal('member_discount_percent', 5, 2)
                ->default(0)
                ->after('is_member');
            $table->timestamp('member_since')
                ->nullable()
                ->after('member_discount_percent');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('member_discount_percent', 5, 2)
                ->default(0)
                ->after('discount_total');
            $table->decimal('member_discount_total', 12, 2)
                ->default(0)
                ->after('member_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'member_discount_percent',
                'member_discount_total',
            ]);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn([
                'member_discount_percent',
                'member_since',
            ]);
        });
    }
};
