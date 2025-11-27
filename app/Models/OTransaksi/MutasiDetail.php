<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiDetail extends Model
{
    use HasFactory;

    protected $table = 'mutasid';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BRG", "NA_BRG", "SATUAN", "QTY",  "KD_BRG2", "NA_BRG2", "SATUAN2", "QTY2" , "KET"
    ];
}
