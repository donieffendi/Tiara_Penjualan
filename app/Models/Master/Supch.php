<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


//ganti 1
class Supch extends Model
{
    use HasFactory;

// ganti 2
    protected $table = 'supch';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

//ganti 3
    protected $fillable =
    [
        "KODES", "TYPE", "SUP_BARU", "NAMAS", "NAMA", "GOLONGAN", "PEMILIK", "TLP_R", "ALMT_R", "ALMT_K", "KOTA", 
        "ALMT_GD", "TLP_K", "NO_FAX", "NO_HP", "NO_TELEX", "EMAIL", "EMAIL2", "EMAIL3", "GOL_BRG", "KD_PEMBY", "STM_PEMBL", 
        "DISC_PS", "JEN_BRG1", "CARA", "BG_PERS", "STTS", "SUB", "KD_BANK", "NPWP", "NPPKP", "NAMA_B", "NM_NPWP", "CABANG_B", 
        "NO_NPWP", "KOTA_B", "AL_NPWP", "AN_B", "NOREK", "TG_NPWP", "SERI", "FO_KLB", "NF_KLB", "PB_KLB", "ST_KLB", "FF_KLB", 
        "BS_KLB", "MATERAI", "ZONE", "CETAK_SBY", "ACC_PPN", "CAT_LO", "DIS_P4", "RETUR", "KET_HAPUS", "JMN_RETUR", "HARI",
        "TND_SPL", "B_CODE", "JAMIN_RET", "TGL", "VA_GZ", "S_BAR", "NOREK_GZ", "AN_B_GZ", "ANB_VA_GZ", "EMAIL_GZ", "D_BUTOR",
        "CAT_RET", "CAT_PRM", "SR_TERBIT", "BONAFIT", "KEL_PAJAK", "N_AKTIF", "KETNAKTIF", "LAIN1", "LAIN2", "KOD_MIN", "KLB2",  
        "ORDR", "BY_KR", "URAIAN1", "URAIAN2", "KLK", "CAT_SP", "SP", "JAM", "CBG"
    ];
}
