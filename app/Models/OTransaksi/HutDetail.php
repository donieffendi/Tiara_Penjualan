<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HutDetail extends Model
{
    use HasFactory;

    protected $table = 'hutd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "ID", "NO_FAKTUR", "TOTAL", "BAYAR", "SISA", "KET", "CBG"
    ];
}
