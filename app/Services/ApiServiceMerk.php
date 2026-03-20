<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Merk, Barang};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceMerk
{
    public function listmerk($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listmerk')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportmerk')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Merk::where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                $results['count_used'][$data->id] = Barang::where('kode_brand', $data->id)->count();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function newmerk($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listmerk')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newmerk')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:200|unique:db_brand',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 10)), 0, 10); 
            $newCodeData = $otp."".$otpa;
            $newCodeData = ltrim($newCodeData, '0');

            $savedata = Merk::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'nama' => $request->get('nama'),
            ]);

            if($savedata){
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData_activity = $time."".$otp;
                $newCodeData_activity = ltrim($newCodeData_activity, '0');
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData_activity,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Tambah data merk ['.$request->nama.' - '.$newCodeData.']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function viewmerk($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listmerk')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['merk'] = Merk::where('id', $request->id)->first();
            if($getdata['merk']){ 
                $count_used = Barang::where('kode_brand', $getdata['merk']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editmerk($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listmerk')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editmerk')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Merk::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'nama' => 'required|string|max:200',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama != $getdata->nama){
                    $validator = Validator::make($request->all(),['nama'=>'required|string|max:200|unique:db_brand']);
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }
                
                $updatedata = Merk::where('id', $request->get('id'))
                    ->update([
                        'nama' => ucfirst($request->get('nama')),
                ]);

                if($updatedata){
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData_activity = $time."".$otp;
                    $newCodeData_activity = ltrim($newCodeData_activity, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData_activity,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Update data merk ['.$getdata->nama.' - '.$getdata->code_data.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function deletemerk($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listmerk')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletemerk')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Merk::where('id', $request->id)->first();
            if($getdata){
                $DelData = $getdata->delete();
                if($DelData){
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData_activity = $time."".$otp;
                    $newCodeData_activity = ltrim($newCodeData_activity, '0');
                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData_activity,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Hapus data merk ['.$getdata->nama.' - '.$getdata->code_data.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }
}