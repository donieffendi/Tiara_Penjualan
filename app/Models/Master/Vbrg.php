<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Vbrg extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'vbrg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KD_BRG", "NA_BRG", "JENIS", "SATUAN","LOGO", "SATUAN", "FORMULA", "USRNM",
        "TG_SMP", "GOL", "SUPI", "MERK", "KET", "created_at", "created_by", "KODES", "SUPP",
        "KLK", "BL_PER", "BL_AKR", "JL_AKR", "HB", "HS", "HB_NAIK", "KELOMPOK", "LOKASI",
        "UP_HB", "KET_UK", "KET_KEM", "DIAMETER", "TEBAL", "PANJANG",
        "KG", "SMIN", "SMAX", "CBG", "HJUAL"
		
    ];
}
