<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Khusus extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'po_dc_ts';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "KRM_EML", "KODES", "NAMAS", "TGO", "TGL_MULAI", "NO_BUKTI", "TGL", "PER", "JTEMPO", "TKK1", "TKKS", "FLAG",
        "TOTAL_QTY", "TOTAL", "NETT", "UTUH", "NOTES", "USRNM", "TG_SMP", "CBG", "KS", "GOLONGAN", "TYPE"
    ];
}
