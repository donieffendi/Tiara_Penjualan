<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class So extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'so';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "PER","KODEC", "NAMAC", "ALAMAT", "KOTA", "TOTAL_QTY",  "TOTAL", "NOTES", "GOL", 
        "FLAG", "USRNM", "TG_SMP", "CBG", "KODEP", "NAMAP", "RING", "KOM", "NETT", "TPPN", "TDISK", "TDPP",
        "HARI", "JTEMPO", "TOTAL_TKOM", "NAMAC_2", "ALAMAT_2", "KOTA_2", "TYPE"
    ];
}
