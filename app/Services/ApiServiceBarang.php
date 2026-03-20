<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Barang, HistoryStock, ListPembelian, Satuan, Kategori, Merk, Supplier};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceBarang
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

    public function listbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','exportbarang')->first();
            if($request->type == 'export'){
                if($level_action->access_rights == 'No'){
                    return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
                }
            }

            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Barang::where('type_produk','LIKE','Barang')
                    ->where(function($query) use ($request) {
                    $query->Where('kode','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('nama', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                // $results['count_used'][$data->id] = HistoryStock::where('kode_barang', $data->id)->count();
                $results['count_used'][$data->id] = ListPembelian::where('kode_barang', $data->id)->count();
                $results['satuan'][$data->id] = Satuan::where('id', $data->kode_satuan)->first();
                $results['kategori'][$data->id] = Kategori::where('id', $data->kode_jenis)->first();
                $results['merk'][$data->id] = Merk::where('id', $data->kode_brand)->first();
                $results['supplier'][$data->id] = Supplier::where('id', $data->kode_supplier)->first();
            
                // Ambil pembelian tertinggi dengan satu query
                $pembelianTertinggi = ListPembelian::where('kode_barang', $data->id)
                    ->orderBy('harga_netto', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_beli'][$data->id] = $pembelianTertinggi->harga_netto ?? 0;
                $results['tanggal_beli'][$data->id] = $pembelianTertinggi->tanggal ?? 'Belum ditentukan';
            
                // Ambil pembelian terakhir dengan satu query
                $pembelianTerakhir = ListPembelian::where('kode_barang', $data->id)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_beli_terakhir'][$data->id] = $pembelianTerakhir->harga_netto ?? 0;
                $results['tanggal_beli_terakhir'][$data->id] = $pembelianTerakhir->tanggal ?? 'Belum ditentukan';
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }
                
    public function newbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $validator = Validator::make($request->all(), [
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200|unique:db_barang',
                'satuan' => 'required|string|max:200',
                'kategori' => 'required|string|max:200',
                'merk' => 'required|string|max:200',
                'supplier' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
                'harga_khusus' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            if($request->harga_jual == '0'){
                $margin_jual1 = '0';
            }else{
                $margin_jual1 = ($request->harga_jual - $request->harga_beli) / $request->harga_beli * 100;                
            }

            if($request->harga_khusus == '0'){
                $margin_jual2 = '0';
            }else{
                $margin_jual2 = ($request->harga_khusus - $request->harga_beli) / $request->harga_beli * 100;                
            }
            
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

            $savedata = Barang::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'kode' => $request->get('kode_barang'),
                'nama' => ucfirst($request->get('nama')),
                'kode_satuan' => $request->get('satuan'),
                'kode_jenis' => $request->get('kategori'),
                'kode_brand' => $request->get('merk'),
                'kode_supplier' => $request->get('supplier'),
                'kode_satuan_default' => $request->get('satuan'),
                'type_produk' => 'Barang',
                'harga_beli' => $request->get('harga_beli'),
                'margin_jual1' => $margin_jual1,
                'harga_jual1' => $request->get('harga_jual'),
                'margin_jual2' => $margin_jual2,
                'harga_jual2' => $request->get('harga_khusus'),
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
                    'activity' => 'Tambah data barang ['.$request->nama.' - '.$request->get('kode_barang').']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data',]);
        } 

    }

    public function viewbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','masterdata')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata['barang'] = Barang::where('id', $request->id)->first();
            if($getdata['barang']){ 
                $count_used = HistoryStock::where('kode_barang', $getdata['barang']->id)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function editbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','editbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $getdata = Barang::where('id', $request->id)->first();
            if($getdata){
                $validator = Validator::make($request->all(), [
                    'kode_barang' => 'required|string|max:200',
                    'nama' => 'required|string|max:200',
                    'satuan' => 'required|string|max:200',
                    'kategori' => 'required|string|max:200',
                    'merk' => 'required|string|max:200',
                    'supplier' => 'required|string|max:200',
                    'harga_beli' => 'required|string|max:200',
                    'harga_jual' => 'required|string|max:200',
                    'harga_khusus' => 'required|string|max:200',
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
    
                if($request->harga_khusus == '0'){
                    $margin_jual2 = '0';
                }else{
                    $margin_jual2 = ($request->harga_khusus - $request->harga_beli) / $request->harga_beli * 100;                
                }
                
                $updatedata = Barang::where('id', $request->get('id'))
                    ->update([
                        'kode' => $request->get('kode_barang'),
                        'nama' => ucfirst($request->get('nama')),
                        'kode_satuan' => $request->get('satuan'),
                        'kode_jenis' => $request->get('kategori'),
                        'kode_brand' => $request->get('merk'),
                        'kode_supplier' => $request->get('supplier'),
                        'kode_satuan_default' => $request->get('satuan'),
                        'harga_beli' => $request->get('harga_beli'),
                        'margin_jual1' => $margin_jual1,
                        'harga_jual1' => $request->get('harga_jual'),
                        'margin_jual2' => $margin_jual2,
                        'harga_jual2' => $request->get('harga_khusus'),
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
                        'activity' => 'Update data barang ['.$getdata->nama.' - '.$getdata->code_data.']',
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

    public function deletebarang($request)
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
                            'activity' => 'Hapus data barang ['.$getdata->nama.' - '.$getdata->code_data.']',
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