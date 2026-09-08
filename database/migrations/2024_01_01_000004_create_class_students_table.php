<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_students')) {
            Schema::create('class_students', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('student_id');
                $table->timestamps();
                $table->unique(['class_id', 'student_id'], 'cs_unique_student');
                $table->index('class_id');
                $table->index('student_id');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('class_students'); }
};
