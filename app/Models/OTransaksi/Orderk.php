<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Orderk extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'orderk';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "JTEMPO", "FLAG", "NO_SO", "KD_BRG", "NA_BRG", "QTY", "KD_BHN", "NA_BHN", 
        "QTY_BHN", "SATUAN", "NO_SERI", "NO_FO", "KODEC", "NAMAC", "ALAMAT", "KOTA", "NOTES", 
        "TOTAL_QTYA", "TOTAL_QTY", "SISA", "USRNM", "PER", "TG_SMP", "ID_SOD", "FIN", "CBG"
    ];
}
