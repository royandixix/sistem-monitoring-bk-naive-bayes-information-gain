<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Kelas extends Model
{
    use HasFactory;
    protected $fillable=[
        'kode_kelas',
        'nama_kelas',
        'tahun_ajaran',
    ];
    public function siswas():HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}