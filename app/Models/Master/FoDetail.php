<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoDetail extends Model
{
    use HasFactory;

    protected $table = 'fod';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "KD_PRS", "NA_PRS", "KD_BHN", "NA_BHN", "SATUAN", "QTY", "KET"
    ];
}
