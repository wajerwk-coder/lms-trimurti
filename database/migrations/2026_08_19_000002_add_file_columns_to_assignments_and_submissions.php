<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── assignments ───────────────────────────────────────────────────
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'file')) {
                $table->string('file')->nullable()->after('file_url');
            }
            if (!Schema::hasColumn('assignments', 'file_path')) {
                $table->string('file_path')->nullable()->after('file');
            }
            if (!Schema::hasColumn('assignments', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('assignments', 'file_type')) {
                $table->string('file_type', 20)->nullable()->after('file_size');
            }
        });

        // ── assignment_submissions ────────────────────────────────────────
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_submissions', 'graded_by')) {
                $table->unsignedBigInteger('graded_by')->nullable()->after('feedback');
            }
            if (!Schema::hasColumn('assignment_submissions', 'graded_at')) {
                $table->timestamp('graded_at')->nullable()->after('graded_by');
            }
            // file_path, file_size sudah ada di submissions atau belum
            if (!Schema::hasColumn('assignment_submissions', 'file_path')) {
                $table->string('file_path')->nullable()->after('file_url');
            }
            if (!Schema::hasColumn('assignment_submissions', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $cols = ['file', 'file_path', 'file_size', 'file_type'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('assignments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $cols = ['graded_by', 'graded_at', 'file_path', 'file_size'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('assignment_submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
