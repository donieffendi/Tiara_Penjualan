<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ganti extends Model
{
    use HasFactory;

    protected $table = 'ganti';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "FLAG", "PER", "NOTES", "USRNM", "TG_SMP", "CBG"
    ];
}
