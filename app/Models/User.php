<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'nip',
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'phone',
        'kelas_id',
        'alamat',
        'jenis_kelamin',
    ];

    /**
     * Kolom yang disembunyikan ketika model diubah menjadi array atau JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Konversi tipe data otomatis.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Menentukan role yang dapat masuk ke panel Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([
            'super_admin',
            'admin',
            'kepala_sekolah',
            'wali_murid',
        ]);
    }

    /**
     * Relasi kelas pengguna.
     *
     * Relasi ini tetap dipertahankan jika kelas_id masih digunakan
     * untuk akun tertentu.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi akun Wali Murid dengan satu atau beberapa anak.
     *
     * Satu akun wali dapat terhubung dengan banyak siswa.
     * Satu siswa juga dapat terhubung dengan lebih dari satu wali,
     * misalnya akun ayah dan akun ibu.
     */
    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'wali_murid_siswa',
            'user_id',
            'siswa_id'
        )
            ->withPivot([
                'hubungan',
                'is_primary',
            ])
            ->withTimestamps();
    }

    /**
     * Periksa satu role tertentu.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Periksa apakah user memiliki salah satu role.
     *
     * @param array<int, string> $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(
            $this->role,
            $roles,
            true
        );
    }

    /**
     * Role Guru BK.
     */
    public function isGuruBk(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Role OSIS.
     */
    public function isOsis(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Role Kepala Sekolah.
     */
    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    /**
     * Role Wali Murid.
     */
    public function isWaliMurid(): bool
    {
        return $this->role === 'wali_murid';
    }
}