<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Barang, ListPenjualan, Satuan};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceSatuan
{
    public function listsatuan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listsatuan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportsatuan')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Satuan::where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                $results['count_used'][$data->id] = Barang::where('kode_satuan', $data->id)->count();
                $results['satuan_pecahan'][$data->id] = Satuan::where('id', $data->kode_pecahan)->first();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function listsatuanpecahan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results['detail'] = Satuan::where('isi','1')->orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function newsatuan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listsatuan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newsatuan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:200|unique:db_satuan_barang',
                'isi_satuan' => 'required|string|max:200',
                'status_pecahan' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 10)), 0, 10); 
            $newCodeData = $otp."".$otpa;
            $newCodeData = ltrim($newCodeData, '0');

            $get_data['satuan_pecahan'] = Satuan::where('id', $request->satuan_pecahan)->first();
            if($get_data['satuan_pecahan']){
                $satuan_pecahan = $get_data['satuan_pecahan']->nama;
                $kode_pecahan = $request->get('satuan_pecahan');
            }else{
                $satuan_pecahan = "";
                $kode_pecahan = "";
            }

            $savedata = Satuan::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'nama' => ucfirst($request->get('nama')),
                'isi' => $request->get('isi_satuan'),
                'status_pecahan' => $request->get('status_pecahan'),
                'kode_pecahan' =>  $kode_pecahan,
                'satuan_pecahan' => $satuan_pecahan,
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
                    'activity' => 'Tambah data satuan ['.$request->nama.' - '.$newCodeData.']',
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

    public function viewsatuan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listsatuan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['satuan'] = Satuan::where('id', $request->id)->first();
            if($getdata['satuan']){ 
                $count_used = Barang::where('kode_satuan', $getdata['satuan']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editsatuan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listsatuan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editsatuan')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Satuan::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'nama' => 'required|string|max:200',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama != $getdata->nama){
                    $validator = Validator::make($request->all(),['nama'=>'required|string|max:200|unique:db_satuan_barang']);
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }

                $get_data['satuan_pecahan'] = Satuan::where('id', $request->satuan_pecahan)->first();
                if($get_data['satuan_pecahan']){
                    $satuan_pecahan = $get_data['satuan_pecahan']->nama;
                    $kode_pecahan = $request->get('satuan_pecahan');
                }else{
                    $satuan_pecahan = "";
                    $kode_pecahan = "";
                }
                
                $updatedata = Satuan::where('id', $request->get('id'))
                    ->update([
                        'nama' => ucfirst($request->get('nama')),
                        'kode_pecahan' =>  $kode_pecahan,
                        'satuan_pecahan' => $satuan_pecahan,
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
                        'activity' => 'Update data satuan ['.$getdata->nama.' - '.$getdata->code_data.']',
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

    public function deletesatuan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listsatuan')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletesatuan')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
                $getdata = Satuan::where('id', $request->id)->first();
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
                            'activity' => 'Hapus data satuan ['.$getdata->nama.' - '.$getdata->code_data.']',
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