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
                
                    if($getdata->code_data != '8603264093R' && $getdata->code_data != '20230401134540H'){
                        Activity::create([
                            'id' => Str::uuid(),
                            'code_data' => $newCodeData,
                            'kode_user' => $getdata->id,
                            'activity' => 'Masuk ke sistem',
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
            
            if($viewadmin->code_data != '8603264093R' && $viewadmin->code_data != '20230401134540H'){
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Keluar dari sistem',
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

    // public function getdash(Request $request)
    // {
    //     date_default_timezone_set('Asia/Jakarta');
    //     $object = array();
    //     $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

    //     // Ambil waktu sekarang satu kali agar konsisten
    //     $now = Carbon::now();

    //     $hari_now = $now->format('d');
    //     $bln_now  = $now->format('m');
    //     $thn_now  = $now->format('Y');

    //     // $thn_now = Carbon::now()->format('Y');
    //     // $bln_now = Carbon::now()->format('m');
    //     // $hari_now = Carbon::now()->format('d');

    //     $results['thn_now'] = $thn_now;
    //     $results['bln_now'] = $bln_now;

    //     $vd = $request->filled('vd') ? $request->vd : 20;

    //     if(!$viewadmin){ 
    //         return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data',]);   
    //     }else{       
    //         $results['total_hutang'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->sum('sisa'); 
    //         $results['total_hutang_count'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->Where('sisa', '<>', 0)->count();        
    //         $results['count_pembelian'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->count(); 

    //         $results['summary_pembelian_hari'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereDay('tanggal',$hari_now)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_pembelian_nilai_hari'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereDay('tanggal',$hari_now)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');
                    
    //         $results['summary_pembelian_bln'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_pembelian_nilai_bln'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');
            
    //         $results['summary_pembelian_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_pembelian_nilai_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');

                   
    //         $results['total_piutang'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->sum('sisa');
    //         $results['total_piutang_count'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->Where('sisa', '<>', 0)->count(); 
    //         $results['count_penjualan'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->count();

    //         $results['summary_penjualan_hari'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereDay('tanggal',$hari_now)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_penjualan_nilai_hari'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereDay('tanggal',$hari_now)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');
                    
    //         $results['summary_penjualan_bln'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_penjualan_nilai_bln'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereMonth('tanggal',$bln_now)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');
            
    //         $results['summary_penjualan_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')->count();

    //         $results['summary_penjualan_nilai_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)
    //             ->whereYear('tanggal',$thn_now)
    //             ->where(function($query) use ($request) {
    //                 $query->Where('status_transaksi','Proses')
    //                 ->OrWhere('status_transaksi','Finish');
    //             })
    //             ->Where('status_transaksi','!=','Input')
    //             ->sum('grand_total');
            
    //             for ($x = 1; $x <= 31; $x++) {
    //                 $results['po_'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->count();

    //                 $results['so_'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->count();

    //                 $results['summary_po_'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->sum('grand_total');

    //                 $results['summary_so_'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->Where('tanggal',$thn_now.'-'.$bln_now.'-'.$x)->sum('grand_total');

    //                 $results['po_thn'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->count();

    //                 $results['so_thn'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->count();

    //                 $results['summary_po_thn'.$x] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->sum('grand_total');

    //                 $results['summary_so_thn'.$x] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->WhereMonth('tanggal',$x)->sum('grand_total');
    //             }

    //         $results['total_summary_po'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->sum('grand_total');

    //         $results['total_summary_so'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->sum('grand_total');

    //         $results['total_po_thn'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->count();

    //         $results['total_so_thn'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('status_transaksi','!=','Input')->WhereYear('tanggal',$thn_now)->count();

 

    //         // Batas periode tanggal
    //         $todayStart    = $now->copy()->startOfDay()->toDateTimeString();
    //         $tomorrowStart = $now->copy()->addDay()->startOfDay()->toDateTimeString();

    //         $monthStart = $now->copy()->startOfMonth()->startOfDay()->toDateTimeString();
    //         $nextMonth  = $now->copy()->addMonth()->startOfMonth()->startOfDay()->toDateTimeString();

    //         $yearStart = $now->copy()->startOfYear()->startOfDay()->toDateTimeString();
    //         $nextYear  = $now->copy()->addYear()->startOfYear()->startOfDay()->toDateTimeString();

    //         // Satu query untuk seluruh summary kas
    //         $summaryKas = HistoryKas::where('kode_kantor', $viewadmin->kode_kantor)
    //             ->where('tanggal', '>=', $yearStart)
    //             ->where('tanggal', '<', $nextYear)
    //             ->selectRaw("
    //                 -- =================================
    //                 -- KAS MASUK (DEBET)
    //                 -- =================================

    //                 SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND debet > 0
    //                     THEN 1 ELSE 0
    //                 END) AS summary_kasmasuk_hari,

    //                 COALESCE(SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND debet > 0
    //                     THEN debet ELSE 0
    //                 END), 0) AS summary_kasmasuk_nilai_hari,

    //                 SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND debet > 0
    //                     THEN 1 ELSE 0
    //                 END) AS summary_kasmasuk_bln,

    //                 COALESCE(SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND debet > 0
    //                     THEN debet ELSE 0
    //                 END), 0) AS summary_kasmasuk_nilai_bln,

    //                 SUM(CASE
    //                     WHEN debet > 0 THEN 1 ELSE 0
    //                 END) AS summary_kasmasuk_thn,

    //                 COALESCE(SUM(CASE
    //                     WHEN debet > 0 THEN debet ELSE 0
    //                 END), 0) AS summary_kasmasuk_nilai_thn,

    //                 -- =================================
    //                 -- KAS KELUAR (KREDIT)
    //                 -- =================================

    //                 SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
    //                     THEN 1 ELSE 0
    //                 END) AS summary_kaskeluar_hari,

    //                 COALESCE(SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
    //                     THEN kredit ELSE 0
    //                 END), 0) AS summary_kaskeluar_nilai_hari,

    //                 SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
    //                     THEN 1 ELSE 0
    //                 END) AS summary_kaskeluar_bln,

    //                 COALESCE(SUM(CASE
    //                     WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
    //                     THEN kredit ELSE 0
    //                 END), 0) AS summary_kaskeluar_nilai_bln,

    //                 SUM(CASE
    //                     WHEN kredit > 0 THEN 1 ELSE 0
    //                 END) AS summary_kaskeluar_thn,

    //                 COALESCE(SUM(CASE
    //                     WHEN kredit > 0 THEN kredit ELSE 0
    //                 END), 0) AS summary_kaskeluar_nilai_thn
    //             ", [
    //                 // Kas masuk hari
    //                 $todayStart, $tomorrowStart,
    //                 $todayStart, $tomorrowStart,

    //                 // Kas masuk bulan
    //                 $monthStart, $nextMonth,
    //                 $monthStart, $nextMonth,

    //                 // Kas keluar hari
    //                 $todayStart, $tomorrowStart,
    //                 $todayStart, $tomorrowStart,

    //                 // Kas keluar bulan
    //                 $monthStart, $nextMonth,
    //                 $monthStart, $nextMonth
    //             ])
    //             ->first();

    //         // Gabungkan hasil summary dengan results sebelumnya
    //         $results = array_merge(
    //             $results,
    //             $summaryKas->getAttributes()
    //         );
            
    //         // $results['summary_kasmasuk_hari'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereDay('tanggal',$hari_now)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kasmasuk_nilai_hari'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereDay('tanggal',$hari_now)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('debet');
                    
    //         // $results['summary_kasmasuk_bln'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kasmasuk_nilai_bln'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('debet');
            
    //         // $results['summary_kasmasuk_thn'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kasmasuk_nilai_thn'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('debet');

    //         // $results['summary_kaskeluar_hari'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereDay('tanggal',$hari_now)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kaskeluar_nilai_hari'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereDay('tanggal',$hari_now)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('kredit');
                    
    //         // $results['summary_kaskeluar_bln'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kaskeluar_nilai_bln'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereMonth('tanggal',$bln_now)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('kredit');
            
    //         // $results['summary_kaskeluar_thn'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->count();

    //         // $results['summary_kaskeluar_nilai_thn'] = HistoryKas::Where('kode_kantor',$viewadmin->kode_kantor)
    //         //     ->whereYear('tanggal',$thn_now)
    //         //     ->sum('kredit');
            
    //         return response()->json([
    //             'status_message' => 'success',
    //             'results' => $results,
    //         ]);
    //     }
    // }

    public function getdash(Request $request)
    {
        // ==========================================
        // 1. KONFIGURASI WAKTU
        // ==========================================

        $now = Carbon::now('Asia/Jakarta');

        $todayDate = $now->toDateString();

        $thn_now = $now->format('Y');
        $bln_now = $now->format('m');
        $hari_now = $now->format('d');

        $results = [
            'thn_now' => $thn_now,
            'bln_now' => $bln_now,
        ];

        // ==========================================
        // 2. VALIDASI ADMIN
        // ==========================================

        $viewadmin = User::where('id', $request->u)
            ->where('key_token', $request->token)
            ->first();

        if (!$viewadmin) {
            return response()->json([
                'status_message' => 'error',
                'note' => 'Terjadi kesalahan saat proses data',
            ]);
        }

        $kodeKantor = $viewadmin->kode_kantor;

        // Batas periode
        $todayStart = $now->copy()->startOfDay()->toDateTimeString();
        $tomorrowStart = $now->copy()->addDay()->startOfDay()->toDateTimeString();

        $monthStart = $now->copy()->startOfMonth()->toDateTimeString();
        $nextMonth = $now->copy()->addMonth()->startOfMonth()->toDateTimeString();

        $yearStart = $now->copy()->startOfYear()->toDateTimeString();
        $nextYear = $now->copy()->addYear()->startOfYear()->toDateTimeString();


        // ==========================================
        // 3. SUMMARY HUTANG
        // ==========================================

        $summaryHutang = Hutang::where('kode_kantor', $kodeKantor)
            ->selectRaw("
                COALESCE(SUM(sisa), 0) AS total_hutang,
                SUM(CASE WHEN sisa <> 0 THEN 1 ELSE 0 END) AS total_hutang_count
            ")
            ->first();

        $results['total_hutang'] = $summaryHutang->total_hutang;
        $results['total_hutang_count'] = $summaryHutang->total_hutang_count;


        // ==========================================
        // 4. SUMMARY PIUTANG
        // ==========================================

        $summaryPiutang = Piutang::where('kode_kantor', $kodeKantor)
            ->selectRaw("
                COALESCE(SUM(sisa), 0) AS total_piutang,
                SUM(CASE WHEN sisa <> 0 THEN 1 ELSE 0 END) AS total_piutang_count
            ")
            ->first();

        $results['total_piutang'] = $summaryPiutang->total_piutang;
        $results['total_piutang_count'] = $summaryPiutang->total_piutang_count;


        // ==========================================
        // 5. FUNGSI AGREGASI PEMBELIAN & PENJUALAN
        // ==========================================

        $ambilSummaryTransaksi = function ($modelClass, $jenis, $prefix) use (
            $kodeKantor,
            $yearStart,
            $nextYear,
            $monthStart,
            $nextMonth,
            $todayStart,
            $tomorrowStart,
            $todayDate,
            $bln_now
        ) {

            // --------------------------------------
            // Jumlah transaksi keseluruhan
            // --------------------------------------

            $totalCount = app($modelClass)
                ->newQuery()
                ->where('kode_kantor', $kodeKantor)
                ->count();

            // --------------------------------------
            // Satu query agregasi per tanggal
            // --------------------------------------

            $rows = app($modelClass)
                ->newQuery()
                ->where('kode_kantor', $kodeKantor)
                ->where('tanggal', '>=', $yearStart)
                ->where('tanggal', '<', $nextYear)
                ->selectRaw("
                    DATE(tanggal) AS tanggal_hari,
                    MONTH(tanggal) AS bulan,

                    -- Grafik: semua transaksi selain Input
                    SUM(CASE
                        WHEN status_transaksi <> ?
                        THEN 1 ELSE 0
                    END) AS grafik_count,

                    COALESCE(SUM(CASE
                        WHEN status_transaksi <> ?
                        THEN grand_total ELSE 0
                    END), 0) AS grafik_nilai,

                    -- Summary: hanya Proses dan Finish
                    SUM(CASE
                        WHEN status_transaksi IN (?, ?)
                        THEN 1 ELSE 0
                    END) AS summary_count,

                    COALESCE(SUM(CASE
                        WHEN status_transaksi IN (?, ?)
                        THEN grand_total ELSE 0
                    END), 0) AS summary_nilai
                ", [
                    'Input',
                    'Input',
                    'Proses',
                    'Finish',
                    'Proses',
                    'Finish'
                ])
                ->groupByRaw('DATE(tanggal), MONTH(tanggal)')
                ->get();

            // --------------------------------------
            // Inisialisasi grafik harian
            // --------------------------------------

            $dailyCount = array_fill(1, 31, 0);
            $dailyValue = array_fill(1, 31, 0);

            // --------------------------------------
            // Inisialisasi grafik bulanan
            // --------------------------------------

            $monthlyCount = array_fill(1, 12, 0);
            $monthlyValue = array_fill(1, 12, 0);

            // --------------------------------------
            // Inisialisasi summary
            // --------------------------------------

            $summaryHariCount = 0;
            $summaryHariValue = 0;

            $summaryBulanCount = 0;
            $summaryBulanValue = 0;

            $summaryTahunCount = 0;
            $summaryTahunValue = 0;

            // --------------------------------------
            // Proses hasil query di PHP
            // --------------------------------------

            foreach ($rows as $row) {

                $tanggal = $row->tanggal_hari;
                $hari = (int) Carbon::parse($tanggal)->format('d');
                $bulan = (int) $row->bulan;

                $grafikCount = (int) $row->grafik_count;
                $grafikValue = (float) $row->grafik_nilai;

                $summaryCount = (int) $row->summary_count;
                $summaryValue = (float) $row->summary_nilai;

                // Grafik harian bulan berjalan
                if ($bulan === (int) $bln_now) {
                    $dailyCount[$hari] += $grafikCount;
                    $dailyValue[$hari] += $grafikValue;
                }

                // Grafik bulanan tahun berjalan
                $monthlyCount[$bulan] += $grafikCount;
                $monthlyValue[$bulan] += $grafikValue;

                // Summary tahun
                $summaryTahunCount += $summaryCount;
                $summaryTahunValue += $summaryValue;

                // Summary bulan berjalan
                if ($bulan === (int) $bln_now) {
                    $summaryBulanCount += $summaryCount;
                    $summaryBulanValue += $summaryValue;
                }

                // Summary hari ini
                if ($tanggal === $todayDate) {
                    $summaryHariCount += $summaryCount;
                    $summaryHariValue += $summaryValue;
                }
            }

            // --------------------------------------
            // Simpan hasil summary
            // --------------------------------------

            $result = [];

            $result['count_' . $jenis] = $totalCount;

            $result['summary_' . $jenis . '_hari'] = $summaryHariCount;
            $result['summary_' . $jenis . '_nilai_hari'] = $summaryHariValue;

            $result['summary_' . $jenis . '_bln'] = $summaryBulanCount;
            $result['summary_' . $jenis . '_nilai_bln'] = $summaryBulanValue;

            $result['summary_' . $jenis . '_thn'] = $summaryTahunCount;
            $result['summary_' . $jenis . '_nilai_thn'] = $summaryTahunValue;

            // --------------------------------------
            // Simpan grafik harian
            // --------------------------------------

            for ($x = 1; $x <= 31; $x++) {

                $result[$prefix . '_' . $x] = $dailyCount[$x];

                $result['summary_' . $prefix . '_' . $x] = $dailyValue[$x];
            }

            // --------------------------------------
            // Simpan grafik bulanan
            // --------------------------------------

            for ($x = 1; $x <= 12; $x++) {

                $result[$prefix . '_thn' . $x] = $monthlyCount[$x];

                $result['summary_' . $prefix . '_thn' . $x] = $monthlyValue[$x];
            }

            // --------------------------------------
            // Total grafik tahunan
            // --------------------------------------

            $result['total_summary_' . $prefix] = array_sum($monthlyValue);

            $result['total_' . $prefix . '_thn'] = array_sum($monthlyCount);

            // Kompatibilitas dengan key lama po_
            if ($prefix === 'po') {
                $result['po_'] = $dailyCount[31];
            }

            return $result;
        };


        // ==========================================
        // 6. SUMMARY PEMBELIAN
        // ==========================================

        $summaryPembelian = $ambilSummaryTransaksi(
            Pembelian::class,
            'pembelian',
            'po'
        );

        $results = array_merge($results, $summaryPembelian);


        // ==========================================
        // 7. SUMMARY PENJUALAN
        // ==========================================

        $summaryPenjualan = $ambilSummaryTransaksi(
            Penjualan::class,
            'penjualan',
            'so'
        );

        $results = array_merge($results, $summaryPenjualan);


        // ==========================================
        // 8. SUMMARY KAS MASUK & KAS KELUAR
        // ==========================================

        $summaryKas = HistoryKas::where('kode_kantor', $kodeKantor)
            ->where('tanggal', '>=', $yearStart)
            ->where('tanggal', '<', $nextYear)
            ->selectRaw("
                -- KAS MASUK

                SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND debet > 0
                    THEN 1 ELSE 0
                END) AS summary_kasmasuk_hari,

                COALESCE(SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND debet > 0
                    THEN debet ELSE 0
                END), 0) AS summary_kasmasuk_nilai_hari,

                SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND debet > 0
                    THEN 1 ELSE 0
                END) AS summary_kasmasuk_bln,

                COALESCE(SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND debet > 0
                    THEN debet ELSE 0
                END), 0) AS summary_kasmasuk_nilai_bln,

                SUM(CASE
                    WHEN debet > 0 THEN 1 ELSE 0
                END) AS summary_kasmasuk_thn,

                COALESCE(SUM(CASE
                    WHEN debet > 0 THEN debet ELSE 0
                END), 0) AS summary_kasmasuk_nilai_thn,

                -- KAS KELUAR

                SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
                    THEN 1 ELSE 0
                END) AS summary_kaskeluar_hari,

                COALESCE(SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
                    THEN kredit ELSE 0
                END), 0) AS summary_kaskeluar_nilai_hari,

                SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
                    THEN 1 ELSE 0
                END) AS summary_kaskeluar_bln,

                COALESCE(SUM(CASE
                    WHEN tanggal >= ? AND tanggal < ? AND kredit > 0
                    THEN kredit ELSE 0
                END), 0) AS summary_kaskeluar_nilai_bln,

                SUM(CASE
                    WHEN kredit > 0 THEN 1 ELSE 0
                END) AS summary_kaskeluar_thn,

                COALESCE(SUM(CASE
                    WHEN kredit > 0 THEN kredit ELSE 0
                END), 0) AS summary_kaskeluar_nilai_thn
            ", [
                // Kas masuk hari
                $todayStart, $tomorrowStart,
                $todayStart, $tomorrowStart,

                // Kas masuk bulan
                $monthStart, $nextMonth,
                $monthStart, $nextMonth,

                // Kas keluar hari
                $todayStart, $tomorrowStart,
                $todayStart, $tomorrowStart,

                // Kas keluar bulan
                $monthStart, $nextMonth,
                $monthStart, $nextMonth
            ])
            ->first();

        $results = array_merge(
            $results,
            $summaryKas->getAttributes()
        );


        // ==========================================
        // 9. RESPONSE JSON
        // ==========================================

        return response()->json([
            'status_message' => 'success',
            'results' => $results,
        ]);
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
