<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operating_expenses', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('expense_date');
            $table->foreignId('chart_of_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();
            $table->string('title', 150);
            $table->decimal('amount', 12, 2)->unsigned();
            $table->string('payment_method', 30)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operating_expenses');
    }
};
