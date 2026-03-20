<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Barang, ListPenjualan, Satuan};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceJasa
{
    public function getgenerate($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{  
            // format kode barcode EAN-13 = 13 Digit
            // contoh kode barcode 8991234567890
            // 899 = kode negara untuk indonesia
            // 123456 = kode produsen
            // 789 = kode produk
            // 0 = digit cek (check digit)
            $codenegara = '899';
            $pool = '0123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 6)), 0, 6); 
            $poola = '0123456789';
            $otpa = substr(str_shuffle(str_repeat($poola, 3)), 0, 3); 
            $poolb = '0123456789';
            $otpb = substr(str_shuffle(str_repeat($poolb, 1)), 0, 1); 
            $newCodeData = $codenegara."".$otp."".$otpa."".$otpb;
                
            return response()->json(['status_message' => 'success','results' => $newCodeData]);
        }
    } 
    
    public function listjasa($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listjasa')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportjasa')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Barang::where('type_produk','LIKE','Jasa')
                    ->where(function($query) use ($request) {
                    $query->Where('kode','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                $results['count_used'][$data->id] = ListPenjualan::where('kode_barang', $data->id)->count();
                $results['satuan'][$data->id] = Satuan::where('id', $data->kode_satuan)->first();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function newjasa($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listjasa')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newjasa')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200|unique:db_barang',
                'satuan' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            if($request->harga_jual == '0'){
                $margin_jual1 = '0';
            }else{
                $margin_jual1 = ($request->harga_jual - $request->harga_beli) / $request->harga_beli * 100;                
            }

            $poola = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otpa = substr(str_shuffle(str_repeat($poola, 1)), 0, 1);
            $pool = '123456789';
            $otp = substr(str_shuffle(str_repeat($pool, 14)), 0, 14); 
            $newCodeData = $otp."".$otpa;
            $newCodeData = ltrim($newCodeData, '0');

            $savedata = Barang::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'kode' => $request->get('kode_barang'),
                'nama' => ucfirst($request->get('nama')),
                'kode_satuan' => $request->get('satuan'),
                'kode_jenis' => '8f6d0336-d838-11eb-8038-204747ab6caa',
                'kode_brand' => '6cf71886-d838-11eb-8038-204747ab6caa',
                'kode_supplier' => 'db7ed68e-478b-11ec-9373-c9aafae6932b',
                'kode_satuan_default' => $request->get('satuan'),
                'type_produk' => 'Jasa',
                'harga_beli' => $request->get('harga_beli'),
                'margin_jual1' => $margin_jual1,
                'harga_jual1' => $request->get('harga_jual'),
                'margin_jual2' => '0',
                'harga_jual2' =>'0',
                'margin_jual3' => '0',
                'harga_jual3' => '0',
                'margin_jual4' => '0',
                'harga_jual4' => '0',
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
                    'activity' => 'Tambah data jasa ['.$request->nama.' - '.$request->get('kode_barang').']',
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

    public function viewjasa($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listjasa')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['jasa'] = Barang::where('id', $request->id)->first();
            if($getdata['jasa']){ 
                $count_used = ListPenjualan::where('kode_barang', $getdata['jasa']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editjasa($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listjasa')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editjasa')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Barang::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'kode_barang' => 'required|string|max:200',
                    'nama' => 'required|string|max:200',
                    'satuan' => 'required|string|max:200',
                    'harga_beli' => 'required|string|max:200',
                    'harga_jual' => 'required|string|max:200',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->nama != $getdata->nama){
                    $validator = Validator::make($request->all(),['nama'=>'required|string|max:200|unique:db_barang']);
                    if($validator->fails()){
                        return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);
                    }
                }

                if($request->harga_jual == '0'){
                    $margin_jual1 = '0';
                }else{
                    $margin_jual1 = ($request->harga_jual - $request->harga_beli) / $request->harga_beli * 100;                
                }
                
                $updatedata = Barang::where('id', $request->get('id'))
                    ->update([
                        'kode' => $request->get('kode_barang'),
                        'nama' => ucfirst($request->get('nama')),
                        'kode_satuan' => $request->get('satuan'),
                        'kode_satuan_default' => $request->get('satuan'),
                        'harga_beli' => $request->get('harga_beli'),
                        'margin_jual1' => $margin_jual1,
                        'harga_jual1' => $request->get('harga_jual'),
                        'margin_jual2' => '0',
                        'harga_jual2' => '0',
                        'margin_jual3' => '0',
                        'harga_jual3' => '0',
                        'margin_jual4' => '0',
                        'harga_jual4' => '0',
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
                        'activity' => 'Update data barang jasa ['.$getdata->nama.' - '.$request->get('kode_barang').']',
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

    public function deletejasa($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','deletebarang')->first();
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
                $getdata = Barang::where('id', $request->id)->first();
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
                            'activity' => 'Hapus data jasa ['.$getdata->nama.' - '.$getdata->kode.']',
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