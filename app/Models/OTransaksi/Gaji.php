<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gaji';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI","TGL","PER","NOTES","TOTAL_GAJI","TOTAL_BON","TOTAL_LAIN","TOTAL_NETT",
        "BACNO","BNAMA","POSTED","USRNM","TG_SMP", "created_by", "updated_by",
		"deleted_by"
    ];
}
