<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel gurus — profil guru, di-link ke users_central via user_id.
 * Dibuat setelah users_central karena FK user_id → users_central.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gurus')) {
            return; // Sudah ada, skip
        }

        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->nullable();
            $table->string('nip', 50)->unique()->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('address')->nullable();
            $table->text('mata_pelajaran')->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('email_pribadi')->nullable();
            $table->string('jurusan_pendidikan')->nullable();
            $table->unsignedSmallInteger('tahun_mulai_kerja')->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // FK ke users_central
            $table->foreign('user_id')
                  ->references('id')->on('users_central')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
