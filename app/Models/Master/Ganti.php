<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ganti extends Model
{
    use HasFactory;

    protected $table = 'ganti';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "no_bukti", "tgl", "FLAG", "per", "notes", "usrnm", "tg_smp", "CBG"
    ];
}
