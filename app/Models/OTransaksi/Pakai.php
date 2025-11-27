<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Pakai extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'pakai';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "FLAG", "NO_ORDER", "NO_SO", "KD_BRG", "NA_BRG", "KD_BHN", 
        "NA_BHN", "KD_BHN", "NA_BHN", "QTY_IN", "QTY_OUT", "TOTAL_QTY", "SATUAN", "NO_FO", 
        "KD_PRS", "NA_PRS", "NO_PRS", "NOTES", "USRNM", "PER", "TG_SMP", "GOL", "CBG"
    ];
}
