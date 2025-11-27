<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Brg extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'brg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KD_BRG", "NA_BRG", "JENIS", "SATUAN","GOL", "USRNM", "TG_SMP", "PPN", "SUPP", "NAMAS",
        "SATUAN_BELI", "KALI", "ACNOA", "NACNOA", "ACNOB","NACNOB", "ROP", "HJUAL", "SMIN", "SMAX",
        "DIAMETER", "TEBAL", "PANJANG", "LEBAR", "KG", "GROUP", "SUB_GROUP", "H_MINA", "H_MINB", "H_MINC",
        "created_by", "created_at", "updated_by", "updated_at", "CBG","KD_BRG2", "TARIK", "MASA_EXP", "KET_UK", "KET_KEM"	
    ];
}
