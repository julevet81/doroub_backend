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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['in', 'out']);
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('donor_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('orientation', ['project', 'inventory', 'other'])->default('inventory');
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
