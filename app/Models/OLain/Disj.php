<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Disj extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'disj';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KD_BRG", "NA_BRG", "QTY1", "QTY2", "QTY3", "QTY4", "DIS1", "DIS2", "DIS3", "DIS4", "TH1", "TH2", "TH3", "TH4",
        "BATAS_DC", "KELIPATAN", "TGL_MULAI", "TGL_SELESAI", "JAM_MULAI", "JAM_SELESAI", "USRNM", "TG_SMP", "AKTIF"
    ];
}
