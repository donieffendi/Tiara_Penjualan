<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Prs extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'prs';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "KD_PRS", "NA_PRS", "AKHIR", "USRNM", "TG_SMP"
    ];
}
