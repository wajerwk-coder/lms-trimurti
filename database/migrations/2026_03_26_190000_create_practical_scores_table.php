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
        // Create practical_scores table for tracking student submissions/scores
        if (!Schema::hasTable('practical_scores')) {
            Schema::create('practical_scores', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('practical_id')->nullable()->comment('Practical work being scored');
            $table->foreignId('siswa_id')->nullable()->comment('Student who submitted');
            $table->foreignId('guru_id')->nullable()->comment('Teacher who scored');
            $table->foreignId('subject_id')->nullable()->comment('Subject of the practical');
            
            // Score details
            $table->decimal('score', 8, 2)->nullable()->comment('Score obtained (0.00 - 100.00)');
            $table->decimal('max_score', 8, 2)->default(100.00)->comment('Maximum possible score');
            $table->string('grade', 10)->nullable()->comment('Grade (A, B, C, D, E)');
            $table->text('feedback')->nullable()->comment('Teacher feedback');
            $table->text('notes')->nullable()->comment('Additional notes');
            
            // Submission details
            $table->timestamp('submitted_at')->nullable()->comment('When the student submitted');
            $table->timestamp('scored_at')->nullable()->comment('When the teacher scored');
            $table->string('status')->default('submitted')->comment('Status: submitted, scored, returned');
            
            // File attachments
            $table->string('file_path')->nullable()->comment('Path to submitted file');
            $table->string('file_name')->nullable()->comment('Original file name');
            $table->integer('file_size')->nullable()->comment('File size in bytes');
            $table->string('mime_type')->nullable()->comment('File MIME type');
            
            // Evidence and documentation
            $table->json('evidence_files')->nullable()->comment('List of evidence files');
            $table->string('video_url')->nullable()->comment('Video evidence URL');
            $table->json('checklist_items')->nullable()->comment('SOP checklist completion');
            
            // Metadata
            $table->boolean('is_late')->default(false)->comment('Whether submission was late');
            $table->timestamp('due_date')->nullable()->comment('Original due date');
            $table->integer('attempt')->default(1)->comment('Attempt number');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('practical_id');
            $table->index('siswa_id');
            $table->index('guru_id');
            $table->index('subject_id');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('scored_at');
            $table->index(['practical_id', 'siswa_id']); // Composite index
            
            // Foreign keys — FK ke users_central dinonaktifkan, relasi dihandle di model
            $table->foreign('practical_id')->references('id')->on('practicals')->onDelete('cascade');
            // $table->foreign('siswa_id')->references('id')->on('users_central')->onDelete('cascade');
            // $table->foreign('guru_id')->references('id')->on('users_central')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
        }); // end Schema::create

        } // end if (!hasTable)

        // Add scores_count column to practicals table if missing
        if (Schema::hasTable('practicals') && !Schema::hasColumn('practicals', 'scores_count')) {
            Schema::table('practicals', function (Blueprint $table) {
                $table->integer('scores_count')->default(0)->after('submissions_count')->comment('Number of scored submissions');
                $table->index('scores_count');
            });
        }
        
        // Update scores_count from existing practical_scores data
        if (Schema::hasTable('practicals') && Schema::hasColumn('practicals', 'scores_count')) {
            DB::statement('
                UPDATE practicals p 
                SET scores_count = (
                    SELECT COUNT(*) 
                    FROM practical_scores ps 
                    WHERE ps.practical_id = p.id AND ps.status = "scored"
                )
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes first
        if (Schema::hasTable('practical_scores')) {
            Schema::table('practical_scores', function (Blueprint $table) {
                $table->dropIndex(['practical_id']);
                $table->dropIndex(['siswa_id']);
                $table->dropIndex(['guru_id']);
                $table->dropIndex(['subject_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['submitted_at']);
                $table->dropIndex(['scored_at']);
                $table->dropIndex(['practical_id', 'siswa_id']);
            });
        }
        
        // Drop table
        Schema::dropIfExists('practical_scores');
        
        // Drop column from practicals table
        if (Schema::hasTable('practicals') && Schema::hasColumn('practicals', 'scores_count')) {
            Schema::table('practicals', function (Blueprint $table) {
                $table->dropIndex(['scores_count']);
                $table->dropColumn('scores_count');
            });
        }
    }
};
