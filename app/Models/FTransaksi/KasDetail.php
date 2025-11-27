<?php

namespace App\Models\FTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasDetail extends Model
{
    use HasFactory;

    protected $table = 'kasd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "ACNO", "NACNO", "URAIAN", "DEBET", "KREDIT", "JUMLAH", "FLAG", "TYPE",
        "NO_HUT", "NO_FAKTUR"
    ];
}
