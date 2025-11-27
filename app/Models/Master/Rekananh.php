<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rekananh extends Model
{
    use HasFactory;

    protected $table = 'rekananh';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "TGLM", "TGLS", "TG_SMP", "KODE", "NAMA", "NOTES", "USRNM"
    ];

}
