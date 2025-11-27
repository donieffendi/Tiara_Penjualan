<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ganti 1
class Cust extends Model
{
    use HasFactory;

// ganti 2

    protected $table = 'cust';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

// ganti 3
    protected $fillable = 
    [
        "KODEC", "NAMAC", "ALAMAT", "KOTA", 'GOL', "TELPON1", "FAX", "HP", "CONTACT", "NPWP", "EMAIL", "KET", 
        "KTP", "AKT", "BANK", "BANK_CAB", "BANK_KOTA", "BANK_NAMA", "BANK_REK", "LIM", "HARI", 
        "USRNM", "TG_SMP", "PKP", "KODEP", "NAMAP", "RING", "KONTAK", "CBG", "NO_MEMBER", "KARYAWAN",
        "INDUK", "T_LAHIR", "TGL_LAHIR", "GENDER", "AGAMA", "STATUS",
        "TGL_NIKAH", "NAMA_KARTU", "KET_KARTU", "ID_POIN", "JUM_POIN", "BLOKIR", "STAT"
    ];
}
