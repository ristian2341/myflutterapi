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

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];
}
