<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

use App\Models\Setting;

final class SettingController extends Controller
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

    public static function setting()
    {
        $setting = Setting::first();

        if(!empty($setting)){
            return response()->json(['message' => 'Data Setting','data' => $setting], 200);
        }else{
            return response()->json(['message' => 'Setting Not Found','data' => $setting], 200);
        }
    }
}
