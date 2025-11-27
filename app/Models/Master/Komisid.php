<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class KomisiDetail extends Model
{
    use HasFactory;

    protected $table = 'rekanand';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "REC", "SUB", "KODE", "NAMA", "KOMIS", "MARGIN"
    ];

}
