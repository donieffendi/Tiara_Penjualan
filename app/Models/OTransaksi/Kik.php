<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Beli extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'beli';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI","TGL", "NO_PO", "PER","KODES", "NAMAS", "KD_BRG","NA_BRG", "KG", "HARGA", "TOTAL",
		"USRNM", "TG_SMP",
		"USRNM_ED", "TG_SMP_ED", "CBG"
    ];
}
