<?php

namespace App\Models\FTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use HasFactory;

    protected $table = 'bankd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "ACNO", "NACNO", "URAIAN", "DEBET", "KREDIT", "JUMLAH", "FLAG", "TYPE",
        "NO_HUT", "NO_FAKTUR"
    ];
}
