<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_criteria', function (Blueprint $table) {
            // Tambah kolom kategori jika belum ada
            if (!Schema::hasColumn('assessment_criteria', 'kategori')) {
                $table->enum('kategori', ['persiapan', 'pelaksanaan', 'hasil', 'sikap'])
                      ->nullable()
                      ->after('type');
            }

            // Tambah soft deletes jika belum ada
            if (!Schema::hasColumn('assessment_criteria', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Konversi weight dari decimal (0.20) ke integer (20) untuk data yang sudah ada
        // Jika nilai <= 1.0 maka nilai lama masih format desimal → kalikan 100
        DB::statement("
            UPDATE assessment_criteria
            SET weight = ROUND(weight * 100)
            WHERE weight > 0 AND weight <= 1.0
        ");

        // Ubah tipe kolom weight dari decimal ke integer
        Schema::table('assessment_criteria', function (Blueprint $table) {
            $table->integer('weight')->default(10)->change();
        });

        // Tambah index untuk kategori
        Schema::table('assessment_criteria', function (Blueprint $table) {
            if (!Schema::hasIndex('assessment_criteria', 'assessment_criteria_kategori_index')) {
                $table->index('kategori');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessment_criteria', function (Blueprint $table) {
            // Kembalikan weight ke decimal
            $table->decimal('weight', 5, 2)->default(0.10)->change();

            if (Schema::hasColumn('assessment_criteria', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }
};
