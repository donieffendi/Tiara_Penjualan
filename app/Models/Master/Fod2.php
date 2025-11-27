<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoDetail2 extends Model
{
    use HasFactory;

    protected $table = 'fod2';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "ID", "NO_BUKTI", "KD_PRS", "NA_PRS", "NO_PRS", "AWAL", "AKHIR" 
    ];
}
