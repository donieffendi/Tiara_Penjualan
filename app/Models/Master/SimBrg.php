<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class SimBrg extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'sim_brg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = [
        'KD_BRG',
        'NA_BRG',
        'KET_UK',
        'KET_KEM',
        'CRUD',
        'KET',
        'FLAG',
        'CBG',
        'TG_SMP',
        'USERX',
    ];
}