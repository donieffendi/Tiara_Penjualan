<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratsDetail extends Model
{
    use HasFactory;

    protected $table = 'suratsd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "NO_SO", "REC", "PER", "FLAG", "TYP", "NO_TERIMA", "KD_BRG", "NA_BRG", "SATUAN", "QTY", 
        "SISA", "HARGA", "TOTAL", "MERK", "NO_SERI", "KET", "ID", "ID_SOD", "GOL", "KD_BHN", "NA_BHN", "PPN",
        "DPP", "QTY_KIRIM", "DISK", "NO_SO", "TYPE_KOM", "KOM", "TKOM"
    ];
}
