<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Piu extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'piu';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
       "NO_BUKTI", "TGL", "NO_SO", "PER","KODEC", "NAMAC",  "TOTAL", "BAYAR",
		"USRNM", "TG_SMP", "CBG", "TKOM", "KODEP", "NAMAP", "KOM", "FLAG", "LAIN", "HARI"
    ];
}
