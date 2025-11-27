<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Gdg extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'gdg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KODE", "NAMA",  "USRNM", "TG_SMP"
    ];
}
