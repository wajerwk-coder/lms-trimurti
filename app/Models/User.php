<?php

namespace App\Models;

/**
 * User — alias ke UserCentral.
 *
 * Kelas ini hanya meneruskan semua panggilan ke UserCentral agar kode lama
 * yang masih pakai `App\Models\User` (tests, seeders, middleware phpdoc)
 * tetap berfungsi tanpa perubahan.
 *
 * Jangan tambahkan logic baru di sini — tulis di UserCentral.
 *
 * @see \App\Models\UserCentral
 */
class User extends UserCentral
{
    // Alias tipis — semua method, relasi, scope, dan boot hook diwarisi dari UserCentral.
}
