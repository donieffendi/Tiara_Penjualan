<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderkDetail extends Model
{
    use HasFactory;

    protected $table = 'orderkd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KD_PRS", "NA_PRS", "KD_BRG", "NA_BRG", "SATUAN",
        "QTY", "QTY_SO", "KET", "REC", "ID", "PER", "FLAG", "NO_FO"
    ];
}
