<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Supd2 extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'supd2';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "SUPP", "SUB", "NOITEM", "KD_BRG", "KLK1", "HARGA", "PPN"
    ];
}
