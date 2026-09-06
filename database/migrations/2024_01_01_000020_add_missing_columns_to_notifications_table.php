<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add missing columns — no ->after() to avoid dependency on optional columns

            if (!Schema::hasColumn('notifications', 'sender_id')) {
                $table->unsignedBigInteger('sender_id')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'receiver_id')) {
                $table->unsignedBigInteger('receiver_id')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'receiver_type')) {
                $table->string('receiver_type')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'tipe')) {
                $table->string('tipe')->default('info');
            }

            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'judul')) {
                $table->string('judul')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'pesan')) {
                $table->text('pesan')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'url_aksi')) {
                $table->string('url_aksi')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'prioritas')) {
                $table->string('prioritas')->default('sedang');
            }

            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->string('priority')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'status')) {
                $table->string('status')->default('belum_dibaca');
            }

            if (!Schema::hasColumn('notifications', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }

            // Add indexes only for columns that exist
            try { $table->index(['receiver_id', 'receiver_type']); } catch (\Throwable $e) {}
            try { $table->index('receiver_type'); } catch (\Throwable $e) {}
            try { $table->index('tipe'); } catch (\Throwable $e) {}
            try { $table->index('type'); } catch (\Throwable $e) {}
            try { $table->index('status'); } catch (\Throwable $e) {}
            try { $table->index('prioritas'); } catch (\Throwable $e) {}
            try { $table->index('priority'); } catch (\Throwable $e) {}
            try { $table->index('scheduled_at'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop added columns
            $columns = [
                'sender_id', 'receiver_id', 'receiver_type',
                'tipe', 'type', 'judul', 'pesan', 'url_aksi',
                'prioritas', 'priority', 'status', 'scheduled_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
