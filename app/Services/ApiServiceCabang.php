<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Cabang, Pembelian};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceCabang
{
    public function listcabang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listcabang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportcabang')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['list'] = Cabang::where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('kode_cabang','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama_cabang','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama_pic','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor_pic','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('alamat','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama_cabang', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                $results['count_used'][$data->id] = Pembelian::where('kode_cabang', $data->id)->count();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }
    
    public function newcabang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listcabang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newcabang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'nama_cabang' => 'required|string|max:200|unique:db_cabang',
                'nama_pic' => 'required|string|max:200',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
            $time = Carbon::now()->format('Ymdhis');
            $newCodeData = $time."".$otp;
            $newCodeData = ltrim($newCodeData, '0');

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 10)), 0, 10); 
            $newKodeCabang = $otp."".$otpa;
            $newKodeCabang = ltrim($newKodeCabang, '0');

            $savedata = Cabang::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'kode_cabang' => $newKodeCabang,
                'nama_cabang' => $request->get('nama_cabang'),
                'nama_pic' => $request->get('nama_pic'),
                'nomor_pic' => $request->get('no_hp_pic'),
                'alamat' => ucfirst($request->get('alamat')),
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
                    'activity' => 'Tambah data cabang ['.$request->nama_cabang.' - '.$newKodeCabang.']',
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

    public function viewcabang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listcabang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['cabang'] = Cabang::where('id', $request->id)->first();
            if($getdata['cabang']){ 
                $count_used = Pembelian::where('kode_cabang', $getdata['cabang']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editcabang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listcabang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editcabang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Cabang::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'nama_cabang' => 'required|string|max:200',
                    'nama_pic' => 'required|string|max:200',
                    'no_hp_pic' => 'required|string|max:200',
                    'alamat' => 'required|string',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama_cabang != $getdata->nama_cabang){
                    $validator = Validator::make($request->all(),['nama_cabang'=>'required|string|max:200|unique:db_cabang']);
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }
                
                $updatedata = Cabang::where('id', $request->get('id'))
                    ->update([
                        'nama_cabang' => ucfirst($request->get('nama_cabang')),
                        'nama_pic' => $request->get('nama_pic'),
                        'nomor_pic' => $request->get('no_hp_pic'),
                        'alamat' => ucfirst($request->get('alamat')),
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
                        'activity' => 'Update data cabang ['.$getdata->nama_cabang.' - '.$getdata->kode_cabang.']',
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

    public function deletecabang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listcabang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletecabang')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
                $getdata = Cabang::where('id', $request->id)->first();
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
                            'activity' => 'Hapus data cabang ['.$getdata->nama_cabang.' - '.$getdata->kode_cabang.']',
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