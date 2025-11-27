<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Tujuan extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'tujuan';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KODET", "NAMAT", "ALAMAT", "KOTA", "USRNM", "TG_SMP"
    ];
}
