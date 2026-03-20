<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Gudang, Pembelian, HistoryStock};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceGudang
{
    public function listgudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportgudang')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Gudang::where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('alamat','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('no_hp','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                $results['count_used'][$data->id] = Pembelian::where('kode_gudang', $data->id)->count();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function newgudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newgudang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:200|unique:db_gudang',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
                'jenis_gudang' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            // $pool = '123456789';
            // $otp = substr(str_shuffle(str_repeat($pool, 4)), 0, 4);
            // $dataAll = Gudang::orderBy('created_at', 'DESC')->first();
            // $countData = Gudang::count();
            // if($countData <> 0){
            //     $newCodeData = substr($dataAll->code_data,-4);
            //     $newCodeData = $newCodeData + 1;
            // }else{
            //     $newCodeData = 1;
            // }
            // $newCodeData = str_pad($newCodeData, 4, "0", STR_PAD_LEFT);
            // $newCodeData = $otp."".$newCodeData;
            // $newCodeData = ltrim($newCodeData, '0');

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 10)), 0, 10); 
            $newCodeData = $otp."".$otpa;
            $newCodeData = ltrim($newCodeData, '0');

            $savedata = Gudang::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'nama' => $request->get('nama'),
                'no_hp' => $request->get('no_hp_pic'),
                'alamat' => ucfirst($request->get('alamat')),
                'jenis_gudang' => $request->get('jenis_gudang'),
                'status_data' => "Aktif",
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
                    'activity' => 'Tambah data gudang ['.$request->nama.' - '.$newCodeData.']',
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

    public function viewgudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['gudang'] = Gudang::where('id', $request->id)->first();
            if($getdata['gudang']){ 
                $count_used = HistoryStock::where('kode_kantor',$viewadmin->kode_kantor)->where('kode_gudang', $getdata['gudang']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editgudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editgudang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Gudang::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'nama' => 'required|string|max:200',
                    'no_hp_pic' => 'required|string|max:200',
                    'alamat' => 'required|string',
                    'jenis_gudang' => 'required|string|max:200',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama != $getdata->nama){
                    $validator = Validator::make($request->all(),['nama'=>'required|string|max:200|unique:db_gudang']);
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }
                
                $updatedata = Gudang::where('id', $request->get('id'))
                    ->update([
                        'nama' => ucfirst($request->get('nama')),
                        'no_hp' => $request->get('no_hp_pic'),
                        'alamat' => ucfirst($request->get('alamat')),
                        'jenis_gudang' => $request->get('jenis_gudang'),
                        'status_data' => $request->get('status_data'),
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
                        'activity' => 'Update data gudang ['.$getdata->nama.' - '.$getdata->code_data.']',
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

    public function upstatusgudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editgudang')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Gudang::where('id', $request->id)->first();
            if($getdata){
                if($getdata->status_data == 'Tidak Aktif'){
                    $statusdata = 'Aktif';
                }else{
                    $statusdata = 'Tidak Aktif';
                }
                $updatedata = Gudang::where('id', $request->get('id'))
                    ->update([
                    'status_data' => $statusdata,
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
                        'activity' => 'Update status gudang ['.$getdata->nama.' - '.$getdata->code_data.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function deletegudang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listgudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletegudang')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Gudang::where('id', $request->id)->first();
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
                        'activity' => 'Hapus data gudang ['.$getdata->nama.' - '.$getdata->code_data.']',
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