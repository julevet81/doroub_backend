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
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('membership_id')->unique();
            $table->enum('gender', ['male', 'female']);
            $table->string('email')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable();
            $table->date('joining_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('subscriptions', 15,2)->nullable();
            $table->string('skills')->nullable();
            $table->enum('study_level', ['primary', 'intermediate','secondary','high_school', 'bachelor', 'master', 'phd', 'other'])->nullable();
            $table->enum('grade', ['founder', 'active', 'honorary'])->nullable();
            $table->enum('section', ['planning', 'entry', 'executive', 'finance', 'management', 'resources','relations', 'media', 'social'])->nullable();
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
        Schema::dropIfExists('volunteers');
    }
};
