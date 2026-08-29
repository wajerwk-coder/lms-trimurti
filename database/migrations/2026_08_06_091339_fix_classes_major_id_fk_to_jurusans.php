<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sinkronkan tabel majors dengan jurusans
        //    Pastikan semua jurusan ada di majors dengan id yang sama
        $jurusans = DB::table('jurusans')->get(['id', 'name', 'code', 'description']);
        foreach ($jurusans as $j) {
            $exists = DB::table('majors')->where('id', $j->id)->exists();
            if (!$exists) {
                // Insert dengan ID yang sama agar FK tetap konsisten
                DB::statement("INSERT INTO `majors` (id, name, code, description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())", [
                    $j->id,
                    $j->name,
                    $j->code,
                    $j->description,
                ]);
            } else {
                // Update agar data sinkron
                DB::table('majors')->where('id', $j->id)->update([
                    'name'        => $j->name,
                    'code'        => $j->code,
                    'description' => $j->description,
                ]);
            }
        }

        // 2. Update classes yang belum punya jurusan_id
        //    Set jurusan_id = major_id untuk data yang sudah ada
        DB::statement("UPDATE classes SET jurusan_id = major_id WHERE jurusan_id IS NULL");
    }

    public function down(): void
    {
        // Tidak perlu rollback — data sinkronisasi tidak merusak
    }
};
