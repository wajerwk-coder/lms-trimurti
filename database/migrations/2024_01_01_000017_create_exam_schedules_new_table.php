<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_schedules_new')) {
            Schema::create('exam_schedules_new', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('exam_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('kelas_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->datetime('start_time')->nullable();
                $table->datetime('end_time')->nullable();
                $table->string('location')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamps();
                $table->softDeletes();
                $table->index('start_time');
                $table->index('is_published');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('exam_schedules_new'); }
};
