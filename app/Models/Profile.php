<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Profile extends Model
{
    
    protected $table = 'profile';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'code','user_code', 'nama_lengkap','whatsapp','alamat','kota','propinsi','facebook','instagram','tiktok','created_at','updated_at','created_by','updated_by'
    ];

    public function getCode()
    {
        $no = 1;
        $user = Profile::where('code','like',date('Ymd')."%")->first();
        if(!empty($user)){
            $no = (int)substr($user->code, 8, 4) + 1;
        }
        
        return date('Ymd').sprintf("%04s",$no);
    }
}
