<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Ubahdim extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'usul_susun_dcts';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "PER", "NOTES", "TG_SMP", "USRNM", "ID_BRG", "KD_BRG", "NA_BRG", "KET_UK", "KET_KEM", "KLK", "LPH", "PANJANG",
        "PANJANG_LAMA", "LEBAR", "LEBAR_LAMA", "TINGGI", "TINGGI_LAMA", "PANJANG_SHELF", "PANJANG_SHELF_LAMA", "SUSUN",
        "SUSUN_LAMA", "DTR_MANUAL", "DTR_MANUAL_LAMA", "KAPRAK", "PERLU", "PERLUB", "DTR_1M", "DTR_ORI", "MUKA", "MUKA_LAMA",
        "DTR", "DTR_LAMA", "DTR2", "DTR2_LAMA", "TGL_AW_DTR2", "TGL_AW_DTR2_LAMA", "TGL_AK_DTR2", "TGL_AK_DTR2_LAMA",
        "DTR2_BORONG", "DTR2_BORONG_LAMA", "TANDA", "TANDA_LAMA", "SMIN", "SMAX", "CBG", "POSTED", "TGL_POSTED", "USRNM_POSTED"
    ];
}
