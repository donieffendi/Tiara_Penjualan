<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Import extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'dataubah';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "NA_FILE", "TGL", "PROSES", "TABEL", "KD_BRG", "KODES", "CHANGES", "KUERI", "KUERI2",
        "KUERI3", "KUERI4", "KUERI5", "LPH", "DTR", "CABANG", "PROS", "TG_SMP", "TG_PROS", "USRNM_PROS",
        "CBG_PROS"	
    ];
}
