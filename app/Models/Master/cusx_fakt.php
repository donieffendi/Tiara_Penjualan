<?php

namespace App\Models\FTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Bank extends Model
{
    use HasFactory;

    // ganti 2
    protected $table = 'cusx_fakt';
    protected $primaryKey = 'KODEC';
    public $timestamps = false;

    //ganti 3
    protected $fillable =
    [
        "KODEC",
        "NAMAC",
        "ALAMAT",
        "NPWP"
    ];
}
