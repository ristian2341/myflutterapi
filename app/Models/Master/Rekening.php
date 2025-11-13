<?php

namespace App\Models\Master;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use App\Models\Master;

class Rekening extends Model
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = 'rekening';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code', 'nomor_rekening', 'atas_nama', 'nama_bank', 'cabang', 'created_at', 'updated_at'
    ];

    public function getCode()
    {
        $no = 1;
        $rekening = $this::where('code','like',date('Ymd')."%")->first();
        if(!empty($rekening)){
            $no = (int)substr($rekening->code, 8, 4) + 1;
        }
        
        return date('Ymd').sprintf("%03s",$no);
    }
}
