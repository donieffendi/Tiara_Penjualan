<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Gantid extends Model
{
    use HasFactory;

    protected $table = 'gantid';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "ID", "REC", "KD_BRG", "NA_BRG", "KD_BRG2", "KET_UK", "KET_KEM", "KET", "CBG", "FLAG", "PER"
    ];
}
