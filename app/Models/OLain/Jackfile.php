<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Jackfile extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'jackfile';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "J_MIN", "J_JACK", "J_UTAMA", "J_SATU", "J_DUA", "J_HIBUR", "J_BELANJA", "J_PERSEN", "J_JAM", "JAM_AK", "JAM_AW1",
        "JAM_AK1", "JAM_AW2", "JAM_AK2", "JAM_AW3", "JAM_AK3", "JAM_AW4", "JAM_AK4", "JAM_AW5", "JAM_AK5", "TGL_AW", "TGL_AK",
        "J_PILIH", "JARAK", "TGL_JACK", "JUMJACK", "PENDING", "USRNM", "CBG", "aktv" 
    ];
}
