<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "KODEP", "NAMAP", "ALAMAT", "KOTA", "TELPON", "KONTAK", "HP", "GOL", "KOM", "STA", "UMAKAN", "PINJAM"
    ];

}
