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
        // Jika tabel sudah ada (import manual), skip create tapi tetap tambah kolom yang kurang
        if (Schema::hasTable('practicals')) {
            // Pastikan kolom yang diperlukan ada
            Schema::table('practicals', function (Blueprint $table) {
                if (!Schema::hasColumn('practicals', 'title'))       $table->string('title')->nullable()->after('id');
                if (!Schema::hasColumn('practicals', 'description')) $table->text('description')->nullable();
                if (!Schema::hasColumn('practicals', 'instructions'))$table->text('instructions')->nullable();
                if (!Schema::hasColumn('practicals', 'due_date'))    $table->timestamp('due_date')->nullable();
                if (!Schema::hasColumn('practicals', 'is_published'))$table->boolean('is_published')->default(false);
                if (!Schema::hasColumn('practicals', 'published_at'))$table->timestamp('published_at')->nullable();
                if (!Schema::hasColumn('practicals', 'views_count')) $table->integer('views_count')->default(0);
                if (!Schema::hasColumn('practicals', 'submissions_count')) $table->integer('submissions_count')->default(0);
            });
            return;
        }

        // Buat tabel dari awal
        Schema::create('practicals', function (Blueprint $table) {
            $table->id();
            
            // Teacher assignment
            $table->foreignId('guru_id')->nullable()->comment('Teacher who created the practical');
            $table->foreignId('subject_id')->nullable()->comment('Subject for this practical');
            $table->foreignId('kelas_id')->nullable()->comment('Class for this practical');
            $table->foreignId('class_subject_id')->nullable()->comment('Link to class_subjects table');
            
            // Basic info
            $table->string('judul')->comment('Title of the practical work');
            $table->text('deskripsi')->nullable()->comment('Description of the practical');
            $table->date('tanggal')->comment('Date of the practical');
            $table->time('waktu_mulai')->nullable()->comment('Start time');
            $table->time('waktu_selesai')->nullable()->comment('End time');
            $table->string('lokasi')->nullable()->comment('Location of the practical');
            
            // Practical details
            $table->integer('durasi')->nullable()->comment('Duration in minutes');
            $table->enum('skill_level', ['basic', 'intermediate', 'advanced'])->default('basic');
            $table->json('tools')->nullable()->comment('List of required tools');
            $table->json('bahan')->nullable()->comment('List of required materials');
            $table->text('instruksi')->nullable()->comment('Instructions for students');
            $table->text('keselamatan')->nullable()->comment('Safety instructions');
            
            // Scoring
            $table->integer('max_score')->default(100)->comment('Maximum possible score');
            $table->json('assessment_criteria')->nullable()->comment('Criteria for assessment');
            
            // Status and visibility
            $table->boolean('is_published')->default(false)->comment('Whether the practical is published to students');
            $table->boolean('is_active')->default(true)->comment('Whether the practical is active');
            $table->timestamp('published_at')->nullable()->comment('When the practical was published');
            
            // Metadata
            $table->integer('views_count')->default(0)->comment('Number of times viewed by students');
            $table->integer('submissions_count')->default(0)->comment('Number of student submissions');
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('guru_id');
            $table->index('subject_id');
            $table->index('kelas_id');
            $table->index('class_subject_id');
            $table->index('tanggal');
            $table->index('is_published');
            $table->index('is_active');
            
            // Foreign keys — FK ke users_central dinonaktifkan karena data mungkin tidak konsisten
            // Relasi dihandle di level model Laravel
            // $table->foreign('guru_id')->references('id')->on('users_central')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('kelas_id')->references('id')->on('classes')->onDelete('set null');
            $table->foreign('class_subject_id')->references('id')->on('class_subjects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicals');
    }
};
