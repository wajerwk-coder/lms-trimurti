<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->string('file_url')->nullable();
                $table->string('video_url')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->index('class_subject_id');
                $table->index('published_at');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('materials'); }
};
