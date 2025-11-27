<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MintaDetail extends Model
{
    use HasFactory;

    protected $table = 'mintad';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BRG", "NA_BRG", "SATUAN", "QTY", "QTYC", "QTYR", "KET",
        "CBG", "CBG_TUJU"
    ];
}
