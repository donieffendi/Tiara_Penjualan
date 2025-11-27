<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LbhijauDTerkait extends Model
{
    use HasFactory;

    protected $table = 'lbhijaud_terkait';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KD_BRG", "NA_BRG", "PER",  "KET_UK", "KET_KEM"
    ];
}
