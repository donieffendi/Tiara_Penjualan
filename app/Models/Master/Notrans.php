<?php
namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notrans extends Model
{
    use HasFactory;

    protected $table      = 'notrans';
    protected $primaryKey = 'no_id';
    public $timestamps    = false;

    protected $fillable =
        [
        'trans',
        'per',
        'nom01',
        'nom02',
        'nom03',
        'nom04',
        'nom05',
        'nom06',
        'nom07',
        'nom08',
        'nom09',
        'nom10',
        'nom11',
        'nom12',
    ];
}