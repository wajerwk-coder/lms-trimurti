<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswa')) {
            Schema::create('siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique()->nullable();
                $table->string('nis', 20)->unique()->nullable();
                $table->string('nisn', 20)->unique()->nullable();
                $table->string('jenis_kelamin', 10)->nullable();
                $table->string('tempat_lahir', 100)->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_telepon', 20)->nullable();
                $table->unsignedBigInteger('kelas_id')->nullable();
                $table->string('major', 100)->nullable();
                $table->string('tahun_ajaran', 20)->nullable();
                $table->string('nama_ortu', 100)->nullable();
                $table->string('no_telepon_ortu', 20)->nullable();
                $table->string('golongan_darah', 5)->nullable();
                $table->text('riwayat_penyakit')->nullable();
                $table->text('alergi')->nullable();
                $table->text('info_kesehatan')->nullable();
                $table->string('foto', 255)->nullable();
                $table->string('status', 20)->default('aktif');
                $table->timestamps();
                $table->softDeletes();
                $table->index('nis');
                $table->index('kelas_id');
                $table->index('status');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('siswa'); }
};
