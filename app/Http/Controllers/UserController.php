<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Profile;

class UserController extends Controller
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

    public function register(Request $req)
    {
        $validate = $this->validate($req,[
            'email' => 'required|max:100',
            'password' => 'required|max:150',
            'nama_panggilan'=> 'required|max:150',
        ]);

        \DB::beginTransaction();
        try {

            $user = User::where('username','=',$req->user_name)->orWhere('email','',$req->email)->first();
            if(!empty($user))
            {
                return response()->json(['message' => "User Name or Email already Exist"], 401);
            }

            $user = new User();
            $user->code = $this->getCode();
            $user->username = !empty($req->user_name) ? $req->user_name : $req->email;
            $user->email = $req->email;
            $user->phone = $req->phone;
            $user->nama_panggilan = $req->nama_panggilan;
            $user->password = Hash::make($req->password);
            if ($user->save()){
                // add profile 
                $profile = new Profile;
                $profile->code = $profile->getCode();
                $profile->user_code = $user->code;
                $profile->nama_lengkap = isset($req->nama_lengkap) ? $req->nama_lengkap : "";
                $profile->whatsapp = isset($req->whatsapp) ? $req->whatsapp : "";
                if(!$profile->save()){
                    \DB::rollback();
                    return response()->json(['message' => "Save profile failed"], 500);
                }

                \DB::commit();
                return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to save user'], 500);
            }
           
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }    
    }

    public function updatePassword(Request $req)
    {
        $validate = $this->validate($req,[
            'code' => 'required|max:14',
            'password_lama' => 'required|max:150',
            'password' => 'required|max:150',
        ]);

        \DB::beginTransaction();
        try {
            $user = User::where('code',$req->code)->first();
            if(!$user){
                return response()->json(['message' => 'User not found'], 404);
            }
            
            if(!Hash::check($req->password_lama,$user->password)){
                return response()->json(['message' => 'Old Password is wrong'], 505);
            }

            $update = User::where('code',$req->code)->update([
                'password' => Hash::make($req->password),
            ]);
            
            if($update){
                \DB::commit();
                return response()->json(['message' => 'Update Password successfully', 'data' => $user], 200);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to save user'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $req)
    {
        $validate = $this->validate($req,[
            'verify_code' => 'required|max:6',
            'password' => 'required|max:150',
            're_password' => 'required|max:150',
        ]);

        \DB::beginTransaction();
        try {
            $user = User::where('verify_code',$req->verify_code)->first();
            if(!$user){
                return response()->json(['message' => 'User not found'], 404);
            }

            if($req->password !== $req->re_password){
                return response()->json(['message' => 'Retype password is not same'], 505);
            }
    
            if($req->verify_code !== $user->verify_code){
                return response()->json(['message' => 'Verify Code invalid'], 505);
            }
    
            $update = User::where('code',$user->code)->update([
                'password' => Hash::make($req->password),
                'verify_code' => "",
            ]);
            
            if($update){
                \DB::commit();
                return response()->json(['message' => 'Reset Password successfully'], 200);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to save user'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $req)
    {
        $validate = $this->validate($req,[
            'user_name' => 'required|max:100',
            'password' => 'required|max:150',
        ]);

        $user = User::where('username','=',$req->user_name)->orWhere('username','',$req->email)->first();
        if(empty($user)){
            return response()->json(['message' => 'User or Password not match'], 400);
        }

        if(!empty($user) && !Hash::check($req->password,$user->password)){
            return response()->json(['message' => 'User or Password not match'], 400);
        }

        \DB::beginTransaction();
        try {
            $token =  Crypt::encrypt(substr($user->code.date('ymdHi'),2),32);
            
            $update = User::where('code','=',$user->code)->update([
                'login_at' => date('Y-m-d H:i:s'),
                'access_token' => $token,
            ]);
            
            $data = [
                'code' => $user->code,
                'username' => $user->username,
                'email' => $user->email,
                'nama_panggilan' => $user->developer,
                'access_token' => $token,
                'developer' => isset($user->developer) ? $user->developer : 0,
                'supervisor' => isset($user->supervisor) ? $user->supervisor : 0,
            ];
            if($update){
                \DB::commit();
                return response()->json(['statusCode' => 200,'message' => 'Login successfully', 'data' =>  $data], 200);
            } else {
                \DB::rollback();
                return response()->json(['statusCode' => 500,'message' => 'Failed to login user'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function profile(Request $req)
    {
        $validate = $this->validate($req,[
            'nama_lengkap' => 'required|max:100',
        ]);
        $user = User::where(['code' => $req->code])->first();
        if(empty($user))
        {
            return response()->json(['message' => 'User Not Found'], 500); 
        }

        $profile = Profile::where('user_code',$user->code)->first();
        if(empty($profile))
        {
            return response()->json(['message' => 'Profile User Not Found'], 500); 
        }

        \DB::beginTransaction();
        try {
            $update_profile = Profile::where('code','=',$profile->code)->update([
                'nama_lengkap' => $req->nama_lengkap,
                'whatsapp' => $req->whatsapp,
                'alamat' => $req->alamat,
                'kota' => $req->kota,
                'propinsi' => $req->propinsi,
                'facebook' => $req->facebook,
                'instagram' => $req->instagram,
                'tiktok' => $req->tiktok,
            ]);

            if($update_profile){
                \DB::commit();
                return response()->json(['message' => 'Update Profile successfully', 'data' =>  $profile], 200);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to update profile'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function forgotPassword(Request $req)
    {
        \DB::beginTransaction();
        try {
            
            $validate = $this->validate($req,[
                'email' => 'required|max:150'
            ]);

            $user = User::where('email',$req->email)->first();
            if(!$user){
                return response()->json(['message' => 'Email not found'], 404);
            }

            // generate verify_code & update into tabel user//
            $verify_code = random_int(100000, 999999);
            $update = User::where('code',$user->code)->update([
                'verify_code' => $verify_code,
            ]);

          
            $message = [
                'title'     => 'Verification code Forgot Password',
                'intro'     => "Verify Code : ".$user->verify_code,
                'link'      => '',
                'code_verify' => $verify_code,
                'to_email'  => $req->email,
                'to_name'   => $req->email." ".$user->nama_panggilan,
            ];

            $data = [
                'name'=> $user->nama_panggilan,
                'verify_code' => $verify_code,
            ]; 

            Mail::send('_mail_layout', $data, function($message) {
                $message->to('ristian.rehi@gmail.com', 'Test Name')->subject('Forgot Password');
                $message->from('ristian.rehi@gmail.com','Notification Email');
            });

            if($update){
                \DB::commit();
                return response()->json(['message' => 'Verify code send your email. please check your inbox or spam mail'], 200);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to save user'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getCode()
    {
        $no = 1;
        $user = User::where('code','like',date('Ymd')."%")->first();
        if(!empty($user)){
            $no = (int)substr($user->code, 8, 4) + 1;
        }
        
        return date('Ymd').sprintf("%04s",$no);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
}
