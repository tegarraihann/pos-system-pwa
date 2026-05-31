<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historical_order_imports', function (Blueprint $table): void {
            $table->foreignId('migrated_order_id')->nullable()->after('review_notes')->constrained('orders')->nullOnDelete();
            $table->timestamp('migrated_at')->nullable()->after('migrated_order_id');
            $table->text('migration_notes')->nullable()->after('migrated_at');
        });
    }

    public function down(): void
    {
        Schema::table('historical_order_imports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('migrated_order_id');
            $table->dropColumn([
                'migrated_at',
                'migration_notes',
            ]);
        });
    }
};
