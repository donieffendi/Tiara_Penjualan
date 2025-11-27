<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LbhijauDetail extends Model
{
    use HasFactory;

    protected $table = 'lbhijaud';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "REC", "NO_BUKTI", "NO_BUKTI2", "ID", "KD_BHN", "NA_BHN", "KD_BHN", "NA_BHN","SATUAN","QTY", "HARGA", 
        "TOTAL", "KET", "GOL", "FLAG", "KD_BRG", "NA_BRG", "PER",  "DPP", "PPN", "DISK"
    ];
}
