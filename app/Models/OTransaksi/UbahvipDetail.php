<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbahvipDetail extends Model
{
    use HasFactory;

    protected $table = 'disd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "no_bukti", "KD_BRG", "NA_BRG", "qty", "ket_uk", "ket_kem", "rec", "per", "FLAG", "DIS", "HJVIP", "SMAX"
    ];
}