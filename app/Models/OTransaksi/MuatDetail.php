<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuatDetail extends Model
{
    use HasFactory;

    protected $table = 'muatd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "NO_CONT", "SOPIR", "NOPOL", "TELP", "SEAL", "T_CONT", "TOTAL", 
        "KET", "GOL", "PER", "FLAG", "QTY"
    ];
}
