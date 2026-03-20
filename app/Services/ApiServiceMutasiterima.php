<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceMutasiterima
{  
    public function getcodemutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }

        // Memformat tanggal dan tahun dari request
        $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');
        $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');

        // Mengambil data Mutasi Terima untuk kantor tertentu berdasarkan tahun
        $dataAll = MutasiTerima::where('kode_kantor', $viewadmin->kode_kantor)
                                ->whereYear('tanggal', $yearnow)
                                ->orderBy('created_at', 'DESC')
                                ->first();

        $countData = MutasiTerima::where('kode_kantor', $viewadmin->kode_kantor)
                                ->whereYear('tanggal', $yearnow)
                                ->count();

        // Menghasilkan nomor kode baru berdasarkan kondisi
        $newCodeData = $countData ? substr($dataAll->nomor, -7) + 1 : 1;
        $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);

        // Format kode final
        $newCodeData = "MTST-$yearnow.$newCodeData";

        return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
    }

    public function listopmutasi($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
    
        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        } 
         
        $getdata = [];
        $results['list'] = Mutasi::select('nomor', 'code_data')
            ->whereNotNull('kode_user')
            ->where([
                ['kode_kantor', '=', $viewadmin->kode_kantor],
                ['nomor', 'LIKE', '%' . $request->term . '%'],
                ['status_transaksi', '=', 'Proses']
            ])
            ->groupBy('nomor', 'code_data')
            ->orderBy('nomor', 'ASC')
            ->get();

        foreach ($results['list'] as $list) {
            $getdata[] = [
                'label' => $list->nomor,
                'code_data' => $list->code_data
            ];
        }

        return response()->json($getdata);
    }

    public function detailopmutasi($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }else{

            $results['detail'] = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();

            $results['detail_gudang_asal'] = Gudang::where('id',$results['detail']->kode_gudang_asal)->first();

            $results['detail_gudang_tujuan'] = Gudang::where('id',$results['detail']->kode_gudang_tujuan)->first();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listprodmutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdataMutasiKirim = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdataMutasiKirim){ 
                $getdata['list_produk'] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdataMutasiKirim->nomor)->orderBy('created_at', 'ASC')->get(); 
                
                foreach($getdata['list_produk'] as $key => $list){ 
                    $id = str_replace('-','',$list->id);

                    $getdata['detail_produk_mk'][$id] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->first();

                    $getdata['qty_mutasi_mk'][$id] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_mutasi');

                    $getdata['qty_kirim_mk'][$id] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_kirim');

                    $countMT = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->count();

                    if($countMT > 0){                        
                        $get_mt = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->orderBy('created_at', 'DESC')->first();

                        $getdata['qty_kirim_mt'][$id] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_kirim');

                        $getdata['qty_terima_mt'][$id] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_terima'); 
                    }
                
                    $qty_kirim_all = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->sum('jumlah_kirim');
                    
                    $qty_terima_all = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_terima');

                    $getdata['qty_ready'][$id] = $qty_kirim_all - $qty_terima_all;

                    $getdata['detail_produk'][$id] = Barang::where('id', $getdata['detail_produk_mk'][$id]->kode_barang)->first();

                    $getdata['satuan_produk'][$id] = Satuan::where('id', $list->kode_satuan)->first();
                }

                return response()->json(['status_message' => 'success','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        } 
    }

    public function viewmutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){    
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }

        $getdata['detail_mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->nomor_mutasi_terima)->first();
        if(!$getdata['detail_mutasi_terima']){             
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }

        $getdata['detail_mutasi'] = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail_mutasi_terima']->code_data)->first();    
        $getdata['detail_mutasi_kirim'] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail_mutasi_terima']->code_data)->first();
        
        $getdata['user_transaksi'] = User::where('id', $getdata['detail_mutasi_terima']->kode_user)->first();
        if(!$getdata['user_transaksi']){
            $getdata['user_transaksi']['full_name'] = 'Belum Ditentukan';
        }

        $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail_mutasi_terima']->kode_kantor)->first();
        $getdata['detail_gudang_asal'] = Gudang::where('id', $getdata['detail_mutasi_terima']->kode_gudang_asal)->first();
        $getdata['detail_gudang_tujuan'] = Gudang::where('id', $getdata['detail_mutasi_terima']->kode_gudang_tujuan)->first();

        $getdata['list_produk'] = Mutasiterima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail_mutasi_terima']->nomor)->orderBy('created_at', 'ASC')->get(); 

        $getdata['count_prod_mt'] = Mutasiterima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',  $getdata['detail_mutasi_terima']->nomor)->sum('jumlah_terima');
        
        foreach($getdata['list_produk'] as $key => $list){    
            $id = str_replace('-','',$list->id);
            $getdata['detail_produk'][$id] = Barang::where('id', $list->kode_barang)->first();
            $getdata['detail_satuan'][$id] = MutasiTerima::where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->first();
            $getdata['satuan_produk'][$id] = Satuan::where('id', $getdata['detail_satuan'][$id]->kode_satuan)->first();
        }

        return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);        
    
    }

    public function savemutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }
            
        $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasiterima')->first();
        $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputmutasiterima')->first();
        
        if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
            return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
        }

        $id = $request->get('id');
        $code_data = $request->get('code_data');
        $nomor_mutasi_terima = $request->get('code_transaksi');
        $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');            
        $nomor_mutasi_kirim = $request->get('no_mutasi_kirim');
        $keterangan = $request->get('keterangan');
        $kode_barang = $request->get('code_produk');
        $qty_mutasi_kirim = $request->get('qty_mutasi');
        $kode_satuan = $request->get('kode_satuan');
        $qty_mutasi_terima = $request->get('qty');

        $getdata['mutasi_kirim'] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $code_data)->first();
        if($getdata['mutasi_kirim']){
            $kode_gudang_asal = $getdata['mutasi_kirim']->kode_gudang_asal;
            $kode_gudang_tujuan = $getdata['mutasi_kirim']->kode_gudang_tujuan;

            $getdata['gudang_asal'] = Gudang::where('id', $kode_gudang_asal)->first();
            $getdata['gudang_tujuan'] = Gudang::where('id', $kode_gudang_tujuan)->first();
        }
                
        if($keterangan == null){
            $keterangan = 'Mutasi terima barang dari '.$getdata['gudang_asal']->nama.' ke '.$getdata['gudang_tujuan']->nama.' ';
        }

        $list_prod['mutasi_kirim'] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $code_data)->get();

        $counttransaksi = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->count();         

        if($counttransaksi == 0){ 
            foreach($list_prod['mutasi_kirim'] as $key => $list){ 
                $uuid = Str::uuid();        
                $savedata = MutasiTerima::create([                    
                    'id' => Str::uuid(),
                    'code_data' => $code_data,
                    'nomor' => $nomor_mutasi_terima,
                    'tanggal' => $tgl_transaksi,
                    'ket' => $keterangan,
                    'kode_barang' => $list->kode_barang,
                    'jumlah_kirim' => $list->jumlah_kirim,
                    'jumlah_terima' => 0,
                    'kode_satuan' => $list->kode_satuan,
                    'kode_gudang_asal' => $list->kode_gudang_asal,
                    'kode_gudang_tujuan' => $list->kode_gudang_tujuan,
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]); 
            }  
                    
            $updateqty['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->where('kode_barang', $kode_barang)->where('jumlah_kirim', $qty_mutasi_kirim)->update(['jumlah_terima' => $qty_mutasi_terima]);
        }else{                    
            $updateqty['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->where('kode_barang', $kode_barang)->where('jumlah_kirim', $qty_mutasi_kirim)->update(['jumlah_terima' => $qty_mutasi_terima]);
        }

        if($updateqty['mutasi_terima']){ 
            return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object,'code' => $nomor_mutasi_terima]);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
        }
    }

    public function historymutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasiterima')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasiterima')->first();
            
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

            $results['list'] = DB::table('db_mutasi_terima')
                ->select(DB::raw('MAX(id) as id'), 'nomor', 'code_data', 'ket', 'kode_kantor', 'kode_user', 'kode_gudang_asal', 'kode_gudang_tujuan', DB::raw('MAX(tanggal) as tanggal'))
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%');
                })
                ->groupBy('nomor','kode_kantor', 'code_data', 'ket','kode_user', 'kode_gudang_asal', 'kode_gudang_tujuan')
                ->orderBy('tanggal', 'DESC')
                ->orderBy('nomor', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->code_data] = User::select('code_data','full_name')->where('id', $data->kode_user)->first();
                $results['qty_kirim'][$data->code_data] = MutasiTerima::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->sum('jumlah_kirim');
                $results['qty_terima'][$data->code_data] = MutasiTerima::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->sum('jumlah_terima');
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('kode', $data->kode_kantor)->first();
                $results['detail_gudang_asal'][$data->code_data] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();
                $results['detail_gudang_tujuan'][$data->code_data] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();
                $results['detail_mutasi'][$data->code_data] = Mutasi::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $data->code_data)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historymutasiterimaitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasiterima')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasiterima')->first();
            
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

            $results['list'] = DB::table('db_mutasi_terima')
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('tanggal', 'DESC')
                ->orderBy('nomor', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->id] = User::select('code_data','full_name')->where('id', $data->kode_user)->first();
                $results['qty_kirim'][$data->id] = MutasiTerima::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->where('kode_barang', $data->kode_barang)->sum('jumlah_kirim');
                $results['qty_terima'][$data->id] = MutasiTerima::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->where('kode_barang', $data->kode_barang)->sum('jumlah_terima');
                $results['detail_barang'][$data->id] = Barang::select('nama')->where('id', $data->kode_barang)->first();
                $results['detail_satuan'][$data->id] = Satuan::select('nama')->where('id', $data->kode_satuan)->first();
                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('kode', $data->kode_kantor)->first();
                $results['detail_gudang_asal'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();
                $results['detail_gudang_tujuan'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang_tujuan)->first();
                $results['detail_mutasi'][$data->code_data] = Mutasi::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $data->code_data)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function deletemutasiterima($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }

        $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasiterima')->first();
        $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasiterima')->first();
        
        if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
            return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
        }
        
        $getdata['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->no_mutasi_terima)->first();
        $nomor_mutasi_terima = $getdata['mutasi_terima']->nomor;
        $code_data_mt = $getdata['mutasi_terima']->code_data;
        if(!$getdata['mutasi_terima']){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }
        
        $listprod['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['mutasi_terima']->nomor)->get();
        foreach($listprod['mutasi_terima'] as $key => $list){
            $Deldata =$list->delete();
        } 

        if(!$Deldata){
            return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
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
            'activity' => 'Membatalkan mutasi terima  ['.$nomor_mutasi_terima.']',
            'kode_kantor' => $viewadmin->kode_kantor,
        ]);

        Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $code_data_mt)->update(['status_transaksi' => 'Proses']);

        return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);     
    }

    public function updatemutasiterima($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        } 

        $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasiterima')->first();
        $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasiterima')->first();
        
        if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
            return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
        }
        
        $getdata['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
        $nomor_mutasi_terima = $getdata['mutasi_terima']->nomor;
        $newCodeData = $getdata['mutasi_terima']->code_data;
        if(!$getdata['mutasi_terima']){ 
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }     
        $qty_mutasi_kirim = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->sum('jumlah_kirim');
        $qty_nutasi_terima = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->sum('jumlah_terima');

        if($qty_mutasi_kirim == $qty_nutasi_terima){
            $status_transaksi = 'Finish';
        }else{
            $status_transaksi = 'Proses';
        }

        $savedata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->update(['status_transaksi' => $status_transaksi,]);

        if($savedata){
            $getdataAll['mutasi_terima'] = MutasiTerima::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
            foreach($getdataAll['mutasi_terima'] as $key => $list){
                $getdata_satuan = Satuan::Where('id',$list->kode_satuan)->first();
                $total_isi = $list->jumlah_terima * $getdata_satuan->isi;
    
                $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi_terima)->where('kode_barang', $list->kode_barang)->count();
                if($count_historystock == 0){
                    $uuid_stock = Str::uuid();
                    HistoryStock::create([  
                        'id' => $uuid_stock,
                        'code_data' => $list->code_data,
                        'nomor' => $list->nomor,
                        'kode_barang' => $list->kode_barang,
                        'tanggal' => $list->tanggal,
                        'masuk' => $total_isi,
                        'keluar' => 0,
                        'ket' => $list->ket,
                        'kode_gudang' => $list->kode_gudang_tujuan,
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);
                }else{
                    HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)
                        ->where('nomor', $nomor_mutasi_terima)
                        ->where('kode_barang', $list->kode_barang)
                        ->update(['masuk' => $total_isi]);
                }
            } 
    
            Activity::create([
                'id' => Str::uuid(),
                'code_data' => $newCodeData,
                'kode_user' => $viewadmin->id,
                'activity' => 'Mutasi terima barang ['.$nomor_mutasi_terima.']',
                'kode_kantor' => $viewadmin->kode_kantor,
            ]);

            return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
        }
    }
}