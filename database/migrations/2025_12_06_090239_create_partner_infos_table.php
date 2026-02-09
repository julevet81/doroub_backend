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
        Schema::create('partner_infos', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('job')->nullable();
            $table->enum('study_level', ['primary_first', 'primary_second', 'primary_third', 'primary_forth', 'primary_fifth', 'intermediate_first', 'intermediate_second', 'intermediate_third', 'intermediate_forth', 'secondary_first', 'secondary_second', 'secondary_third', 'bachelor_1', 'bachelor_2', 'bachelor_3', 'master_1', 'master_2', 'phd'])->nullable();
            $table->string('health_status')->nullable();
            $table->boolean('insured')->default(false);
            $table->string('income_source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_infos');
    }
};
