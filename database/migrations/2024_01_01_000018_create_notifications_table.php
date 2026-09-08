<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('message');
                $table->string('tipe_penerima')->default('user');
                $table->unsignedBigInteger('penerima_id')->nullable();
                $table->string('tipe_notifikasi')->default('info');
                $table->string('action_url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->json('data')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('is_read');
                $table->index('tipe_penerima');
            });
        }
    }

    public function down(): void { Schema::dropIfExists('notifications'); }
};
