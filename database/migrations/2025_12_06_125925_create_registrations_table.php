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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('job')->nullable();
            $table->string('health_status')->nullable();
            $table->boolean('insured')->default(false);
            $table->enum('social_status', ['divorced', 'widowed', 'low_income', 'cancer_patient'])->default('low_income');
            $table->integer('nbr_in_family')->nullable();
            $table->integer('nbr_studing')->default(0);
            $table->enum('house_status', ['owned', 'rented', 'host', 'other'])->default('owned');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignId('municipality_id')->constrained('municipalities')->onDelete('cascade');
            $table->string('city')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('first_name_of_wife')->nullable();
            $table->string('last_name_of_wife')->nullable();
            $table->date('date_of_birth_of_wife')->nullable();
            $table->string('birth_place_of_wife')->nullable();
            $table->string('job_of_wife')->nullable();
            $table->string('health_status_of_wife')->nullable();
            $table->boolean('is_wife_insured')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
