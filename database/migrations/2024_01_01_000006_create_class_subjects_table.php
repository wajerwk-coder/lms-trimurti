<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->string('day')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('room')->nullable();
                $table->timestamps();
                $table->index('class_id');
                $table->index('subject_id');
                $table->index('teacher_id');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('class_subjects'); }
};
