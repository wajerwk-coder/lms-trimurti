<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('practical_assessments')) {
            Schema::create('practical_assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('criteria_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->date('assessment_date')->nullable();
                $table->text('notes')->nullable();
                $table->string('evidence_url')->nullable();
                $table->timestamps();
                $table->index('student_id');
                $table->index('subject_id');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('practical_assessments'); }
};
