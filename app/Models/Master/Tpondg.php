<?php
namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tpondg extends Model
{
    use HasFactory;

    protected $table = 'tpondg';
    protected $primaryKey = 'NO_ID';
    public $timestamps = false;

    protected $fillable = 
    [
        "NO_BUKTI", "KD_DEPT", "DEPT", "TGL", "NOTES", "USRNM", "NA_BRG", "QTY", "SATUAN", "UKURAN", "MERK",
        "HARGA", "TOTAL", "BATAS1", "AKUNT", "PER", "CBG", "TG_SMP"
    ];
}
