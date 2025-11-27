<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MarginKsr extends Model
{
    use HasFactory;

    protected $table = 'marg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "JNS", "MARGIN", "USRNM", "TG_SM"
    ];
}
