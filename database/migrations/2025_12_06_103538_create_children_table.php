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
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->enum('study_level', ['primary_first', 'primary_second', 'primary_third', 'primary_forth', 'primary_fifth', 'intermediate_first', 'intermediate_second', 'intermediate_third', 'intermediate_forth', 'secondary_first', 'secondary_second', 'secondary_third', 'bachelor_1', 'bachelor_2', 'bachelor_3', 'master_1', 'master_2', 'phd'])->nullable();
            $table->string('school')->nullable();
            $table->string('health_status')->nullable();
            $table->string('job')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
