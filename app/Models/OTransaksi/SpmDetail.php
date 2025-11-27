<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmDetail extends Model
{
    use HasFactory;

    protected $table = 'spmd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "NO_SO", "REC", "PER", "FLAG", "TYP", "NO_TERIMA", "KD_BRG", "NA_BRG", "SATUAN", "QTY", 
        "SISA", "HARGA", "TOTAL", "MERK", "NO_SERI", "KET", "ID", "ID_SOD", "GOL", "KD_BHN", "NA_BHN"
    ];
}
