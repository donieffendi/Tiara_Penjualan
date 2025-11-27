<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiuDetail extends Model
{
    use HasFactory;

    protected $table = 'piud';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "NO_FAKTUR", "TOTAL", "BAYAR", "SISA", "KET", "FLAG", "TGL_FAKTUR",
        "LAIN", "NO_TAGIH"
    ];
}
