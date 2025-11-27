<?php

namespace App\Models\FMaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Acnox extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'acnox';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "ACNO", "NAMA", "BACNO", "BNAMA"
    ];
}
