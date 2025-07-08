<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //

    public function register(Request $req)
    {
        $validate = $this->validate($req,[
            'email' => 'required|max:100',
            'password' => 'required|max:150',
            'nama_panggilan'=> 'required|max:150',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Semua Kolom Wajib Diisi!',
                'data'   => $validator->errors()
            ],401);
        }else{
            $user = new User();
            $user->code = $this->getCode();
            $user->username = $req->email;
            $user->nama_panggilan = $req->nama_panggilan;
            $user->password = Has::make($req->password);
            if ($user->save()){
                return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
            } else {
                return response()->json(['message' => 'Failed to save user'], 500);
            }
        }
    }

    public function updatePassword(Request $req)
    {
        $validate = $this->validate($req,[
            'verify_code' => 'required|char:10',
            'code' => 'required|char:14',
            'password_lama' => 'required|max:150',
            'password' => 'required|max:150',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Semua Kolom Wajib Diisi!',
                'data'   => $validator->errors()
            ],401);
        }else{
            $user = User::where([['code','=',$req->code],['verify_code','=',$req->verify_code]])->first();
            $password_lama = Has::make($req->password_lama);
            if($password_lama == $user->password)
            {
                $user->password = Has::make($req->password);
                if ($user->save()){
                    return response()->json(['message' => 'User created successfully', 'data' => $user], 200);
                } else {
                    return response()->json(['message' => 'Failed to save user'], 500);
                }
            }
        }
    }

    public function getCode()
    {
        $no = 1;
        $user = User::whereLike('code',date('Ymd')."%")->first();
        if(!empty($user)){
            $lastCode = substr($user->code, 0, 8);
            $no = (int)substr($lastCode, 8, 4) + 1;
        }

        return date('Ymd').sprintf("%04s",$no);
    }
}
