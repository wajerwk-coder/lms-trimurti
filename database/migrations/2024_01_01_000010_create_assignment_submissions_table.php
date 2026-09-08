<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assignment_id');
                $table->unsignedBigInteger('student_id')->nullable();
                $table->string('file_url')->nullable();
                $table->text('submission_text')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->integer('score')->nullable();
                $table->text('feedback')->nullable();
                $table->string('status')->default('submitted');
                $table->timestamps();
                $table->index('assignment_id');
                $table->index('student_id');
                $table->index('status');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('assignment_submissions'); }
};
