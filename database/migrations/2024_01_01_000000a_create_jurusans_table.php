<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel jurusans — master data program keahlian SMK.
 * Harus dibuat sebelum classes karena classes.jurusan_id references jurusans.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jurusans')) {
            return; // Sudah ada, skip
        }

        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusans');
    }
};
