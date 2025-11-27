<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BelizDetail extends Model
{
    use HasFactory;

    protected $table = 'belizd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BHN", "NA_BHN", "KD_BHN", "NA_BHN", "QTY", "HARGA", "TOTAL", 
        "KET", "TTOTAL_QTY", "TTOTAL", "GOL", "PER", "FLAG", "KD_BRG", "NA_BRG", "X", "SATUAN_BL", "QTY_BL"
    ];
}
