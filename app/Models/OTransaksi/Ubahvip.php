<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Ubahvip extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'dis';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "no_bukti", "tgl", "KODES", "per", "NAMAS","notes", "posted", "usrnm",
        "flag", "GOL", "KET", "CBG", "TG_SMP", "created_by", "POSTED1", "TOLAK", "TOLAK1"
    ];
}
