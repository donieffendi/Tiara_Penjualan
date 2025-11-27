<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerimaDetail extends Model
{
    use HasFactory;

    protected $table = 'terimad';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KD_PRS", "NA_PRS", "KD_BHN", "NA_BHN", "SATUAN", "QTYA", "QTY", "KET", "REC", "ID", "PER", "FLAG"
    ];
}
