<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HterimaDetail extends Model
{
    use HasFactory;

    protected $table = 'hterimad';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KD_BRG", "NA_BRG", "SATUAN", "QTYA", "QTY", "HARGA", "TOTAL", "KET", "REC", "ID", "PER", "FLAG",
        "QTY1"
    ];
}
