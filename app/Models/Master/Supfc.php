<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Supfc extends Model
{
    use HasFactory;

    // ganti 2
    protected $table = 'Supfc';
    protected $primaryKey = 'kodes';
    public $timestamps = false;

    //ganti 3
    protected $fillable = [
        'kodes',
        'namas',
        'stand',
        'margin',
        'AN_B',
        'no_rek',
        'Nama_B',
        'kota_B',
    ];
}
