<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Brgfc extends Model
{
    use HasFactory;

    // ganti 2
    protected $table = 'masks';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    //ganti 3
    protected $fillable = [
        'SUB',
        'kdbar',
        'na_brg',
        'hj',
        'hb',
        'LOKASI',
        'KELOMPOK',
        'tkp',
        'flagstok',
        'supp',
        'namas',
        'BARCODE',
        'STAND',
        'TYPE',
        'dis',
        'MARGIN',
    ];
}
