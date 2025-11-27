<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Muat extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'muat';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI","TGL", "NO_BELI", "FLAG", "GOL", "PER","KODES", "NAMAS", "TOTAL_QTY", "TOTAL", "NOTES",
		"USRNM", "TG_SMP", "ALAMAT", "KOTA", "KD_BRG", "NA_BRG", "NO_PO", "QTY_BELI", "GOL", "PKP", "CBG"
    ];
}
