<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'status')) {
                $table->enum('status', ['active', 'inactive'])
                      ->default('active')
                      ->after('academic_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
