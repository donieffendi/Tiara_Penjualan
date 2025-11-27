<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class RekananhDetail extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'rekanand';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable = 
    [
        'ID',
        'NO_BUKTI',
        'REC',
        'SUB',
        'KOMISI',
        'MARGIN'
    ];
}