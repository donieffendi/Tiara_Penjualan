<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Brgdt extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'brgdt';
    protected $primaryKey = 'KD_BRG';
    public $timestamps = false;

//ganti 3
    protected $fillable = [
        'KD_BRG',
        'HB',
    ];
}