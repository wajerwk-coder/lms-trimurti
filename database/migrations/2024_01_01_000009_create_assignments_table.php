<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assignments')) {
            Schema::create('assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('file_url')->nullable();
                $table->dateTime('due_date')->nullable();
                $table->integer('max_score')->default(100);
                $table->timestamps();
                $table->index('class_subject_id');
                $table->index('due_date');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('assignments'); }
};
