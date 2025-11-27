<?php

namespace App\Models\OTransaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbsupDetail extends Model
{
    use HasFactory;

    protected $table = 'ubsupd';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable =
    [
        "NO_BUKTI", "KODES", "NAMAS", "KET", "FLAG", "PER", "GOL", "NM_BRG",
        "MERK", "UKURAN", "KD_BRG", "KLP", "HARGA", "DISC", "BY_ANGKUT", "PPN",
        "KET_KMS", "MO", "KLK", "N_POINT", "KIRA_LPP", "KET_X", "KET_PB", "POSTED1", "TOLAK", "POSTED", "TOLAK1",
        "EMAIL", "E_BARU"
    ];
}