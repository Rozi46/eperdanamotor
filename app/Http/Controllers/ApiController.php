<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;

class ApiController extends Controller
{
    // Admin Login
    public function login(Request $request)
    {
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = array();
        $email = $request->email;
        $password = $request->password;

        $validator = Validator::make($request->all(), [
            'email' => 'required|min:1|max:200',
            'password' => 'required|min:1|max:200',
        ]);

        if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]); }

        $credentials = $request->only('email', 'password');
        $getdata = User::where('email',$email)->first();
        $getstatusdata = User::where('email',$email)->where('status_data','Aktif')->first();

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
                
                    if($getdata->code_data != '8603264093R'){
                        Activity::create([
                            'id' => Str::uuid(),
                            'code_data' => $newCodeData,
                            'kode_user' => $getdata->id,
                            'activity' => 'Masuk ke sistem '.$link_akses,
                            'kode_kantor' => $getdata->kode_kantor,
                        ]);
                    }

                    $token = Str::uuid();
                    User::where('code_data', $getdata->code_data)
                        ->update([
                            'key_token' => $token,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Berhasil masuk ke sistem','key_token' => $token,'results' => $object ],200);

                }else{
                    return response()->json(['status_message' => 'error','note' => 'Kata sandi salah','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data pengguna tidak aktif','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Data tidak terdaftar','results' => $object]);
        }
    }

    public function logout(Request $request)
    {
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = array();
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
            
            if($viewadmin->code_data != '8603264093R'){
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Keluar dari sistem '.$link_akses,
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);
            }

            User::where('id', $viewadmin->id)
                ->update([
                    'key_token' => null,
            ]);
    
            Session::flush();
    
            return response()->json(['status_message' => 'success','note' => 'Berhasil keluar ke sistem', 'code_data' => $viewadmin->code_data,]);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat keluar ke sistem','code_data' => null]);
        }                
    }

    public function getdash(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        $thn_now = Carbon::now()->format('Y');
        $bln_now = Carbon::now()->format('m');
        $hari_now = Carbon::now()->format('d');

        $results['thn_now'] = $thn_now;
        $results['bln_now'] = $bln_now;

        $vd = $request->filled('vd') ? $request->vd : 20;

        if(!$viewadmin){ 
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data',]);   
        }else{       
            $results['total_hutang'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->sum('sisa'); 
            $results['total_hutang_count'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->Where('sisa', '<>', 0)->count();        
            $results['count_pembelian'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->count(); 

            $results['summary_pembelian_hari'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                ->whereDay('tanggal',$hari_now)
                ->whereMonth('tanggal',$bln_now)
                ->whereYear('tanggal',$thn_now)
                ->where(function($query) use ($request) {
                    $query->Where('status_transaksi','Proses')
                    ->OrWhere('status_transaksi','Finish');
                })
                ->Where('status_transaksi','!=','Input')->count();

                $results['summary_pembelian_nilai_hari'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                    ->whereDay('tanggal',$hari_now)
                    ->whereMonth('tanggal',$bln_now)
                    ->whereYear('tanggal',$thn_now)
                    ->where(function($query) use ($request) {
                        $query->Where('status_transaksi','Proses')
                        ->OrWhere('status_transaksi','Finish');
                    })
                    ->Where('status_transaksi','!=','Input')
                    ->sum('grand_total');
                        
                    $results['summary_pembelian_bln'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereMonth('tanggal',$bln_now)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')->count();

                    $results['summary_pembelian_nilai_bln'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereMonth('tanggal',$bln_now)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')
                        ->sum('grand_total');
                    
                    $results['summary_pembelian_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')->count();

                    $results['summary_pembelian_nilai_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')
                        ->sum('grand_total');

                   
            $results['total_piutang'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->sum('sisa');
            $results['total_piutang_count'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->Where('sisa', '<>', 0)->count(); 
            $results['count_penjualan'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->count();

            $results['summary_penjualan_hari'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                ->whereDay('tanggal',$hari_now)
                ->whereMonth('tanggal',$bln_now)
                ->whereYear('tanggal',$thn_now)
                ->where(function($query) use ($request) {
                    $query->Where('status_transaksi','Proses')
                    ->OrWhere('status_transaksi','Finish');
                })
                ->Where('status_transaksi','!=','Input')->count();

                $results['summary_penjualan_nilai_hari'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                    ->whereDay('tanggal',$hari_now)
                    ->whereMonth('tanggal',$bln_now)
                    ->whereYear('tanggal',$thn_now)
                    ->where(function($query) use ($request) {
                        $query->Where('status_transaksi','Proses')
                        ->OrWhere('status_transaksi','Finish');
                    })
                    ->Where('status_transaksi','!=','Input')
                    ->sum('grand_total');
                        
                    $results['summary_penjualan_bln'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereMonth('tanggal',$bln_now)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')->count();

                    $results['summary_penjualan_nilai_bln'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereMonth('tanggal',$bln_now)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')
                        ->sum('grand_total');
                    
                    $results['summary_penjualan_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')->count();

                    $results['summary_penjualan_nilai_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->whereYear('tanggal',$thn_now)
                        ->where(function($query) use ($request) {
                            $query->Where('status_transaksi','Proses')
                            ->OrWhere('status_transaksi','Finish');
                        })
                        ->Where('status_transaksi','!=','Input')
                        ->sum('grand_total');
                    
                        for ($x = 1; $x <= 31; $x++) {
                            $results['po_'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->count();
    
                            $results['so_'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->count();

                            $results['summary_po_'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->sum('grand_total');
    
                            $results['summary_so_'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->sum('grand_total');

                            $results['po_thn'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->count();
    
                            $results['so_thn'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->count();

                            $results['summary_po_thn'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->sum('grand_total');
    
                            $results['summary_so_thn'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->sum('grand_total');
                        }

            $results['total_summary_po'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->sum('grand_total');

            $results['total_summary_so'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->sum('grand_total');

            $results['total_po_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->count();

            $results['total_so_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->count();

            return response()->json([
                'status_message' => 'success',
                'results' => $results,
            ]);
        }
    }

    public function getadmin(Request $request)
    {
        $object = array();
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

    public function getSetting(Request $request)
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

    // Isi Combobox
    public function listopsatuan(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Satuan::Where('isi','1')->orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopkategori(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Kategori::orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopmerk(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Merk::orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopsupplier(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Supplier::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopgudang(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Gudang::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopcabang(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Cabang::orderBy('nama_cabang', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listopakun(Request $request)
    {
        $object = array();
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Akun::whereRaw("LEFT(kode, 1) = '5'")->whereRaw("LENGTH(kode) = 3")->orderBy('id', 'ASC')->get();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function downloadmanualbook(Request $request)
    {
        $uuid = Str::uuid();
        $object = array();  
        
        $filePath = public_path('themes/admin/AdminOne/ManualBook/' . $request['d']);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return response()->json(['status_message' => 'failed','note' => 'Data tidak ditemukan','results' => $object]);
        }
    }
}
