<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ganti 1
class Fo extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'fo';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

// ganti 3
    protected $fillable = 
    [
        "NO_BUKTI", "KD_BRG", "NA_BRG", "NOTES", "AKTIF", "TOTAL_QTY", "USRNM", "TG_SMP", "KD_BHN", "NA_BHN", "FLAG"
    ];
}
