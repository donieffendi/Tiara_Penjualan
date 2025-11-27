<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';
    protected $primaryKey = 'kode';
    public $timestamps = false;

    protected $fillable = 
    [
        'kode',
        'type'
    ];
}