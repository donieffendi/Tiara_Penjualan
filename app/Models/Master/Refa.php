<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Refa extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'refa';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KA", "REF","USRNM", "TG_SMP", "KODET", "KD_BRG", "NA_BRG", "REC"
    ];
}
