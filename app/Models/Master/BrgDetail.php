<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrgDetail extends Model
{
    use HasFactory;

    protected $table = 'brgd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "ID", "KD_BRG", "RING", "HARGA", "HARGA2", "HARGA3", "HARGA4", "HARGA5", "REC",
        "HARGA6", "HARGA7", "CBG"
    ];
}
