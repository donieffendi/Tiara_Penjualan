<?php

namespace App\Models\OTools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Poin extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'poin';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "CBG", "FLAG", "TYPE", "USRNM", "TG_SMP" 
    ];
}
