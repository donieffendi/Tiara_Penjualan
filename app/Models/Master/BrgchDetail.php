<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrgchDetail extends Model
{
    use HasFactory;

    protected $table = 'brgchd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "ID", "NO_BUKTI", "SUB", "KD_BRG", "NA_BRG", "KET_UK", "KET_KEM", "BARCODE", "SUPP", "HB", "TGLPRO",
        "D1", "D2", "D3", "LPH_GZ", "LPH_TM", "LPH_TD", "DTR_GZ", "KD", "KET_UK2", "ALASAN", "KLK2", "KET_KEM2",
        "KMP_LAMA", "FUNGSI", "FUNGSI2", "MASA_EXP", "TARIK", "KET_KEM3", "SUPP_LAMA", "USRNM", "OUT", "KLK",
        "MO", "MO2", "KMP", "PPN"
    ];
}
