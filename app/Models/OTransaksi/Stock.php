<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "TGL", "PER", "FLAG", "KD_BRG", "NA_BRG", "NOTES", "KG", 
		"USRNM", "TG_SMP","created_by", "updated_by",
		"deleted_by", "CBG"
    ];
}