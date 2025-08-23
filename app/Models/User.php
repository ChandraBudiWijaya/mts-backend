<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- TAMBAHKAN DUA FUNGSI DI BAWAH INI ---

    /**
     * Mendefinisikan relasi many-to-many ke model Role.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Memeriksa apakah user memiliki izin tertentu.
     *
     * @param string $permissionSlug
     * @return bool
     */

    /**
     * Mendefinisikan relasi one-to-one ke model Employee.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // -----------------------------
    public function hasPermission(string $permissionSlug): bool
    {
        // Loop melalui setiap peran yang dimiliki user
        foreach ($this->roles as $role) {
            // Periksa apakah koleksi izin dari peran tersebut mengandung slug yang kita cari
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }
        // Jika setelah semua peran diperiksa tidak ada yang cocok, kembalikan false
        return false;
    }
}
