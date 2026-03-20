<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePenyesuaianstock
{    
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
    
    public function getcodepenyesuaian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            // $datenow = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 00:00:00';
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = PenyesuaianStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = PenyesuaianStock::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal_transaksi','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = PenyesuaianStock::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal_transaksi','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->code_transaksi,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            // $datenow = Carbon::now()->modify("0 days")->format('Ymd');
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PS-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData]);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }
    
    public function listbarangstockopname($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{  
            $getprod = array();

            $results = Barang::select('id','code_data','kode','nama')
                ->where('type_produk','LIKE','Barang')
                ->where(function($query) use ($request) {
                    $query->where('code_data','LIKE', '%'.$request->term.'%')
                    ->Orwhere('kode','LIKE', '%'.$request->term.'%')
                    ->Orwhere('nama','LIKE', '%'.$request->term.'%');
                })
                ->orderBy('nama', 'ASC')
                ->limit(6)
                ->get();
            
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

    public function liststockbarangSO($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{  
            $list_gudang = Gudang::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();

            // $stockprod = array();
            
            // foreach($list_gudang as $keywh => $listwh){
            //     $stok_in[$listwh->code_data] = HistoryStock::where('kode_gudang', $listwh->id)->where('kode_barang', $request->code_data)->sum('masuk');    
            //     $stok_out[$listwh->code_data]  = HistoryStock::where('kode_gudang', $listwh->id)->where('kode_barang', $request->code_data)
            //     ->sum(DB::raw("
            //         CASE
            //             WHEN keluar < 0 THEN keluar
            //             ELSE -keluar
            //         END
            //     "));

            //     $stock_akhir[$listwh->code_data] = $stok_in[$listwh->code_data] - $stok_out[$listwh->code_data];
            //     $stockprod[] = array('stock_akhir_'.$listwh->code_data => $stock_akhir[$listwh->code_data]);
            // }

            // $results[] = array(
            //     'stock_prod' => $stockprod,
            // );


            $stockprod = [];

            foreach ($list_gudang as $listwh) {

                $stock_akhir = HistoryStock::where('kode_gudang', $listwh->id)
                    ->where('kode_barang', $request->code_data)
                    ->sum(DB::raw("
                        masuk +
                        CASE
                            WHEN keluar < 0 THEN keluar
                            ELSE -keluar
                        END
                    "));

                $stockprod[] = [
                    'stock_akhir_' . $listwh->code_data => $stock_akhir
                ];
            }

            $results[] = [
                'stock_prod' => $stockprod,
            ];
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function savepenyesuaianstock($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json( ['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menugudang')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','penyesuaianstockbarang')->first();            
            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $list_gudang = Gudang::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
            $getdata = Barang::where('id', $request->code_barang)->first();

            if($getdata){            
                $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData = $time."".$otp;
                $newCodeData = ltrim($newCodeData, '0');
                $no_data = 0;
                foreach($list_gudang as $keywh => $listwh){

                    if($request->get('stock_awal_'.$listwh->code_data) != $request->get('stock_akhir_'.$listwh->code_data)){
                        $no_data++ ;
                        $uuid_so = Str::uuid();
                        $savedata = PenyesuaianStock::create([
                            'id' => $uuid_so,
                            'code_data' => $newCodeData,
                            // 'code_transaksi' => $request->get('code_data').'-'.$no_data,                            
                            'code_transaksi' => $request->get('code_data'),
                            'tanggal_transaksi' => $tgl_transaksi,
                            'stock_awal' => $request->get('stock_awal_'.$listwh->code_data),
                            'stock_penyesuaian' => $request->get('selisih_stock_'.$listwh->code_data),
                            'stock_akhir' => $request->get('stock_akhir_'.$listwh->code_data),
                            'keterangan' => $request->get('keterangan'),
                            'kode_kantor' => $viewadmin->kode_kantor,
                            'kode_gudang' => $listwh->id,
                            'kode_barang' => $request->get('code_barang'),
                            'kode_user' => $viewadmin->id,
                        ]);
                         
                        if($request->get('stock_awal_'.$listwh->code_data) < $request->get('stock_akhir_'.$listwh->code_data)){                            
                            $uuid_stock = Str::uuid();
                            HistoryStock::create([  
                                'id' => $uuid_stock,
                                'code_data' => $newCodeData,
                                'nomor' => $request->get('code_data'),
                                'kode_barang' => $request->get('code_barang'),
                                'tanggal' => $tgl_transaksi,
                                'masuk' => $request->get('selisih_stock_'.$listwh->code_data),
                                'keluar' => 0,
                                'ket' => $request->get('keterangan'),
                                'kode_gudang' => $listwh->id,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);
                        }else{                          
                            $uuid_stock = Str::uuid();
                            HistoryStock::create([  
                                'id' => $uuid_stock,
                                'code_data' => $newCodeData,
                                'nomor' => $request->get('code_data'),
                                'kode_barang' => $request->get('code_barang'),
                                'tanggal' => $tgl_transaksi,
                                'masuk' => 0,
                                'keluar' => $request->get('selisih_stock_'.$listwh->code_data),
                                'ket' => $request->get('keterangan'),
                                'kode_gudang' => $listwh->id,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);
                        }
                    }
                }
                
                if($savedata){
                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Penyesuaian stock ['.$request->code_data.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
                }else{
                        return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                    }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        } 

    }

    public function historypenyesuaianstockbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','penyesuaianstockbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenyesuaianstockbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $dateyear = Carbon::now()->modify("-1 year")->format('Y-m-d') . ' 00:00:00';

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("+1 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $searchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($searchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($searchdate[1])->format('Y-m-d') . ' 23:59:59';
            }
            
            // $results['list'] = DB::table('db_penyesuaian_stock')
            //     ->join('db_barang', 'db_penyesuaian_stock.kode_barang', '=', 'db_barang.id')
            //     ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
            //     ->join('db_gudang', 'db_penyesuaian_stock.kode_gudang', '=', 'db_gudang.id')
            //     ->Where('db_penyesuaian_stock.kode_kantor',$viewadmin->kode_kantor)
            //     ->where('db_barang.type_produk','LIKE','Barang')
            //     ->whereBetween('db_penyesuaian_stock.tanggal_transaksi', [$datefilterstart, $datefilterend])
            //     ->where(function($query) use ($request) {
            //         $query->Where('db_penyesuaian_stock.code_data','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penyesuaian_stock.code_transaksi','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_gudang.nama','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penyesuaian_stock.keterangan','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penyesuaian_stock.tanggal_transaksi','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penyesuaian_stock.stock_penyesuaian','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_barang.nama','LIKE', '%'.$request->keysearch.'%');
            //     })
            //     ->orderBy('db_penyesuaian_stock.created_at', 'DESC')
            //     ->paginate($vd ? $vd : 20);

            // foreach($results['list'] as $key => $data){
            //     $results['detail_produk'][$data->kode_barang] = Barang::where('id',$data->kode_barang)->first();
            //     $results['satuan_prod'][$data->kode_barang] = Satuan::where('id',$results['detail_produk'][$data->kode_barang]->kode_satuan_default)->first();
            //     $results['user_input'][$data->id] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
            //     $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
            //     $results['detail_gudang'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang)->first();
            // }

            $results['list'] = DB::table('db_penyesuaian_stock')
                ->join('db_barang', 'db_penyesuaian_stock.kode_barang', '=', 'db_barang.id')
                ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
                ->join('db_gudang', 'db_penyesuaian_stock.kode_gudang', '=', 'db_gudang.id')
                ->join('db_users_web', 'db_penyesuaian_stock.kode_user', '=', 'db_users_web.id')
                ->join('db_kantor', 'db_penyesuaian_stock.kode_kantor', '=', 'db_kantor.id')
                ->where('db_penyesuaian_stock.kode_kantor', $viewadmin->kode_kantor)
                ->where('db_barang.type_produk', 'Barang')
                ->whereBetween('db_penyesuaian_stock.tanggal_transaksi', [$datefilterstart, $datefilterend])
                ->where(function ($query) use ($request) {
                    $query->where('db_penyesuaian_stock.code_data', 'LIKE', "%{$request->keysearch}%")
                        ->orWhere('db_penyesuaian_stock.code_transaksi', 'LIKE', "%{$request->keysearch}%")
                        ->orWhere('db_gudang.nama', 'LIKE', "%{$request->keysearch}%")
                        ->orWhere('db_penyesuaian_stock.keterangan', 'LIKE', "%{$request->keysearch}%")
                        ->orWhere('db_barang.nama', 'LIKE', "%{$request->keysearch}%");
                })
                ->select(
                    'db_penyesuaian_stock.*',
                    'db_barang.nama AS nama_barang',
                    'db_satuan_barang.nama AS satuan',
                    'db_gudang.nama AS nama_gudang',
                    'db_users_web.full_name AS user_input',
                    'db_kantor.kantor AS nama_kantor'
                )
                ->orderBy('db_penyesuaian_stock.created_at', 'DESC')
                ->paginate($vd ?: 20);


            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function updatenomorpenyesuaian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json( ['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menugudang')->first();
            $level_sub_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','penyesuaianstockbarang')->first();            
            if($level_menu->access_rights == 'No' OR $level_sub_menu->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
  
            try {DB::beginTransaction();
                $getData = PenyesuaianStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('kode_gudang', '5eae8dea-e50a-11eb-9b08-204747ab6caa')->where('tanggal_transaksi','2025-12-09')->orderBy('created_at', 'ASC')->get();   
                
                $counter = 1;

                foreach ($getData as $row) {
                    PenyesuaianStock::where('code_data', $row->code_data)
                        ->update([
                            'code_transaksi' => 'PS-2025.'.str_pad($counter, 7, '0', STR_PAD_LEFT),
                        ]);

                    HistoryStock::where('code_data', $row->code_data)
                        ->update([
                            'nomor' => 'PS-2025.'.str_pad($counter, 7, '0', STR_PAD_LEFT),
                        ]);
                        
                    $counter++;
                }

                $otpAct = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
                $newCodeData_activity = ltrim(Carbon::now()->format('Ymdhis') .$otpAct, '0');
                Activity::create([
                    'id'            => Str::uuid(),
                    'code_data'     => $newCodeData_activity,
                    'kode_user'     => $viewadmin->id ?? null,
                    'activity'      => 'Update Nomor Penyesuaian tanggal 9 Desember 2025',
                    'kode_kantor'   => $viewadmin->kode_kantor ?? null,
                ]);
                
                DB::commit();
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan ' . $e->getMessage(),'results' => $object]);
            }
        } 
    }
}