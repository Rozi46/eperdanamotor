<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ListPenjualanMekanik, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceCashier
{     
    public function generateCode($length = 4, $type = 'letters') {
        switch ($type) {
            case 'letters': // huruf saja A-Z
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);

            case 'numbers': // angka saja 0-9
                return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

            case 'mixed': // huruf + angka
                return strtoupper(Str::random($length));

            default:
                throw new InvalidArgumentException("Type harus 'letters', 'numbers', atau 'mixed'");
        }
    }

    public function getlevelaksesCashier($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results['menu'] = ListAkses::where('menu', 'Yes')->orderBy('no_urut', 'DESC')->get();
            foreach($results['menu'] as $key => $menu){
                $results['count_used'][$menu->id] = ListAkses::where('menu_index',$menu->id)->count();
                $results['submenu'][$menu->id] = ListAkses::where('menu_index', $menu->id)
                    ->where('submenu', 'Yes')
                    ->orderBy('no_urut', 'ASC')
                    ->get();

                foreach($results['submenu'][$menu->id] as $key => $submenu){
                    $results['action'][$submenu->id] = ListAkses::where('menu_index', $submenu->id)->where('action', 'Yes')->orderBy('no_urut', 'ASC')->get();

                    foreach($results['action'][$submenu->id] as $key => $action){
                        $results['subaction'][$action->id] = ListAkses::where('menu_index', $action->id)->where('subaction', 'Yes')->orderBy('no_urut', 'ASC')->get();
                    }
                }
            }
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function getadminCashier($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $object['data_company'] = Kantor::select('kantor','jenis','alamat','ket','foto')->where('id', $viewadmin->kode_kantor)->first();

            $resultsdata['detailadmin'] = array();
            array_push($resultsdata['detailadmin'], $viewadmin);

            $leveladmin = LevelAdmin::where('code_data','=',$viewadmin->level)->get();
            $resultsdata['leveladmin'] = array();
            array_push($resultsdata['leveladmin'], $leveladmin);
            array_push($object, $resultsdata);

            return response()->json(['status_message' => 'success','results' => $object],200);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }  

    public function getSettingCashier($request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $object['data_setting'] = Setting::where('id','1')->first();


            return response()->json(['status_message' => 'success','results' => $object],200);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    } 

    public function loginCashier($request)
    {
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = [];
        $email = $request->email;
        $password = $request->password;

        $validator = Validator::make($request->all(), [
            'email' => 'required|min:1|max:200',
            'password' => 'required|min:1|max:200',
        ]);

        if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

        $credentials = $request->only('email', 'password');
        $getdata = User::where('email',$email)->where('level','LV7622003')->first();
        $getstatusdata = User::where('email',$email)->where('status_data','Aktif')->where('level','LV7622003')->first();

        if($getdata){
            if($getstatusdata){
                if(Hash::check($password,$getdata->password)){
                    $resultsdata['detailadmin'] = array();
                    array_push($resultsdata['detailadmin'], $getdata);
                    $leveladmin = LevelAdmin::where('code_data','=',$getdata->level)->get();
                    $resultsdata['leveladmin'] = array();
                    array_push($resultsdata['leveladmin'], $leveladmin);
                    array_push($object, $resultsdata);

                    // if($request->url() == $url_app.'/conflogin'){
                    //     $link_akses = 'Online';
                    // }else{
                    //     $link_akses = 'Offline';
                    // }

                    $link_akses = app()->environment('production') ? 'Online' : 'Offline';

                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData = $time."".$otp;
                    $newCodeData = ltrim($newCodeData, '0');
                
                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $getdata->id,
                        'activity' => 'Masuk ke sistem',
                        'kode_kantor' => $getdata->kode_kantor,
                    ]);

                    $token = Str::uuid();
                    User::where('code_data', $getdata->code_data)
                        ->update([
                            'key_token' => $token,
                    ]);

                    return response()->json([
                        'status_message' => 'success',
                        'note' => 'Berhasil masuk ke sistem',
                        'key_token' => $token,
                        'results' => $object
                    ],200);

                }else{
                    return response()->json([
                        'status_message' => 'error',
                        'note' => 'Kata sandi salah',
                        'results' => $object
                    ]);
                }
            }else{
                return response()->json([
                    'status_message' => 'error',
                    'note' => 'Data pengguna tidak aktif',
                    'results' => $object
                ]);
            }
        }else{
            return response()->json([
                'status_message' => 'error',
                'note' => 'Data tidak terdaftar',
                'results' => $object
            ]);
        }
    }

    public function logoutCashier($request)
    {
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin && $request->token != null){

            // if($request->url() == $url_app.'/logout'){
            //     $link_akses = 'Online';
            // }else{
            //     $link_akses = 'Offline';
            // }

            $link_akses = app()->environment('production') ? 'Online' : 'Offline';

            $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
            $time = Carbon::now()->format('Ymdhis');
            $newCodeData = $time."".$otp;
            $newCodeData = ltrim($newCodeData, '0');

                Activity::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'kode_user' => $viewadmin->id,
                'activity' => 'Keluar dari sistem',
                'kode_kantor' => $viewadmin->kode_kantor,
            ]);

            User::where('id', $viewadmin->id)
                ->update([
                    'key_token' => null,
            ]);
    
            Session::flush();
    
            return response()->json( [
                'status_message' => 'success',
                'note' => 'Berhasil keluar ke sistem',
                'code_data' => $viewadmin->code_data,
            ]);
        }else{
            return response()->json( [
                'status_message' => 'error',
                'note' => 'Terjadi kesalahan saat keluar ke sistem',
                'code_data' => null,
            ]);
        }                
    }

    public function listopgudangCashier($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Gudang::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }
    
    public function listopmekanikCashier($request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }else{
            $results = Karyawan::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function editadminCashier($request)
    {
        $object = [];
        
        $viewadmin = User::where('id', $request->id)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            if($request->nama != $viewadmin->nama){
                $validator = Validator::make($request->all(),['full_name'=>'required|min:1|max:30']);
                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);}
            }
            if($request->no_hp != $viewadmin->no_hp){
                $validator = Validator::make($request->all(),['phone_number'=>'required|min:1|max:30']);
                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);}
            }
            if($request->email != $viewadmin->email){
                $validator = Validator::make($request->all(),['email'=>'required|min:1|max:30|unique:db_users_web']);
                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);}
            }

            $update = User::where('id', $viewadmin->id)
                ->update([
                    'full_name' => $request->get('full_name'),
                    'phone_number' => $request->get('phone_number'),
                    'email' => $request->get('email'),
            ]);
            
            if($update){
                if($request->image_admin != ''){
                    $request->validate(['image_admin' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2548']);

                    $imageName = 'PP-'.$request->id.'-'.time().'.'.$request->image_admin->extension();

                    $request->image_admin->move(public_path('/themes/admin/AdminOne/image/upload/'), $imageName); 

                    User::where('id', $viewadmin->id)
                    ->update([
                        'image' => $imageName,
                    ]);

                    File::delete(public_path('/themes/admin/AdminOne/image/upload/'.$viewadmin->image.''));
                    $file = $request->file('image_admin');
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $file->getClientOriginalName()]);
                }else{
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }
    }

    public function editpassadminCashier($request)
    {
        $object = [];
        $old_password = $request->old_password;
        $password = $request->password;

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|max:30',
            'new_password' => 'required|string|max:30',
        ]);

        if($validator->fails()){ return response()->json(['status_message' => 'error','note' => $validator->errors(),'results' => $object]);}

        $viewadmin = User::where('id', $request->id)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            if(Hash::check($request->old_password,$viewadmin->password)){
                $new_password = bcrypt($request->new_password);

                $update = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $viewadmin->id)
                    ->update([
                        'password' => $new_password,
                ]);

                if($update){
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Kata sandi salah','results' => $object]);
            }
        }
    } 

    // Auto Complete
    public function listopcustomer($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Customer::select('id','code_data','nama','no_telp','alamat')
            ->where('nama','LIKE', '%'.$request->term.'%')
            ->Orwhere('no_telp','LIKE', '%'.$request->term.'%')
            ->Orwhere('alamat','LIKE', '%'.$request->term.'%')->limit(6)
            ->orderBy('nama', 'ASC')->get();
            
            $getsupplier = array();

            foreach($results as $key => $list){
                $getsupplier[] = array(
                    'label' => $list->nama.' - '.$list->alamat,
                    'nama' => $list->nama,
                    'code_data' => $list->id,
                    'no_telp' => $list->no_telp,
                    'alamat' => $list->alamat,
                );
            }
                
            return response()->json($getsupplier);
        }
    }
    
    public function listbarangtransaksi($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{  
            $results = Barang::select('id','code_data','kode','nama')
            ->where('code_data','LIKE', '%'.$request->term.'%')
            ->Orwhere('kode','LIKE', '%'.$request->term.'%')
            ->Orwhere('nama','LIKE', '%'.$request->term.'%')->limit(6)
            ->orderBy('nama', 'ASC')->get();
            
            $getprod = array();

            foreach($results as $key => $list){
                $getprod[] = array(
                    'label' => $list->nama.' - '.$list->kode,
                    'nama' => $list->nama,
                    'code_data' => $list->id,
                    'kode' => $list->kode,
                );
            }
                
            return response()->json($getprod);
        }
    }

    // Penjualan Barang
    public function getcodepenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();
            $dataAll = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();
            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PJ-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data',]);
        }

    }

    public function saveprodpenjualan($request)
    {        
        date_default_timezone_set('Asia/Jakarta');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            if($request->get('code_data') == ''){
                $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
                $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
                $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();
                $dataAll = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
                $countData = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();
                if($countData <> 0){
                    $newNomorPenjualan = substr($dataAll->nomor,-7);
                    $newNomorPenjualan = $newNomorPenjualan + 1;
                }else{
                    $newNomorPenjualan = 1;
                }
    
                $kantor = $viewadmin->kode_kantor;
                $newNomorPenjualan = str_pad($newNomorPenjualan, 7, "0", STR_PAD_LEFT);
                $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
                $newNomorPenjualan = "PJ-".$datenow.'.'.$newNomorPenjualan;                
                $code_transaksi = $newNomorPenjualan;
                $code_data = $newNomorPenjualan;
            }else{
                $code_data = $request->get('code_data');
                $code_transaksi = $request->get('code_transaksi');
            }
            
            $code_produk = $request->get('code_produk');


            $counttransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count();         
            $countprod = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang', $code_produk)->count();

            $validator = Validator::make($request->all(), [
                // 'code_transaksi' => 'required|string|max:200',
                'tgl_transaksi' => 'required|string|max:200',
                'code_gudang' => 'required|string|max:200',
                'code_customer' => 'required|string|max:200',
                'code_produk' => 'required|string|max:200',
            ]);

            if($validator->fails()){
                return response()->json([
                    'status_message' => 'failed',
                    'note' => $validator->errors()
                ]);
            }

            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');     
            
            $getdata['barang'] = Barang::where('id', $request->code_produk)->first();
            $getdata['customer'] = Customer::where('id', $request->code_customer)->first();

            $getdata['penjualan'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();
            if($getdata['penjualan']){                
                $newCodeData = $getdata['penjualan']->code_data; 
            }else{
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData = $time."".$otp;
                $newCodeData = ltrim($newCodeData, '0'); 
            }

            if($request->type_harga == 'Harga Normal'){
                $harga_barang = $getdata['barang']->harga_jual1;
            }else{
                $harga_barang = $getdata['barang']->harga_jual2;
            }

            if($tgl_transaksi < '2022-04-01'){
                $get_setting_nilai_pajak = 10;
            }else{
                $get_setting_nilai_pajak = 11;
            }

            $jenis_ppn = 'Include';
            $status_ppn = 'Ya';
            $nilai_ppn = 0;

            if($counttransaksi == 0){            
                $savedata = ListPenjualan::create([
                    'id' => Str::uuid(),                    
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'kode_barang' => $getdata['barang']->id,
                    'jumlah_jual' => $request->get('qty'),
                    'jumlah_kirim' => 0,
                    'jumlah_retur' => 0,
                    'kode_satuan' => $getdata['barang']->kode_satuan,
                    'harga' => $harga_barang,
                    'diskon_persen' => 0,
                    'diskon_harga' => 0,
                    'diskon_persen2' => 0,
                    'diskon_harga2' => 0,
                    'harga_netto' => $harga_barang,
                    'total_harga' => $harga_barang,
                    'status_ppn' => $status_ppn,
                    'ppn' => $nilai_ppn,
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]); 

                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    }                        
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                }   

                // if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                // }else{
                //     $grand_total = $total - $diskon_harga + $biaya_lain; 
                //     $sub_total = $grand_total;
                //     $sub_total = Round($sub_total);

                //     $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                //     $nilai_ppn = Round($nilai_ppn);

                //     $grand_total = $sub_total + $nilai_ppn; 
                // }                                  
                                    
                $ket = $request->get('keterangan');
                if ($ket == ''){
                    $ket = "Jual pada ".$getdata['customer']->nama;
                }

                Penjualan::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'kode_customer' => $request->get('code_customer'),
                    'ket' => $ket,
                    'jenis_penjualan' => $request->jenis_penjualan,
                    'sub_total' => $sub_total,
                    'ppn' => $nilai_ppn,
                    'total' => $total,
                    'diskon_persen' => $diskon_persen,
                    'diskon_harga' => $diskon_harga,
                    'biaya_kirim' => $biaya_lain,
                    'grand_total' => $grand_total,
                    'status_transaksi' => 'Proses',
                    'kode_gudang' => $request->get('code_gudang'),
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,                        
                ]);

                $mekanikLama = ListPenjualanMekanik::where('nomor', $code_transaksi)->where('kode_kantor', $viewadmin->kode_kantor)->pluck('code_mekanik')->toArray();
                $mekanikBaru = $request->get('code_mekanik', []);
                $hapusMekanik = array_diff($mekanikLama, $mekanikBaru);
                if (!empty($hapusMekanik)) {
                    ListPenjualanMekanik::where('nomor', $code_transaksi)->where('kode_kantor', $viewadmin->kode_kantor)->whereIn('code_mekanik', $hapusMekanik)->delete();
                }

                foreach ($mekanikBaru as $mekanikId){
                    ListPenjualanMekanik::Create([
                        'id'        => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor'        => $code_transaksi,
                        'code_mekanik' => $mekanikId,
                        'tanggal'   => $tgl_transaksi,
                        'kode_kantor'  => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);
                }
                
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Penjualan barang ['.$code_transaksi.']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);

            }else{
                if($countprod == 0){              
                    $savedata = ListPenjualan::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'kode_barang' => $getdata['barang']->id,
                        'jumlah_jual' => $request->get('qty'),
                        'jumlah_kirim' => 0,
                        'jumlah_retur' => 0,
                        'kode_satuan' => $getdata['barang']->kode_satuan,
                        'harga' => $harga_barang,
                        'total_harga' => $harga_barang,
                        'diskon_persen' => 0,
                        'diskon_harga' => 0,
                        'diskon_persen2' => 0,
                        'diskon_harga2' => 0,
                        'harga_netto' => $harga_barang,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                        'kode_cabang' => $request->get('code_cabang'),
                    ]);
                }else{
                    $countprodgift = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga',0)->where('kode_barang', $code_produk)->count();
                    if($countprodgift > 0){
                        $countprodall = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)->count();
                        if($countprodall == 0){        
                            $savedata = ListPenjualan::create([
                                'id' => Str::uuid(),
                                'code_data' => $newCodeData,
                                'nomor' => $code_transaksi,
                                'tanggal' => $tgl_transaksi,
                                'kode_barang' => $getdata['barang']->id,
                                'jumlah_jual' => $request->get('qty'),
                                'jumlah_kirim' => 0,
                                'jumlah_retur' => 0,
                                'kode_satuan' => $getdata['barang']->kode_satuan,
                                'harga' => $harga_barang,
                                'total_harga' => $harga_barang,
                                'diskon_persen' => 0,
                                'diskon_harga' => 0,
                                'diskon_persen2' => 0,
                                'diskon_harga2' => 0,
                                'harga_netto' => $harga_barang,
                                'status_ppn' => $status_ppn,
                                'ppn' => $nilai_ppn,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);
                        }else{
                            $getprodtransaksi = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)->first();

                            $kode_satuan = $getprodtransaksi->kode_satuan;  
                            $harga_jual = $getprodtransaksi->harga;                  
                            $qty_jual = $getprodtransaksi->jumlah_jual + $request->get('qty');            
            
                            $diskon_persen = $getprodtransaksi->diskon_persen;
                            $diskon_harga = $harga_jual * ($diskon_persen/100);
            
                            $diskon_persen2 = $getprodtransaksi->diskon_persen2;
                            $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);
            
                            $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                            $total_harga = $qty_jual * $harga_netto;
            
                            $status_ppn = 'Ya'; 
                            $nilai_ppn = 0;
                            
                            $savedata = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)
                                ->update([
                                    'kode_satuan' => $kode_satuan,
                                    'harga' => $harga_jual,
                                    'jumlah_jual' => $qty_jual,
                                    'diskon_persen' => $diskon_persen,
                                    'diskon_harga' => $diskon_harga,
                                    'diskon_persen2' => $diskon_persen2,
                                    'diskon_harga2' => $diskon_harga2,
                                    'harga_netto' => $harga_netto,
                                    'total_harga' => $total_harga,
                                    'status_ppn' => $status_ppn,
                                    'ppn' => $nilai_ppn,
                            ]);
                        }
                    }else{
                        $getprodtransaksi = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang', $code_produk)->first();

                        $kode_satuan = $getprodtransaksi->kode_satuan;  
                        $harga_jual = $getprodtransaksi->harga;                  
                        $qty_jual = $getprodtransaksi->jumlah_jual + $request->get('qty');            
        
                        $diskon_persen = $getprodtransaksi->diskon_persen;
                        $diskon_harga = $harga_jual * ($diskon_persen/100);
        
                        $diskon_persen2 = $getprodtransaksi->diskon_persen2;
                        $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);
        
                        $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                        $total_harga = $qty_jual * $harga_netto;
        
                        $status_ppn = 'Ya'; 
                        $nilai_ppn = 0;
                        
                        $savedata = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang',$getdata['barang']->id)
                            ->update([
                                'kode_satuan' => $kode_satuan,
                                'harga' => $harga_jual,
                                'jumlah_jual' => $qty_jual,
                                'diskon_persen' => $diskon_persen,
                                'diskon_harga' => $diskon_harga,
                                'diskon_persen2' => $diskon_persen2,
                                'diskon_harga2' => $diskon_harga2,
                                'harga_netto' => $harga_netto,
                                'total_harga' => $total_harga,
                                'status_ppn' => $status_ppn,
                                'ppn' => $nilai_ppn,
                        ]);
                    }
                }
            }

            if($savedata){
                $counttransaksi = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count();
                    $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                    $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                    if($gettransaksi){
                        $diskon_persen = $gettransaksi->diskon_persen;
                        if($diskon_persen == 0){
                            $diskon_harga = 0;
                        }else{
                            $diskon_harga = ($diskon_persen/100) * $total;
                        } 
                        $biaya_lain = $gettransaksi->biaya_kirim;
                    }else{
                        $diskon_persen = 0;
                        $diskon_harga = 0;
                        $biaya_lain = 0;
                    }      


                    // if($jenis_ppn == 'Include'){
                        $grand_total = $total - $diskon_harga + $biaya_lain; 
                        $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                        $sub_total = Round($sub_total);

                        $nilai_ppn = $grand_total - $sub_total;
                        $nilai_ppn = Round($nilai_ppn);
                    // }else{
                    //     $grand_total = $total - $diskon_harga + $biaya_lain; 
                    //     $sub_total = $grand_total;
                    //     $sub_total = Round($sub_total);

                    //     $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    //     $nilai_ppn = Round($nilai_ppn);

                    //     $grand_total = $sub_total + $nilai_ppn; 
                    // }    
    
                    Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$code_transaksi)
                        ->update([
                            'jenis_penjualan' => $request->jenis_penjualan,
                            'sub_total' => $sub_total,
                            'ppn' => $nilai_ppn,
                            'total' => $total,
                            'diskon_persen' => $diskon_persen,
                            'diskon_harga' => $diskon_harga,
                            'biaya_kirim' => $biaya_lain,
                            'grand_total' => $grand_total,
                    ]);
                // }
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $code_transaksi]);
            }else{
                return response()->json(['status_message' => 'failed','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json( [
                'status_message' => 'failed',
                'note' => 'Terjadi kesalahan saat proses data',
            ]);
        } 

    }

    public function viewpenjualan($request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();
            if($getdata['detail']){ 
                
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                $getdata['detail_gudang'] = Gudang::where('id', $getdata['detail']->kode_gudang)->first();

                if($getdata['detail']->kode_customer != 'null'){
                    $getdata['detail_customer'] = Customer::where('id', $getdata['detail']->kode_customer)->first();
                }

                if($getdata['detail']->tipe_diskon == 'Persen'){
                    $total_diskon = $getdata['detail']->sub_total - ($getdata['detail']->sub_total * $getdata['detail']->diskon_faktur/100);
                }elseif($getdata['detail']->tipe_diskon == 'Jumlah'){
                    $total_diskon = $getdata['detail']->sub_total - $getdata['detail']->diskon_faktur;
                }else{
                    $total_diskon = $getdata['detail']->sub_total;
                }
                $biaya_lain = $getdata['detail']->biaya_lain;
                $grand_total_bef_tax = $total_diskon + $biaya_lain;
                
                $pajak = ($grand_total_bef_tax * $getdata['detail']->pajak/100);

                $qty_penjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->sum('jumlah_jual');

                $qty_kirim = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->sum('jumlah_kirim');

                $getdata['counttransaksi'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->count();

                $getdata['list_mekanik'] = ListPenjualanMekanik::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail']->nomor)->orderBy('created_at', 'ASC')->get();
        
                foreach($getdata['list_mekanik'] as $key => $listMekanik){
                    $getdata['detail_mekanik'][$listMekanik->code_mekanik] = Karyawan::where('code_data', $listMekanik->code_mekanik)->first();                   
                }
                
                $getdata['list_produk'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail']->nomor)->orderBy('created_at', 'ASC')->get(); 
        
                foreach($getdata['list_produk'] as $key => $list){
                    $getdata['qty_penjualan'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_jual');

                    $getdata['qty_kirim'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_kirim');

                    $getdata['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    
                    $getdata['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                    $getdata['satuan_produk'][$list->kode_barang] = Satuan::where('id', $getdata['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                    
                    $getdata['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $getdata['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get();
                    
                }
                    
                $get_list_penjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->first(); 
                if($get_list_penjualan){
                    $get_barang = Barang::where('id', $get_list_penjualan->kode_barang)->first();
                    $harga_barang = $get_list_penjualan->harga;
                    $harga_jual1 = $get_barang->harga_jual1;
                    $harga_jual2 = $get_barang->harga_jual2;
                    if($harga_barang == $harga_jual1){
                        $getdata['type_harga'] = 'Harga Normal';
                    }else{
                        $getdata['type_harga'] = 'Harga Khusus';
                    }
                }

                $qty_penjualan = ListPenjualan::sum('jumlah_jual');

                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function listsatuanhargapenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();
            if($getdata_listpenjualan){                 
                $getdatasatuan = Satuan::Where('id',$request->harga_satuan)->first();  
                if($getdata_listpenjualan->kode_satuan == $request->harga_satuan){
                    $harga_jual = $getdata_listpenjualan->harga;
                }elseif($getdatasatuan->isi == 1){ 
                    $getdatasatuan1 = Satuan::Where('id',$getdata_listpenjualan->kode_satuan)->first();
                    $harga_jual = $getdata_listpenjualan->harga / $getdatasatuan1->isi;
                }else{
                    $getdatasatuan1 = Satuan::Where('id',$getdata_listpenjualan->kode_satuan)->first();
                    $harga_jual = $getdatasatuan->isi * $getdata_listpenjualan->harga;
                }   
                
                $qty_jual = $getdata_listpenjualan->jumlah_jual;                    

                $diskon_persen = $getdata_listpenjualan->diskon_persen;
                $diskon_harga = $harga_jual * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpenjualan->diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_jual * $harga_netto;

                $tgl_transaksi = $getdata_listpenjualan->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_penjualan->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpenjualan->nomor;
                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $request->harga_satuan,
                        'harga' => $harga_jual,
                        'jumlah_jual' => $qty_jual,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    } 
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                }   

                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn); 

                Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
                    ->update([                        
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function uphargapenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();
            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan; 

                $harga_jual = $request->harga;
                // Ganti tanda koma dengan titik
                $harga_jual = str_replace(',', '.', $harga_jual);        
                // Konversi ke tipe numerik (float)
                $harga_jual = floatval($harga_jual);                  
                
                $qty_jual = $getdata_listpenjualan->jumlah_jual;            

                $diskon_persen = $getdata_listpenjualan->diskon_persen;
                $diskon_harga = $harga_jual * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpenjualan->diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_jual * $harga_netto;

                $tgl_transaksi = $getdata_listpenjualan->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_penjualan->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpenjualan->nomor;
                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_jual,
                        'jumlah_jual' => $qty_jual,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    } 
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                }  
                
                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn);

                Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
                    ->update([                        
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function upqtypenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();

            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan;  
                $harga_jual = $getdata_listpenjualan->harga;                  
                $qty_jual = $request->qty;            

                $diskon_persen = $getdata_listpenjualan->diskon_persen;
                $diskon_harga = $harga_jual * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpenjualan->diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                
                $numeric_qty_jual = str_replace(',', '.', $qty_jual);
                $numeric_qty_jual = (float) $numeric_qty_jual;

                $total_harga = $numeric_qty_jual * $harga_netto;

                $tgl_transaksi = $getdata_listpenjualan->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_penjualan->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpenjualan->nomor;
                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_jual,
                        'jumlah_jual' => $numeric_qty_jual,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    } 
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                } 

                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn);

                Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
                    ->update([                        
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function updiscpenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();

            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan;  
                $harga_jual = $getdata_listpenjualan->harga;                  
                $qty_jual = $getdata_listpenjualan->jumlah_jual;             

                $diskon_persen = $request->nilai_diskon;
                $diskon_harga = $harga_jual * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpenjualan->diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_jual * $harga_netto;

                $tgl_transaksi = $getdata_listpenjualan->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_penjualan->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpenjualan->nomor;
                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_jual,
                        'jumlah_jual' => $qty_jual,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    } 
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                } 

                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn);

                Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
                    ->update([                        
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function updiscpenjualan2($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();

            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan;  
                $harga_jual = $getdata_listpenjualan->harga;                  
                $qty_jual = $getdata_listpenjualan->jumlah_jual;             

                $diskon_persen = $getdata_listpenjualan->diskon_persen;
                $diskon_harga = $harga_jual * ($diskon_persen/100);

                $diskon_persen2 = $request->nilai_diskon2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_jual - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_jual * $harga_netto;

                $tgl_transaksi = $getdata_listpenjualan->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_penjualan->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpenjualan->nomor;
                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_jual,
                        'jumlah_jual' => $qty_jual,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    if($diskon_persen == 0){
                        $diskon_harga = 0;
                    }else{
                        $diskon_harga = ($diskon_persen/100) * $total;
                    } 
                    $biaya_lain = $gettransaksi->biaya_kirim;
                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $biaya_lain = 0;
                }   

                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn);

                Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
                    ->update([                        
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function upsummarypenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->first();
            if($getdata){ 
                $sub_total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');

                $diskon_faktur = str_replace(".","",$request->get('nilai_diskon'));
                $diskon_faktur = str_replace(",",".",$diskon_faktur);

                $total = $sub_total;
                $jenis_ppn = $getdata->jenis_ppn;
                $biaya_lain = $getdata->biaya_kirim;

                $tipe_diskon = $request->tipe_diskon;

                if( $tipe_diskon == 'Persen'){
                    // if($diskon_faktur == 0){
                    //     $tipe_diskon = 'Tidak Aktif';
                    // }  
                    $diskon_harga = $total * ($diskon_faktur/100);
                    $diskon_harga = Round($diskon_harga);
                    $diskon_persen = $request->get('nilai_diskon');
                    $diskon_persen = str_replace(",",".",$diskon_persen);
                    $total_diskon = $total - ($total * ($diskon_faktur/100));
                }elseif( $tipe_diskon == 'Jumlah'){
                    // if($diskon_faktur == 0){
                    //     $tipe_diskon = 'Tidak Aktif';
                    // }
                    $diskon_harga = $request->get('nilai_diskon');
                    $diskon_persen = ($request->get('nilai_diskon')/$total) * 100;
                    $total_diskon = $total - $diskon_faktur;
                }else{
                    $diskon_harga = 0;
                    $diskon_persen = 0;
                    $total_diskon = 0;
                }
 

                $tgl_transaksi = $getdata->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                } 

                $grand_total = $total - $diskon_harga + $biaya_lain; 
                $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                $sub_total = Round($sub_total);

                $nilai_ppn = $grand_total - $sub_total;
                $nilai_ppn = Round($nilai_ppn);                

                $savedata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)
                    ->update([
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'biaya_kirim' => $biaya_lain,
                        'grand_total' => $grand_total,
                ]);
                if($savedata){
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function deleteprodpenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $getdata = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->where('id', $request->id)->first();
            if($getdata){
                $getdatatransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();
                $DelData = $getdata->delete();
                if($DelData){
                    $total = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');
                    $gettransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first(); 
                    
                    $jenis_ppn = $getdatatransaksi->jenis_ppn;

                    if($gettransaksi){
                        $diskon_persen = $gettransaksi->diskon_persen;
                        if($diskon_persen == 0){
                            $diskon_harga = 0;
                        }else{
                            $diskon_harga = ($diskon_persen/100) * $total;
                        } 
                        $biaya_lain = $gettransaksi->biaya_kirim;
                    }else{
                        $diskon_persen = 0;
                        $diskon_harga = 0;
                        $biaya_lain = 0;
                    }   

                    $tgl_transaksi = $getdatatransaksi->tanggal;
                    $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');
    
                    if($tgl_transaksi < '2022-04-01'){
                        $get_setting_nilai_pajak = 10;
                    }else{
                        $get_setting_nilai_pajak = 11;
                    }

                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
    
                    Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdatatransaksi->nomor)
                        ->update([
                            'sub_total' => $sub_total,
                            'ppn' => $nilai_ppn,
                            'total' => $total,
                            'diskon_persen' => $diskon_persen,
                            'diskon_harga' => $diskon_harga,
                            'biaya_kirim' => $biaya_lain,
                            'grand_total' => $grand_total,
                    ]);

                    $count_produk = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->count();
                    if($count_produk == 0){
                        Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdatatransaksi->nomor)
                            ->update([
                                'sub_total' => 0,
                                'ppn' => 0,
                                'total' => 0,
                                'diskon_persen' => 0,
                                'diskon_harga' => 0,
                                'biaya_kirim' => 0,
                                'grand_total' => 0,
                        ]);
                    }
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function deletepenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if(!$getdata){
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }else{
                try {
                    DB::beginTransaction();
                    ListPenjualan::where('kode_kantor', $viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->delete();
                    ListPenjualanMekanik::where('kode_kantor', $viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->delete();

                    $Deldata = $getdata->delete();
                    if (!$Deldata) {
                        throw new \Exception('Gagal menghapus data penjualan utama');
                    }  
                    
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData = $time."".$otp;
                    $newCodeData = ltrim($newCodeData, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Membatalkan penjualan barang  ['.$getdata->nomor.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    DB::commit();
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);   
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan: ' . $e->getMessage(),'results' => $object]);
                }
            }
        }
    }

    public function updatepenjualan($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){            
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();
            $getdata['counttransaksi'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->count();

            if($getdata){  
                $validator = Validator::make($request->all(), [
                    'customer' => 'required|string|max:200',
                ]);
    
                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}
                $savedata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)
                    ->update([
                        'kode_customer' => $request->get('customer'),
                        'ket' => $request->get('keterangan'),
                        'jenis_penjualan' => $request->jenis_penjualan,
                        'status_transaksi' => 'Finish',
                    ]);

                $mekanikLama = ListPenjualanMekanik::where('nomor', $getdata->nomor)->where('kode_kantor', $viewadmin->kode_kantor)->pluck('code_mekanik')->toArray();
                $mekanikBaru = $request->get('code_mekanik');
                if (is_array($mekanikBaru)) {
                    $mekanikBaru = $mekanikBaru;
                } else {
                    $mekanikBaru = explode(",", $mekanikBaru);
                }  

                $hapusMekanik = array_diff($mekanikLama, $mekanikBaru);

                if (!empty($hapusMekanik)) {
                    ListPenjualanMekanik::where('nomor', $getdata->nomor)->where('kode_kantor', $viewadmin->kode_kantor)->whereIn('code_mekanik', $hapusMekanik)->delete();
                }

                foreach ($mekanikBaru as $mekanikId) {
                    ListPenjualanMekanik::updateOrCreate(
                        [
                            'nomor'        => $getdata->nomor,
                            'code_mekanik' => $mekanikId,
                            'kode_kantor'  => $viewadmin->kode_kantor,
                        ],
                        [
                            'id'        => Str::uuid(),
                            'code_data' => $getdata->code_data,
                            'tanggal'   => $getdata->tanggal,
                            'kode_user' => $viewadmin->id,
                        ]
                    );
                }

                if($savedata){  
                    if($getdata['counttransaksi'] == 0){       
                        $savedata = Piutang::create([
                            'id' => Str::uuid(),
                            'code_data' => $getdata->code_data,
                            'nomor' => $getdata->nomor,
                            'tanggal' => $getdata->tanggal,
                            'jumlah' => $getdata->grand_total,
                            'bayar' => 0,
                            'sisa' => $getdata->grand_total,
                            'kode_user' => $viewadmin->id,
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]);   

                        $savedata = JurnalUmum::create([
                            'code_data' => $getdata->code_data,
                            'nomor' => $getdata->nomor,
                            'tanggal' => $getdata->tanggal,
                            'kode_akun' =>'112',
                            'uraian' => 'Piutang Dagang',
                            'debet' => $getdata->grand_total,
                            'kredit' => 0,
                            'kode_user' => $viewadmin->id,
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]);   

                        $savedata = JurnalUmum::create([
                            'code_data' => $getdata->code_data,
                            'nomor' => $getdata->nomor,
                            'tanggal' => $getdata->tanggal,
                            'kode_akun' =>'411',
                            'uraian' => 'Penjualan',
                            'debet' => 0,
                            'kredit' => $getdata->grand_total,
                            'kode_user' => $viewadmin->id,
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]); 
                    }else{   
                        $updatedata = Piutang::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)
                            ->update([
                                'nomor' => $getdata->nomor,
                                'tanggal' => $getdata->tanggal,
                                'jumlah' => $getdata->grand_total,
                                'bayar' => 0,
                                'sisa' => $getdata->grand_total,
                        ]);

                        $updatedata = JurnalUmum::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->where('nomor', $getdata->nomor)->where('tanggal', $getdata->tanggal)->where('kode_akun', '112')->where('uraian', 'Piutang Dagang')->where('debet', 0)
                            ->update([
                                'debet' => $getdata->grand_total,
                        ]);  

                        $updatedata = JurnalUmum::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->where('nomor', $getdata->nomor)->where('tanggal', $getdata->tanggal)->where('kode_akun', '411')->where('uraian', 'Penjualan')->where('kredit', 0)
                            ->update([
                                'kredit' => $getdata->grand_total,
                        ]); 
                    }

                    // Pengiriman Barang
                        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d'); 
                        $yearnow = Carbon::now()->modify("0 days")->format('Y');
                        $getdata['pengiriman'] = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();
        
                        $dataAll = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
                        $countData = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();
        
                        if($countData <> 0){
                            $newCodeDataKirim = substr($dataAll->nomor_pengiriman,-7);
                            $newCodeDataKirim = $newCodeDataKirim + 1;
                        }else{
                            $newCodeDataKirim = 1;
                        }
        
                        $kantor = $viewadmin->kode_kantor;
                        $newCodeDataKirim = str_pad($newCodeDataKirim, 7, "0", STR_PAD_LEFT);
                        $datenow = Carbon::now()->modify("0 days")->format('Y'); 
                        $newCodeDataKirim = "PGB-".$datenow.'.'.$newCodeDataKirim;                    
        
                        $getdata['penjualan'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();
                        $getdata['list_prod'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->orderBy('created_at', 'ASC')->get();
                        $getdata['customer'] = Customer::where('id',$getdata['penjualan']->kode_customer)->first();    
                        $keterangan_kirim = 'Pengiriman barang ['.$getdata->nomor.' - '.$getdata['customer']->nama.']';
                        
                        $uuidkirim = Str::uuid();
                        $savedata_kirim = Pengiriman::create([
                            'id' => $uuidkirim,
                            'code_data' => $getdata->code_data,
                            'nomor_pengiriman' => $newCodeDataKirim,
                            'nomor_penjualan' => $getdata->nomor,
                            'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                            'ket' => $keterangan_kirim,
                            'kode_gudang' => $getdata['penjualan']->kode_gudang,
                            'kode_kantor' => $getdata['penjualan']->kode_kantor,
                            'kode_user' => $viewadmin->id,
                        ]);

                        foreach($getdata['list_prod'] as $key => $list){
                            $qty_penjualan = $list->jumlah_jual - $list->jumlah_kirim;

                            if($list->jumlah_jual != $list->jumlah_kirim){
                                $uuidrdo = Str::uuid();
                                ListPengiriman::create([  
                                    'id' => $uuidrdo,
                                    'code_data' => $getdata->code_data,
                                    'nomor_pengiriman' => $newCodeDataKirim,
                                    'nomor_penjualan' => $getdata->nomor,
                                    'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                    'kode_barang' => $list->kode_barang,
                                    'jumlah_jual' => $qty_penjualan,
                                    'jumlah_kirim' => $qty_penjualan,
                                    'kode_satuan' => $list->kode_satuan,
                                    'kode_kantor' => $list->kode_kantor,
                                    'kode_user' => $list->kode_user,
                                ]);
                                
                                ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata->nomor)->where('kode_barang', $list->kode_barang)->update(['jumlah_kirim' => $qty_penjualan]);

                                $getdata_satuan = Satuan::Where('id',$list->kode_satuan)->first();
                                $total_isi = $qty_penjualan * $getdata_satuan->isi;
        
                                $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $newCodeDataKirim)->where('kode_barang', $list->kode_barang)->count();
                                if($count_historystock == 0){
                                    $uuid_stock = Str::uuid();
                                    HistoryStock::create([  
                                        'id' => $uuid_stock,
                                        'code_data' => $getdata->code_data,
                                        'nomor' => $newCodeDataKirim,
                                        'kode_barang' => $list->kode_barang,
                                        'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                        'masuk' => 0,
                                        'keluar' => $total_isi,
                                        'ket' => $getdata->ket,
                                        'kode_gudang' => $getdata->kode_gudang,
                                        'kode_kantor' => $viewadmin->kode_kantor,
                                        'kode_user' => $viewadmin->id,
                                    ]);
                                }else{
                                    HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)
                                        ->where('nomor', $newCodeDataKirim)
                                        ->where('kode_barang', $list->kode_barang)
                                        ->update(['keluar' => $total_isi]);
                                }
                            }
                        }                        

                        Activity::create([
                            'id' => Str::uuid(),
                            'code_data' => $getdata->code_data,
                            'kode_user' => $viewadmin->id,
                            'activity' => 'Pengiriman barang ['.$getdata->nomor.' - '.$newCodeDataKirim.']',
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]);
                    // end Pengiriman Barang

                    // Pembayaran                                
                        $getdataPenjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();
                        if($getdataPenjualan->jenis_penjualan == 'Cash'){
                            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d'); 
                            $yearnow = Carbon::now()->modify("0 days")->format('Y');
                            $getdata['piutangbayar'] = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();                
                            $dataAll['piutangbayar'] = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
                            $countDataPiutangBayar = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();
                
                            if($countDataPiutangBayar <> 0){
                                $newCodeDataPembayaran = substr($dataAll['piutangbayar']->nomor,-7);
                                $newCodeDataPembayaran = $newCodeDataPembayaran + 1;
                            }else{
                                $newCodeDataPembayaran = 1;
                            }
                
                            $kantor = $viewadmin->kode_kantor;
                            $newCodeDataPembayaran = str_pad($newCodeDataPembayaran, 7, "0", STR_PAD_LEFT);
                            $datenow = Carbon::now()->modify("0 days")->format('Y'); 
                            $newCodeDataPembayaran = "PP-".$datenow.'.'.$newCodeDataPembayaran;

                            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();
                            $uuidPembayaran = Str::uuid();
                            $savedata = PiutangBayar::create([
                                'id' => $uuidPembayaran,
                                'code_data' => $getdata_penjualan->code_data,
                                'nomor' => $newCodeDataPembayaran,
                                'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                'nomor_piutang' => $getdata->nomor,
                                'jumlah' => $getdata_penjualan->grand_total,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);

                            if($savedata){                                 
                                $updatedata = Piutang::where('nomor', $getdata->nomor)->where('kode_kantor', $viewadmin->kode_kantor)
                                    ->update([
                                        'bayar' => $getdata_penjualan->grand_total,
                                        'sisa' => 0,
                                ]);
                                
                                $uuidHistoryKas = Str::uuid();
                                HistoryKas::create([     
                                    'id' => $uuidHistoryKas,
                                    'code_data' => $getdata_penjualan->code_data,
                                    'nomor' => $newCodeDataPembayaran,
                                    'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                    'debet' => $getdata_penjualan->grand_total,
                                    'kredit' => 0,
                                    'keterangan' => 'Diterima Pembayaran penjualan nomor ['.$getdata->nomor.']',
                                    'kode_kantor' => $viewadmin->kode_kantor,
                                    'kode_user' => $viewadmin->id,
                                ]);

                                JurnalUmum::create([  
                                    'code_data' => $getdata_penjualan->code_data,
                                    'nomor' => $newCodeDataPembayaran,
                                    'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                    'kode_akun' => '211',
                                    'uraian' => 'Hutang Dagang',
                                    'debet' => $getdata_penjualan->grand_total,
                                    'kredit' => 0,
                                    'kode_kantor' => $viewadmin->kode_kantor,
                                    'kode_user' => $viewadmin->id,
                                ]);

                                JurnalUmum::create([  
                                    'code_data' => $getdata_penjualan->code_data,
                                    'nomor' => $newCodeDataPembayaran,
                                    'tanggal' => Carbon::parse($getdata->tanggal)->format('Y-m-d'),
                                    'kode_akun' => '111',
                                    'uraian' => 'Kas',
                                    'debet' => 0,
                                    'kredit' => $getdata_penjualan->grand_total,
                                    'kode_kantor' => $viewadmin->kode_kantor,
                                    'kode_user' => $viewadmin->id,
                                ]);

                                $uuidActivity = Str::uuid();
                                Activity::create([
                                    'id' => $uuidActivity,
                                    'code_data' => $getdata_penjualan->code_data,
                                    'kode_user' => $viewadmin->id,
                                    'activity' => 'Tambah data pembayaran penjualan ['.$getdata->nomor.']',
                                    'kode_kantor' => $viewadmin->kode_kantor,
                                ]);
                            }
                        }
                    // end Pembayaran

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
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

    public function historypenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $dateyear = Carbon::now()->modify("-1 year")->format('Y-m-d') . ' 00:00:00';

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("+1 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $searchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($searchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($searchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $results['list'] = DB::table('db_customer')
                ->join('db_penjualan', 'db_customer.id', '=', 'db_penjualan.kode_customer')
                ->whereBetween('db_penjualan.tanggal', [$datefilterstart, $datefilterend])
                ->Where('db_penjualan.kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_penjualan.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.status_transaksi','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_penjualan.tanggal', 'DESC')
                ->orderBy('db_penjualan.nomor', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                
                $results['detail_penjualan'][$data->code_data] = Penjualan::select('code_data','nomor','kode_user','kode_kantor','kode_customer','kode_gudang','status_transaksi')->where('nomor', $data->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->first();
                
                $results['user_input'][$data->code_data] = User::select('code_data','full_name')->where('id', $results['detail_penjualan'][$data->code_data]->kode_user)->first();
                if(!$results['user_input'][$data->code_data]){
                    $results['user_input'][$data->code_data]['full_name'] = 'Belum Ditentukan';
                }

                $results['qty_penjualan'][$data->code_data] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->code_data]->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->sum('jumlah_jual');

                $results['qty_kirim'][$data->code_data] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->code_data]->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->sum('jumlah_kirim');

                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('kode', $results['detail_penjualan'][$data->code_data]->kode_kantor)->first();

                $results['detail_customer'][$data->code_data] = Customer::where('id', $results['detail_penjualan'][$data->code_data]->kode_customer)->first();

                $results['detail_gudang'][$data->code_data] = Gudang::select('nama')->where('id', $results['detail_penjualan'][$data->code_data]->kode_gudang)->first();

                $results['list_mekanik'][$data->code_data] = ListPenjualanMekanik::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->orderBy('created_at', 'ASC')->get();
        
                foreach($results['list_mekanik'][$data->code_data] as $key => $listMekanik){
                    $results['detail_mekanik'][$data->code_data][$listMekanik->code_mekanik] = Karyawan::where('code_data', $listMekanik->code_mekanik)->first();                   
                }
                
                $results['list_produk'][$data->nomor] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->orderBy('created_at', 'ASC')->get(); 
        
                foreach($results['list_produk'][$data->nomor] as $key => $list){
                    $results['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    
                    $results['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                    $results['satuan_produk'][$list->kode_barang] = Satuan::where('id', $results['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                    
                    $results['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $results['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get();                        
                }
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historypenjualanitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $dateyear = Carbon::now()->modify("-1 year")->format('Y-m-d') . ' 00:00:00';

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("+1 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $searchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($searchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($searchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $results['list'] = DB::table('db_customer')
                ->join('db_penjualan', 'db_customer.id', '=', 'db_penjualan.kode_customer')
                ->join('db_penjualand', 'db_penjualan.nomor', '=', 'db_penjualand.nomor')
                ->join('db_barang', 'db_penjualand.kode_barang', '=', 'db_barang.id')
                ->whereBetween('db_penjualan.tanggal', [$datefilterstart, $datefilterend])
                ->Where('db_penjualan.kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_penjualan.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.status_transaksi','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_penjualan.tanggal', 'DESC')
                ->orderBy('db_penjualan.nomor', 'DESC')
                ->paginate($vd ? $vd : 20);
            

            foreach($results['list'] as $key => $data){
                
                $results['detail_penjualan'][$data->id] = Penjualan::select('code_data','nomor','kode_user','kode_kantor','kode_customer','kode_gudang','status_transaksi','ket')->where('nomor', $data->nomor)->first();
                
                $results['user_input'][$data->id] = User::select('code_data','full_name')->where('id', $results['detail_penjualan'][$data->id]->kode_user)->first();
                
                $results['kategori_prod'][$data->id] = Kategori::select('nama')->where('id',$data->kode_jenis)->orderBy('created_at','DESC')->first();
                
                $results['produk'][$data->id] = Barang::select('code_data','nama')->where('id', $data->kode_barang)->first();
                
                $results['satuan_prod'][$data->id] = Satuan::select('code_data','nama')->where('id', $data->kode_satuan)->first();

                $results['qty_penjualan'][$data->id] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->id]->nomor)->sum('jumlah_jual');

                $results['qty_kirim'][$data->id] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->id]->nomor)->sum('jumlah_kirim');

                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('kode', $results['detail_penjualan'][$data->id]->kode_kantor)->first();

                $results['detail_customer'][$data->id] = Customer::where('id', $results['detail_penjualan'][$data->id]->kode_customer)->first();
                if(!$results['detail_customer'][$data->id]){
                    $results['detail_customer'][$data->id]['nama'] = 'Belum Ditentukan';
                }

                $results['detail_gudang'][$data->id] = Gudang::select('nama')->where('id', $results['detail_penjualan'][$data->id]->kode_gudang)->first();

            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function persediaanbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        } else {
            $vd = $request->vd ?: '20';
            
            // Ambil barang dan join yang dibutuhkan dalam satu query
            $results['list'] = DB::table('db_arusstock')
                ->join('db_barang', 'db_arusstock.kode_barang', '=', 'db_barang.id')
                ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
                ->select(
                    'db_barang.id',
                    'db_barang.code_data',
                    'db_barang.kode',
                    'db_barang.harga_beli',
                    'db_barang.harga_jual1',
                    'db_barang.harga_jual2',
                    'db_barang.harga_jual3',
                    'db_barang.harga_jual4',
                    'db_barang.nama AS nama_barang',
                    'db_satuan_barang.nama AS nama_satuan',
                    'db_barang.kode_satuan_default',
                    'db_arusstock.kode_barang'
                )
                ->where('db_barang.type_produk', 'Barang')
                ->where(function ($query) use ($request) {
                    $query->where('db_barang.code_data', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.kode', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_satuan_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.kode_satuan_default', 'LIKE', '%' . $request->keysearch . '%');
                })
                ->groupBy(
                    'db_barang.id',
                    'db_barang.code_data',
                    'db_barang.kode',
                    'db_barang.harga_beli',
                    'db_barang.harga_jual1',
                    'db_barang.harga_jual2',
                    'db_barang.harga_jual3',
                    'db_barang.harga_jual4',
                    'db_barang.nama',
                    'db_satuan_barang.nama',
                    'db_barang.kode_satuan_default',
                    'db_arusstock.kode_barang'
                )
                ->orderBy('db_barang.nama', 'DESC')
                ->paginate($vd);

            // Ambil semua kode_barang yang ada di hasil pencarian
            $kodeBarangList = $results['list']->pluck('kode_barang')->toArray();

            // Lakukan batch query untuk stok akhir, barang, kategori, merk, supplier, gudang
            $stokAkhirList = HistoryStock::whereIn('kode_barang', $kodeBarangList)
                ->select('kode_barang', DB::raw('SUM(masuk - keluar) as stock_akhir'))
                ->groupBy('kode_barang')
                ->pluck('stock_akhir', 'kode_barang');

            $barangList = Barang::whereIn('id', $kodeBarangList)->get()->keyBy('id');
            $kategoriList = Kategori::whereIn('id', $barangList->pluck('kode_jenis'))->get()->keyBy('id');
            $merkList = Merk::whereIn('id', $barangList->pluck('kode_brand'))->get()->keyBy('id');
            $supplierList = Supplier::whereIn('id', $barangList->pluck('kode_supplier'))->get()->keyBy('id');

            $results['list_gudang'] = Gudang::where('status_data', 'Aktif')
                ->orderBy('nama', 'ASC')
                ->get();

            foreach($results['list'] as $list){ 
                $kodeBarang = $list->kode_barang;
            
                // Set stok akhir dari hasil query batch
                $results['stock_akhir'][$kodeBarang] = $stokAkhirList[$kodeBarang] ?? 0;
            
                // Pastikan data barang ada sebelum diakses
                if (isset($barangList[$kodeBarang])) {
                    $results['barang'][$kodeBarang] = $barangList[$kodeBarang];
            
                    // Set kategori, merk, supplier dengan pengecekan null
                    $results['kategori'][$kodeBarang] = $kategoriList[$results['barang'][$kodeBarang]->kode_jenis] ?? null;
                    $results['merk'][$kodeBarang] = $merkList[$results['barang'][$kodeBarang]->kode_brand] ?? null;
                    $results['supplier'][$kodeBarang] = $supplierList[$results['barang'][$kodeBarang]->kode_supplier] ?? null;
                } else {
                    $results['barang'][$kodeBarang] = null;
                    $results['kategori'][$kodeBarang] = null;
                    $results['merk'][$kodeBarang] = null;
                    $results['supplier'][$kodeBarang] = null;
                }
            
                // Set stok per gudang
                foreach($results['list_gudang'] as $gudang){
                    $results['stok_pergudang'][$kodeBarang][$gudang->code_data] = HistoryStock::where('kode_gudang', $gudang->id)
                        ->where('kode_barang', $kodeBarang)
                        ->sum(DB::raw('masuk - keluar'));
                }
            
                // Ambil pembelian tertinggi dengan satu query
                $pembelianTertinggi = ListPembelian::where('kode_barang', $kodeBarang)
                ->orderBy('harga', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_beli'][$kodeBarang] = $pembelianTertinggi->harga ?? 'Belum ditentukan';
                $results['tanggal_beli'][$kodeBarang] = $pembelianTertinggi->tanggal ?? 'Belum ditentukan';
            
                // Ambil penjualan terakhir dengan satu query
                $penjualanTerakhir = ListPenjualan::where('kode_barang', $kodeBarang)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_jual'][$kodeBarang] = $penjualanTerakhir->harga ?? 'Belum ditentukan';
                $results['tanggal_jual'][$kodeBarang] = $penjualanTerakhir->tanggal ?? 'Belum ditentukan';
            }           

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $results,'vd' => $vd,'keysearch' => $request->keysearch]);
        }
    }

    public function pendingpenjualan($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){            
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();

            if($getdata){  
                $validator = Validator::make($request->all(), [
                    'customer' => 'required|string|max:200',
                ]);
    
                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}
                $savedata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)
                    ->update([
                        'status_transaksi' => 'Pending',
                    ]);
                if($savedata){
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
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
}