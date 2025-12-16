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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->integer('usage_count')->default(0);
            $table->boolean('status')->default(false);
            $table->boolean('is_destructed')->default(false);
            $table->text('destruction_report')->nullable();
            $table->text('destruction_reason')->nullable();
            $table->date('destruction_date')->nullable();
            $table->string('barcode')->unique();
            $table->boolean('is_new')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
