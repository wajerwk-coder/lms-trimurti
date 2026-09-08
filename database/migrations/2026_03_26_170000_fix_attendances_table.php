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
        Schema::table('attendances', function (Blueprint $table) {
            // Add missing columns that the model expects
            if (!Schema::hasColumn('attendances', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->after('note')->comment('User who recorded the attendance');
                $table->index('recorded_by');
            }
            
            if (!Schema::hasColumn('attendances', 'siswa_id')) {
                $table->foreignId('siswa_id')->nullable()->after('id')->comment('Student ID (alias for student_id)');
                $table->index('siswa_id');
            }
            
            if (!Schema::hasColumn('attendances', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('siswa_id')->comment('Attendance date');
                $table->index('tanggal');
            }
            
            if (!Schema::hasColumn('attendances', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('status')->comment('Attendance notes');
            }
            
            if (!Schema::hasColumn('attendances', 'waktu_masuk')) {
                $table->time('waktu_masuk')->nullable()->after('keterangan')->comment('Check-in time');
            }
            
            if (!Schema::hasColumn('attendances', 'waktu_keluar')) {
                $table->time('waktu_keluar')->nullable()->after('waktu_masuk')->comment('Check-out time');
            }
            
            if (!Schema::hasColumn('attendances', 'type')) {
                $table->enum('type', ['regular', 'praktik'])->default('regular')->after('waktu_keluar')->comment('Attendance type');
                $table->index('type');
            }
            
            if (!Schema::hasColumn('attendances', 'practical_id')) {
                $table->foreignId('practical_id')->nullable()->after('type')->comment('Related practical ID');
                $table->index('practical_id');
            }
            
            if (!Schema::hasColumn('attendances', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('practical_id')->comment('Subject ID');
                $table->index('subject_id');
            }
            
            // FK ke users_central dinonaktifkan — data mungkin tidak konsisten
            // Relasi dihandle di level model
            // if (Schema::hasColumn('attendances', 'recorded_by')) {
            //     $table->foreign('recorded_by')->references('id')->on('users_central')->onDelete('set null');
            // }
            // if (Schema::hasColumn('attendances', 'siswa_id')) {
            //     $table->foreign('siswa_id')->references('id')->on('users_central')->onDelete('cascade');
            // }

            if (Schema::hasColumn('attendances', 'practical_id')) {
                $table->foreign('practical_id')->references('id')->on('practicals')->onDelete('set null');
            }
            
            if (Schema::hasColumn('attendances', 'subject_id')) {
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            }
        });
        
        // Update existing records to populate recorded_by from created_by
        if (Schema::hasColumn('attendances', 'recorded_by') && Schema::hasColumn('attendances', 'created_by')) {
            DB::statement('
                UPDATE attendances 
                SET recorded_by = created_by 
                WHERE recorded_by IS NULL AND created_by IS NOT NULL
            ');
        }
        
        // Update student_id mapping
        if (Schema::hasColumn('attendances', 'siswa_id') && Schema::hasColumn('attendances', 'student_id')) {
            DB::statement('
                UPDATE attendances 
                SET siswa_id = student_id 
                WHERE siswa_id IS NULL AND student_id IS NOT NULL
            ');
        }
        
        // Update date mapping
        if (Schema::hasColumn('attendances', 'tanggal') && Schema::hasColumn('attendances', 'date')) {
            DB::statement('
                UPDATE attendances 
                SET tanggal = date 
                WHERE tanggal IS NULL AND date IS NOT NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop indexes first
            $indexes = ['recorded_by', 'siswa_id', 'tanggal', 'type', 'practical_id', 'subject_id'];
            foreach ($indexes as $index) {
                if (Schema::hasIndex('attendances', 'attendances_' . $index . '_index')) {
                    $table->dropIndex([$index]);
                }
            }
            
            // Drop columns
            $columns = ['recorded_by', 'siswa_id', 'tanggal', 'keterangan', 'waktu_masuk', 'waktu_keluar', 'type', 'practical_id', 'subject_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
