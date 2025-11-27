<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JualDetail extends Model
{
    use HasFactory;

    protected $table = 'juald';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "NO_SO", "REC", "PER", "FLAG", "TYP", "KD_BRG", "NA_BRG", "SATUAN", "QTY", "HARGA", 
        "TOTAL", "NO_SERI", "KET", "ID", "ID_SOD", "KD_BHN", "NA_BHN", "GOL", "PER", "PPN", "DPP",
        "QTY_KIRIM", "DISK", "NO_SO", "TYPE_KOM", "KOM", "TKOM", "TKOM_REAL"
    ];
}
