<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Supd2chd extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'supd2chd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "SUPP", "SUB", "NOITEM", "KD_BRG", "KLK1", "HARGA", "PPN", "D1", "D2", "D3", "KLK1",
        "MO1", "CAT", "CAT2", "CAT3", "TG_MULAI", "TG_AKHIR", "TGRB"
    ];
}
