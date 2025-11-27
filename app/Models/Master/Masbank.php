<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Masbank extends Model
{
    use HasFactory;

    protected $table = 'masbank';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_ID","KODE","NM_BANK","TYPE","NOBANK","CR_CARD","BATAS","BANK","BY_CARD","USRNM","TG_SMP"
    ];
}
