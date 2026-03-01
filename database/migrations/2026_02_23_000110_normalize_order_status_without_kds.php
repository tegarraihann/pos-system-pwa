<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. Old KDS statuses should not be restored automatically.
    }
};
