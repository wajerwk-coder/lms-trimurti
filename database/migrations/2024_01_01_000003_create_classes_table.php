<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('major_id')->nullable();
                $table->string('academic_year')->nullable();
                $table->string('wallpaper')->nullable();
                $table->timestamps();
                $table->index('major_id');
                $table->index('academic_year');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('classes'); }
};
