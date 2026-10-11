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
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete(); $table->foreignId('program_id')->constrained()->restrictOnDelete(); $table->string('student_number')->unique(); $table->string('first_name'); $table->string('middle_name')->nullable(); $table->string('last_name'); $table->string('suffix')->nullable(); $table->date('birth_date')->nullable(); $table->string('email')->nullable()->index(); $table->string('contact_number')->nullable(); $table->text('address')->nullable(); $table->unsignedTinyInteger('year_level')->index(); $table->string('status')->default('Regular')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
