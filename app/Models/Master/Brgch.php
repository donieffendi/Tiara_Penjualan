<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Brgch extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'brgch';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "NO_TAMU", "FLAG", "PER", "ID_BRG", "SUB", "KDBAR", "KD_BRG", "NA_BRG", "NA_FILE", "TYPE", "USRNM",
        "created_by", "created_at", "updated_by", "updated_at", "CBG","KD_BRG2", "TARIK", "MASA_EXP", "KET_UK", "KET_KEM"	
    ];
}
