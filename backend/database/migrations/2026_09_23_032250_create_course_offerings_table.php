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
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id(); $table->foreignId('course_id')->constrained()->restrictOnDelete(); $table->foreignId('academic_term_id')->constrained()->restrictOnDelete(); $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('section'); $table->string('schedule')->nullable(); $table->string('room')->nullable(); $table->unsignedSmallInteger('capacity'); $table->string('status')->default('Active')->index(); $table->unique(['course_id','academic_term_id','section']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};
