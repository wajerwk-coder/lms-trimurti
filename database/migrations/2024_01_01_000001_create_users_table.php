<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->enum('role', ['admin', 'guru', 'siswa']);
                $table->string('nis_nip')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('role');
                $table->index('nis_nip');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
