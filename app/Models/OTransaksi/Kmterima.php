<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kmterima extends Model
{
    use HasFactory;

    protected $table = 'kmterima';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "JTEMPO", "FLAG", "NO_SO", "KD_BRG", "NA_BRG", "QTY", "KD_BHN", "NA_BHN",
        "QTY_BHN", "SATUAN", "NO_SERI", "NO_FO", "KODEC", "NAMAC", "ALAMAT", "KOTA", 
        "NOTES", "TOTAL_QTYA", "TOTAL_QTY", "SISA", "USRNM", "PER", "TG_SMP", "ID_SOD", 
        "HASIL", "NO_PAKAI", "NO_ORDER", "FIN", "CBG", "CBG_DARI", "NO_KIRIM"
    ];
}

