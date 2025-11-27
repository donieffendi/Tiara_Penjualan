<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiDetail extends Model
{
    use HasFactory;

    protected $table = 'gajid';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "ID","REC","NO_BUKTI","KODEP","NAMAP","HARI","GAJI","BON","BAYAR_BON","LAIN","NETT","TYPE", "NOTES","created_by", "updated_by",
		"deleted_by"
    ];
}
