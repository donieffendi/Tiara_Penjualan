<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class KhususDetail extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'pod_dc_ts';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "REC", "TGO", "TGL_MULAI", "TYPE", "NO_BUKTI", "TGL", "PER", "KD_BRG", "NA_BRG", "KET_UK", "KET_KEM",
        "KDLAKU", "QTY", "SISA", "HARGA", "TOTAL", "KET", "TG_SMP", "CBG", "ID", "STOCKZ" 
    ];
}
