<?php

namespace App\Models\FTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoDetail extends Model
{
    use HasFactory;

    protected $table = 'memod';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "ID", "NO_BUKTI", "ACNO", "NACNO", "ACNOB", "NACNOB", "URAIAN", "DEBET", "KREDIT", "JUMLAH", "FLAG",
    ];
}
