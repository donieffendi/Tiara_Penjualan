<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class SupSewa extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'supstand';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KODES", "NAMAS", "KD_DISTRIBUTOR", "KTP", "PRODUK", "AL_PRSH", "AL_PRSH2", "KOTA", "NO_TELP",
        "STS_PJK", "NPWP", "CARA_BYR", "CARA_BYR2", "KET", "EMAIL"
    ];
}