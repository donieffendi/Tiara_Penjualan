<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukarDetail extends Model
{
    use HasFactory;

    protected $table = 'tukard';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "KD_BRG", "NA_BRG", "SATUAN", "HARGA", "TOTAL",
        "QTY", "KET", "PER", "FLAG", "CBG"
    ];
}
