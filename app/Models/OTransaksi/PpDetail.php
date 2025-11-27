<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpDetail extends Model
{
    use HasFactory;

    protected $table = 'ppd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BHN", "NA_BHN", "KD_BHN", "NA_BHN","SATUAN","QTY", "HARGA", 
        "TOTAL", "KET", "GOL", "FLAG", "KD_BRG", "NA_BRG", "PER",  "DPP", "PPN", "DISK", "CBG", "KODES",
        "TAHAP1", "TAHAP2", "TAHAP3"
    ];
}
