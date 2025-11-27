<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HdhDetail extends Model
{
    use HasFactory;

    protected $table = 'hdhd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BRG", "NA_BRG", "SATUAN", 
        "QTY", "QTYC", "QTYR", "KET", "PER", "FLAG", "HARGA", "TOTAL", "DPP", "PPN",
        "CBG"
    ];
}
