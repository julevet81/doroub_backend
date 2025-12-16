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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->enum('social_status', ['maried', 'single', 'divorced', 'widowed'])->default('single');
            $table->enum('gender', ['male', 'female']);
            $table->integer('nbr_in_family')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('partner_infos')->onDelete('cascade');
            $table->integer('nbr_studing')->default(0);
            $table->string('job')->nullable();
            $table->boolean('insured')->default(false);
            $table->enum('study_level', ['none', 'primary', 'secondary', 'higher'])->nullable();
            $table->string('health_status')->nullable();
            $table->string('income_source')->nullable();
            $table->string('barcode')->unique();
            $table->string('national_id_file')->nullable();
            $table->foreignId('municipality_id')->constrained('municipalities')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('city')->nullable();
            $table->string('neighborhood')->nullable();
            $table->enum('house_status', ['owned', 'rented', 'family'])->default('owned');
            $table->string('national_id')->nullable()->unique();
            $table->string('national_id_at')->nullable();
            $table->string('national_id_from')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
