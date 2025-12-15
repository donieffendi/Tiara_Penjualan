<?php

namespace App\Models\OLain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Jackh extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'jackh';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        "kodeh", "namah", "undian", "gol", "max", "kirim" 
    ];
}
