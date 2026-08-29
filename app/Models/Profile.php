<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'avatar',
        'phone',
        'address',
        'bio',
        'date_of_birth',
        'gender',
        'emergency_contact'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserCentral::class, 'user_id');
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/profiles/avatars/' . $this->avatar) : asset('images/default-avatar.png');
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return null;

        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($phone) === 12) {
            return '+62 ' . substr($phone, 2, 3) . ' ' . substr($phone, 5, 4) . ' ' . substr($phone, 9);
        }

        return $this->phone;
    }
}