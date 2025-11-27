<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ganti 1
class Sup extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'sup';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

// ganti 3
    protected $fillable = 
    [
        "KODES", "NAMAS", "ALAMAT", "KOTA", 'GOL', "TELPON1", "HP", "KET", 
        "KONTAK", "NPWP", "EMAIL", "AKT", "FAX", "BANK", "BANK_CAB", 
        "BANK_KOTA", "BANK_NAMA", "BANK_REK", "HARI", "USRNM", "TG_SMP", 
        "PN", "NOREK", "NOTBAY", "LDT_NEW",
        "LDT_REP","PLH","PLM","PLL","SKH","SKH_KET","SKM","SKM_KET","SKL","SKL_KET","KET","NKUALITAS","KUALITAS","NHARGA",
        "NOTE_HARGA","NPENGIRIMAN","PENGIRIMAN","NKEAMANAN","KEAMANAN","NKREDIT","KREDIT","NPRODUKSI","PRODUKSI","NPELAYANAN",
        "PELAYANAN","NISO","ISO","NILAI", "PKP", "ALAMAT2"
    ];
}
