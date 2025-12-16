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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('transaction_type', ['income', 'expense']);
            $table->foreignId('donor_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('orientation', ['project', 'family'])->nullable();
            $table->enum('out_orientation', ['project', 'sponsored_family', 'services', 'electricity', 'maintenance', 'internet', 'cleaning', 'generals'])->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'other'])->nullable();
            $table->decimal('previous_balance', 15, 2)->nullable();
            $table->decimal('new_balance', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
