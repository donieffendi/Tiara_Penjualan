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
        "NO_BUKTI", "TGL", "PER", "FLAG", "NOTES", "TOTAL_QTY", 
		"USRNM", "TG_SMP", "CBG", "KODES", "NAMAS", "ALAMAT", "KOTA", "HARI", "TDPP",
        "NETT","TPPN","SISA", "NO_PO"
    ];
}
