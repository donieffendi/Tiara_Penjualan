<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Lbhijau extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'lbhijau';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "TGZ", "SOP", "TMM", "KD_PRM", "TYPE", "KD_BRG", "KONDISI", "KODES", "NAMAS", "PER",
        "JNS", "TGL", "QTY_BELI", "RP_BELI", "RP_BELI_MAX", "KELIPATAN", "DAPAT", "TG_MULAI", "TG_AKHIR",
        "JM_MULAI", "JM_AKHIR", "GOL", "FLAG", "KET", "USRNM", "BRG", "CBG", "NKARTU", "KONDISI"
    ];
}
