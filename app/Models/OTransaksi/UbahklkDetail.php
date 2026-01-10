<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbahklkDetail extends Model
{
    use HasFactory;

    protected $table = 'histod';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KODE", "URAIAN", "KET_UK", "FLAG", "PER", "REC", "LPH", "KLK", "KLKBR", "SMIN", "SMAX"
    ];
}