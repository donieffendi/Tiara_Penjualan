<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Beliz extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'beliz';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI","TGL", "NO_PO", "FLAG", "GOL", "PER","KODES", "NAMAS", "TOTAL_QTY", "TOTAL", "NOTES",
		"USRNM", "TG_SMP", "ALAMAT", "KOTA", "ACNOA", "NACNOA", "NO_BANK", "BACNO", "BNAMA", 
        "TOTAL", "PPN", "NETT", "GOL", "NO_BELI"
    ];
}
