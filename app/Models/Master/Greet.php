<?php
namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Greet extends Model
{
    use HasFactory;

    protected $table = 'greet';
    protected $primaryKey = 'BARIS';
    public $timestamps = false;

    protected $fillable = 
    [
        "KATA"
    ];
}
