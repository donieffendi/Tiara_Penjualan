<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Sub extends Model
{
    use HasFactory;

    protected $table = 'aotprice';
    protected $primaryKey = 'SUB';
    public $timestamps = false;

    protected $fillable = 
    [
        "SUB", "KELOMPOK", "PERSEN_HJ", "PERSEN", "TYPE"
    ];
}
