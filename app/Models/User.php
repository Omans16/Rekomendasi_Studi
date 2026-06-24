<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nisn',
        'name',
        'password',
        'role',
        'kelas',
        'password_changed_at',
        'password_security_acknowledged_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'kelas' => 'integer',
            'password_changed_at' => 'datetime',
            'password_security_acknowledged_at' => 'datetime',
        ];
    }

    public function hasilPrediksis()
    {
        return $this->hasMany(HasilPrediksi::class);
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isGuruBk(): bool
    {
        return $this->role === 'guru_bk';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}