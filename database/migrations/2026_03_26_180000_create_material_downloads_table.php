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
        // Create material_downloads table
        if (!Schema::hasTable('material_downloads')) {
            Schema::create('material_downloads', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('material_id')->nullable()->comment('Material that was downloaded');
            $table->foreignId('siswa_id')->nullable()->comment('Student who downloaded');
            $table->foreignId('user_id')->nullable()->comment('User who downloaded (alternative to siswa_id)');
            
            // Download details
            $table->string('ip_address', 45)->nullable()->comment('IP address of downloader');
            $table->string('user_agent')->nullable()->comment('Browser/user agent info');
            $table->timestamp('downloaded_at')->nullable()->comment('When the download occurred');
            
            // Metadata
            $table->boolean('completed')->default(true)->comment('Whether download completed successfully');
            $table->integer('file_size')->nullable()->comment('Size of downloaded file in bytes');
            $table->string('file_name')->nullable()->comment('Original file name');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('material_id');
            $table->index('siswa_id');
            $table->index('user_id');
            $table->index('downloaded_at');
            $table->index('completed');
            
            // Foreign keys — FK ke users_central dinonaktifkan, relasi dihandle di model
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            // $table->foreign('siswa_id')->references('id')->on('users_central')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users_central')->onDelete('cascade');
            });
        }
        
        // Add views_count column to materials table if missing
        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'views_count')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->integer('views_count')->default(0)->comment('Total number of views');
                $table->index('views_count');
            });
        }
        
        // Add downloads_count column to materials table
        if (Schema::hasTable('materials') && !Schema::hasColumn('materials', 'downloads_count')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->integer('downloads_count')->default(0)->comment('Total number of downloads');
                $table->index('downloads_count');
            });
        }
        
        // Populate downloads_count from existing material_downloads data (if any)
        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'downloads_count')) {
            DB::statement('
                UPDATE materials m 
                SET downloads_count = (
                    SELECT COUNT(*) 
                    FROM material_downloads md 
                    WHERE md.material_id = m.id AND md.completed = 1
                )
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes first
        if (Schema::hasTable('material_downloads')) {
            Schema::table('material_downloads', function (Blueprint $table) {
                $table->dropIndex(['material_id']);
                $table->dropIndex(['siswa_id']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['downloaded_at']);
                $table->dropIndex(['completed']);
            });
        }
        
        // Drop table
        Schema::dropIfExists('material_downloads');
        
        // Drop column from materials table
        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'downloads_count')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropIndex(['downloads_count']);
                $table->dropColumn('downloads_count');
            });
        }
    }
};
