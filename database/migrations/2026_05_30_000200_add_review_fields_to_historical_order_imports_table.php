<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historical_order_imports', function (Blueprint $table): void {
            $table->boolean('ready_for_migration')->default(false)->after('mapping_status');
            $table->foreignId('reviewed_by')->nullable()->after('ready_for_migration')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('historical_order_imports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn([
                'ready_for_migration',
                'reviewed_at',
                'review_notes',
            ]);
        });
    }
};
