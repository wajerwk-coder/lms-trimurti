<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('subjects', 'major_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedBigInteger('major_id')
                      ->nullable()
                      ->after('code')
                      ->comment('FK ke jurusans.id — pengelompokan mata pelajaran per jurusan');
                $table->index('major_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subjects', 'major_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropIndex(['major_id']);
                $table->dropColumn('major_id');
            });
        }
    }
};
