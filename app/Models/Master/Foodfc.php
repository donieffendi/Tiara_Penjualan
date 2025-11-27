<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Foodfc extends Model
{
    use HasFactory;

    // ganti 2
    protected $table = 'brgfc';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    //ganti 3
    protected $fillable = [
        'SUB',
        'KDBAR',
        'KD_BRG',
        'NA_BRG',
        'SATUAN',
        'STAND',
        'TYPE',
        'KELOMPOK',
        'NMBAR',
        'TG_SMP',
        'KET_UK',
        'KET_KEM',
        'HJ',
        'DIS',
        'LOC',
        'KD_RUBAH',
        'SUPP',
        'NAMAS',
        'STOCKA',
        'STOCKR',
        'BELIAKIR',
        'FLAG',
        'NOITEM',
        'HB',
        'MARGIN',
        'BARCODE',
        'KET',
        'HJ_VIP',
        'TG_HJ_VIP',
        'LOK_TG',
        'KEM_P',
        'RETUR',
        'PPN',
        'TKP',
        'RASA',
        'KOSONG',
        'AL_KOSONG',
        'FLAGSTOK',
        'TGL_KOSONG',
        'USRNM',
    ];
}
