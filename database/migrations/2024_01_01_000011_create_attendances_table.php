<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->date('date')->nullable();
                $table->string('status')->default('hadir');
                $table->string('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index('class_subject_id');
                $table->index('student_id');
                $table->index('date');
                $table->index('status');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('attendances'); }
};
