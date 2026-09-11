<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel periode pembelajaran (tahun ajaran + semester)
        if (!Schema::hasTable('academic_periods')) {
            Schema::create('academic_periods', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);                          // e.g. "Semester Ganjil 2025/2026"
                $table->string('academic_year', 20);                  // e.g. "2025/2026"
                $table->enum('semester', ['ganjil', 'genap']);        // Ganjil / Genap
                $table->date('start_date')->nullable();               // Tanggal mulai
                $table->date('end_date')->nullable();                 // Tanggal selesai
                $table->boolean('is_active')->default(false);         // Periode aktif saat ini
                $table->text('description')->nullable();              // Keterangan tambahan
                $table->timestamps();
                $table->softDeletes();

                $table->index('academic_year');
                $table->index('semester');
                $table->index('is_active');
            });
        }

        // Tambah kolom semester ke tabel classes (jika belum ada)
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'semester')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil')
                      ->after('academic_year')
                      ->comment('Semester kelas: ganjil atau genap');
                $table->unsignedBigInteger('academic_period_id')->nullable()
                      ->after('semester')
                      ->comment('FK ke tabel academic_periods');
                $table->index('semester');
                $table->index('academic_period_id');
            });
        }

        // Tambah kolom semester ke tabel siswa (jika belum ada)
        if (Schema::hasTable('siswa') && !Schema::hasColumn('siswa', 'semester')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil')
                      ->after('tahun_ajaran')
                      ->comment('Semester siswa: ganjil atau genap');
            });
        }
    }

    public function down(): void
    {
        // Drop kolom semester dari siswa
        if (Schema::hasTable('siswa') && Schema::hasColumn('siswa', 'semester')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }

        // Drop kolom dari classes
        if (Schema::hasTable('classes')) {
            $cols = [];
            if (Schema::hasColumn('classes', 'semester'))            $cols[] = 'semester';
            if (Schema::hasColumn('classes', 'academic_period_id'))  $cols[] = 'academic_period_id';
            if (!empty($cols)) {
                Schema::table('classes', function (Blueprint $table) use ($cols) {
                    $table->dropIndex(['semester']);
                    $table->dropIndex(['academic_period_id']);
                    $table->dropColumn($cols);
                });
            }
        }

        Schema::dropIfExists('academic_periods');
    }
};
