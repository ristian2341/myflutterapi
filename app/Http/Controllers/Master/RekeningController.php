<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\Master\Rekening;
use App\Http\Controllers\Controller;
use App\Http\Models\Model;

class RekeningController extends Controller
{
    public function loadData()
    {
        try {
            $data_rekening = Rekening::orderBy('created_at', 'desc')->limit(10)->get();
            if(!empty($data_rekening)){
                return response()->json(['statusCode' => 200,"message"=>"Load Data Rekening",'data' => $data]);
            }else{
                return response()->json(['statusCode' => 500,"message"=>"Data Not Found",'data' => []]);
            }
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 404,"print"=>"Data Not Found",'data' => []]);
        }
    }

    public function loadDataOne($id)
    {
        try {
            $data_rekening = Rekening::where("code",$id)->orderBy('created_at', 'desc')->limit(10)->get();
            if(!empty($data_rekening)){
                return response()->json(['statusCode' => 200,"message"=>"Load Data Rekening",'data' => $data]);
            }else{
                return response()->json(['statusCode' => 500,"message"=>"Data Not Found",'data' => []]);
            }
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 404,"print"=>"Data Not Found",'data' => []]);
        }
    }

    public function saveData(Request $req)
    {
        \DB::beginTransaction();
        try {
            $result = true;

            $validate = $this->validate($req,[
                'nomor_rekening' => 'required|max:150',
                'atas_nama' => 'required|max:150',
                'bank' => 'required|max:150',
            ]);

            if(empty($req->code)){
                $rekening = new Rekening();
                $rekening->code = $rekening->getCode(); 
                $rekening->nomor_rekening = $rekening->nomor_rekening; 
                $rekening->atas_nama = $req->atas_nama ?? ""; 
                $rekening->cabang = $req->cabang ?? ""; 
                $rekening->nama_bank = $req->nama_bank ?? ""; 
                $result = $rekening->save();
            }else{
                $result = Rekening::where('code',$id)->update([
                    'nomor_rekening' => $req->nomor_rekening,
                    'atas_nama' => $req->atas_nama,
                    'cabang' => $req->cabang,
                    'nama_bank' => $req->nama_bank,
                ]);
            }

            if ($result) {
                \DB::commit();
                return response()->json(['message' => 'Rekening saved successfully!', 'rekening' => $rekening]);
            } else {
                \DB::rollback();
                return response()->json(['message' => 'Failed to save rekening.'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['statusCode' => 404,"print"=>"Proses save data failed",'data' => []]);
        }
    }
}

