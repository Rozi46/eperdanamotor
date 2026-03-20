<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Karyawan};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceKaryawan
{
    public function listkaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportkaryawan')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Karyawan::where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('alamat','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('no_hp','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function newkaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newkaryawan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'nama_karyawan' => 'required|string|max:200',
                'tempat_lahir' => 'required|string|max:200',
                'tanggal_lahir' => 'required|string|max:200',
                'jenis_kelamin' => 'required|string|max:200',
                'jabatan' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 10)), 0, 10); 
            $newCodeData = $otp."".$otpa;
            $newCodeData = ltrim($newCodeData, '0');

            $tgl_lahir = Carbon::parse($request->get('tanggal_lahir'))->format('Y-m-d');

            $savedata = Karyawan::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'nama' => $request->get('nama_karyawan'),
                'tempat_lahir' => $request->get('tempat_lahir'),
                'tanggal_lahir' => $tgl_lahir,
                'jenis_kelamin' => $request->get('jenis_kelamin'),
                'jabatan' => ucfirst($request->get('jabatan')),
                'no_hp' => $request->get('no_hp'),
                'alamat' => ucfirst($request->get('alamat')),
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
                    'activity' => 'Tambah data karyawan ['.$request->nama_karyawan.' - '.$newCodeData.']',
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

    public function viewkaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['karyawan'] = Karyawan::where('id', $request->id)->first();
            if($getdata['karyawan']){           
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editkaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editkaryawan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Karyawan::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'nama_karyawan' => 'required|string|max:200',
                    'tempat_lahir' => 'required|string|max:200',
                    'tanggal_lahir' => 'required|string|max:200',
                    'jenis_kelamin' => 'required|string|max:200',
                    'jabatan' => 'required|string|max:200',
                    'no_hp' => 'required|string|max:200',
                    'alamat' => 'required|string',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama != $getdata->nama){
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }

                $tgl_lahir = Carbon::parse($request->get('tanggal_lahir'))->format('Y-m-d');
                
                $updatedata = Karyawan::where('id', $request->get('id'))
                    ->update([
                        'nama' => ucfirst($request->get('nama_karyawan')),
                        'tempat_lahir' => $request->get('tempat_lahir'),
                        'tanggal_lahir' => $tgl_lahir,
                        'jenis_kelamin' => $request->get('jenis_kelamin'),
                        'jabatan' => ucfirst($request->get('jabatan')),
                        'no_hp' => $request->get('no_hp'),
                        'alamat' => ucfirst($request->get('alamat')),
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
                        'activity' => 'Update data karyawan ['.$getdata->nama.' - '.$getdata->code_data.']',
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

    public function upstatuskaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editkaryawan')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Karyawan::where('id', $request->id)->first();
            if($getdata){
                if($getdata->status_data == 'Tidak Aktif'){
                    $statusdata = 'Aktif';
                }else{
                    $statusdata = 'Tidak Aktif';
                }
                $updatedata = Karyawan::where('id', $request->get('id'))
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
                        'activity' => 'Update status karyawan ['.$getdata->nama.' - '.$getdata->code_data.']',
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

    public function deletekaryawan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listkaryawan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletekaryawan')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $getdata = Karyawan::where('id', $request->id)->first();
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
                        'activity' => 'Hapus data karyawan ['.$getdata->nama.' - '.$getdata->code_data.']',
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