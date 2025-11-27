<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Ubsup extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'ubsup';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "NO_BUKTI", "TGL", "KODES", "PER", "NAMAS", "KOTA", "NOTES", "POSTED",
        "FLAG", "GOL", "KET", "CBG", "TG_SMP", "created_by", "POSTED1", "TOLAK", "TOLAK1"
    ];
}
