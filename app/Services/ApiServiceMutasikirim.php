<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceMutasikirim
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

    public function getcodemutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            // $datenow = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 00:00:00';
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();
            $dataAll = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            // $datenow = Carbon::now()->modify("0 days")->format('Ymd');
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "MTS-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }   

    public function savemutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputmutasikirim')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $nomor_mutasi = $request->get('code_transaksi');
            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');  
            $code_gudang_asal = $request->get('code_gudang_asal');
            $code_gudang_tujuan = $request->get('code_gudang_tujuan');
            $keterangan = $request->get('keterangan');
            $kode_barang = $request->get('code_produk');
            $qty = $request->get('qty');

            $getdata['gudang_asal'] = Gudang::where('id',$code_gudang_asal)->first(); 
            $getdata['gudang_tujuan'] = Gudang::where('id',$code_gudang_tujuan)->first();         
            if($keterangan == null){
                $keterangan = 'Mutasi dari '.$getdata['gudang_asal']->nama.' ke '.$getdata['gudang_tujuan']->nama.' ';
            }

            $getdata['barang'] = Barang::where('id', $kode_barang)->first();
            $counttransaksi = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->count();         
            $countprod = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->where('kode_barang', $kode_barang)->count(); 
            $countprod_nihil = Mutasi::where('kode_kantor', $viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->whereNull('kode_barang')->count();

            $validator = Validator::make($request->all(), [
                'code_transaksi' => 'required|string|max:200',
                'tgl_transaksi' => 'required|string|max:200',
                'code_gudang_asal' => 'required|string|max:200',
                'code_gudang_tujuan' => 'required|string|max:200',
                'code_produk' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $getdata['mutasi'] = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->first();
            if($getdata['mutasi']){                
                $newCodeData = $getdata['mutasi']->code_data; 
            }else{
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData = $time."".$otp;
                $newCodeData = ltrim($newCodeData, '0'); 
            }

            if($counttransaksi == 0){            
                $savedata = Mutasi::create([                    
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $nomor_mutasi,
                    'tanggal' => $tgl_transaksi,
                    'ket' => $keterangan,
                    'kode_barang' => $kode_barang,
                    'qty' => $qty,
                    'kode_satuan' => $getdata['barang']->kode_satuan,
                    'kode_gudang_asal' => $code_gudang_asal,
                    'kode_gudang_tujuan' => $code_gudang_tujuan,
                    'status_transaksi' => 'Input',
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]); 

                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Mutasi barang ['.$nomor_mutasi.']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);

            }else{
                if($countprod_nihil == 1){                      
                    $savedata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->whereNull('kode_barang')
                        ->update([
                            'kode_barang' => $kode_barang,
                            'qty' => $qty,
                            'kode_satuan' => $getdata['barang']->kode_satuan,
                    ]);
                }else{
                    if($countprod == 0){           
                        $savedata = Mutasi::create([                       
                            'id' => Str::uuid(),
                            'code_data' => $newCodeData,
                            'nomor' => $nomor_mutasi,
                            'tanggal' => $tgl_transaksi,
                            'ket' => $keterangan,
                            'kode_barang' => $kode_barang,
                            'qty' => $qty,
                            'kode_satuan' => $getdata['barang']->kode_satuan,
                            'kode_gudang_asal' => $code_gudang_asal,
                            'kode_gudang_tujuan' => $code_gudang_tujuan,
                            'status_transaksi' => 'Input',
                            'kode_kantor' => $viewadmin->kode_kantor,
                            'kode_user' => $viewadmin->id,
                        ]); 
                    }else{
                        $savedata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_mutasi)->where('kode_barang',$kode_barang)
                            ->update([
                                'ket' => $keterangan,
                                'qty' => $qty,
                                'kode_satuan' => $getdata['barang']->kode_satuan,
                        ]);
                    }
                }
            }

            if($savedata){ 
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object,'code' => $nomor_mutasi]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }
    }    

    public function viewmutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

        $detail = Mutasi::where('kode_kantor', $viewadmin->kode_kantor) ->where('nomor', $request->code_data)->first();

        if (!$detail) {
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }

        $getdata = [
            'detail' => $detail,
            'user_transaksi' => User::find($detail->kode_user),
            'detail_perusahaan' => Kantor::find($detail->kode_kantor),
            'detail_gudang_asal' => Gudang::find($detail->kode_gudang_asal),
            'detail_gudang_tujuan' => Gudang::find($detail->kode_gudang_tujuan),
        ];

        $getdata['list_produk'] = Mutasi::where('kode_kantor', $viewadmin->kode_kantor)->where('nomor', $detail->nomor)->orderBy('created_at', 'ASC')->get();

        foreach ($getdata['list_produk'] as $key => $list) {
            $kode_barang = $list->kode_barang;

            $getdata['qty_mutasi'] = Mutasi::where('kode_kantor', $viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('qty');

            $getdata['detail_produk'][$kode_barang] = Barang::find($kode_barang) ?? ['nama' => '', 'kode_satuan' => ''];
            
            if (isset($getdata['detail_produk'][$kode_barang]['kode_satuan'])) {
                $kode_satuan = $getdata['detail_produk'][$kode_barang]['kode_satuan'];
                $getdata['satuan_produk'][$kode_barang] = Satuan::find($kode_satuan) ?? '';
                $getdata['satuan_barang_pecahan'][$kode_barang] = $getdata['satuan_produk'][$kode_barang]
                    ? Satuan::where('kode_pecahan', $getdata['satuan_produk'][$kode_barang]->id)->orderBy('nama', 'ASC')->get()
                    : Satuan::orderBy('nama', 'ASC')->get();
            }

            $getdata['satuan_barang_produk'][$kode_barang] = Satuan::find($list->kode_satuan) ?? ['nama' => ''];
        }

        return response()->json(['status_message' => 'success', 'note' => 'Proses data berhasil','results' => $getdata]);
    }

    public function upqtymutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata_mutasikirim = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();

            if($getdata_mutasikirim){   
                Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata_mutasikirim->nomor)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'qty' => $request->qty,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function listsatuanmutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }           
            
            $getdata_mutasikirim = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();

            if($getdata_mutasikirim){   
                Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata_mutasikirim->nomor)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $request->satuan,
                ]);

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function updatemutasikirim($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata['mutasi'] = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();

            if($getdata){  
                $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
                $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
                $getdata['mutasi_kirim'] = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();
                $dataAll = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
                $countData = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();
    
                if($countData <> 0){
                    $newCodeData_nomor = substr($dataAll->nomor,-7);
                    $newCodeData_nomor = $newCodeData_nomor + 1;
                }else{
                    $newCodeData_nomor = 1;
                }
    
                $kantor = $viewadmin->kode_kantor;
                $newCodeData_nomor = str_pad($newCodeData_nomor, 7, "0", STR_PAD_LEFT);
                $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
                $newCodeData_nomor = "MTSK-".$datenow.'.'.$newCodeData_nomor;

                $list_mutasi = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['mutasi']->nomor)->get(); 

                foreach($list_mutasi as $key => $list){
                    $savedata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['mutasi']->nomor)
                        ->update([
                            'ket' => $request->get('keterangan'),
                            'status_transaksi' => 'Proses',
                        ]);
                        
                    $uuid_mts = Str::uuid();
                    MutasiKirim::create([                           
                        'id' => $uuid_mts,
                        'code_data' => $list->code_data,
                        'nomor' => $newCodeData_nomor,
                        'tanggal' => $list->tanggal,
                        'ket' => $list->ket,
                        'kode_barang' => $list->kode_barang,
                        'jumlah_mutasi' => $list->qty,
                        'jumlah_kirim' => $list->qty,
                        'kode_satuan' => $list->kode_satuan,
                        'kode_gudang_asal' => $list->kode_gudang_asal,
                        'kode_gudang_tujuan' => $list->kode_gudang_tujuan,
                        'kode_kantor' => $list->kode_kantor,
                        'kode_user' => $list->kode_user,
                    ]);

                    // Simpan history stock
                    $getdata_satuan = Satuan::Where('id',$list->kode_satuan)->first();
                    $total_isi = $list->qty * $getdata_satuan->isi;

                    $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->count();

                    if($count_historystock == 0){
                        $uuid_stock = Str::uuid();
                        HistoryStock::create([  
                            'id' => $uuid_stock,
                            'code_data' => $list->code_data,
                            'nomor' => $list->nomor,
                            'kode_barang' => $list->kode_barang,
                            'tanggal' => $list->tanggal,
                            'masuk' => 0,
                            'keluar' => $total_isi,
                            'ket' => $list->ket,
                            'kode_gudang' => $list->kode_gudang_asal,
                            'kode_kantor' => $viewadmin->kode_kantor,
                            'kode_user' => $viewadmin->id,
                        ]);
                    }else{
                        HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)
                            ->where('nomor', $list->nomor)
                            ->where('kode_barang', $list->kode_barang)
                            ->update(['keluar' => $total_isi]);
                    }
                }

                // if($savedata){ 
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
                // }else{
                //     return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                // }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function historymutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
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

            $results['list'] = DB::table('db_mutasi')
                ->select(DB::raw('MAX(id) as id'), 'nomor', 'kode_kantor', 'code_data', 'status_transaksi', 'ket', 'kode_user', 'kode_gudang_asal', 'kode_gudang_tujuan', DB::raw('MAX(tanggal) as tanggal'))
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('status_transaksi','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%');
                })
                ->groupBy('nomor','kode_kantor', 'code_data', 'status_transaksi', 'ket','kode_user', 'kode_gudang_asal', 'kode_gudang_tujuan')
                ->orderBy('tanggal', 'DESC')
                ->orderBy('nomor', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->code_data] = User::select('code_data','full_name')->where('id', $data->kode_user)->first();
                if(!$results['user_input'][$data->code_data]){
                    $results['user_input'][$data->code_data]['full_name'] = 'Belum Ditentukan';
                }

                $results['qty_kirim'][$data->code_data] = Mutasi::where('kode_kantor', $data->kode_kantor)->where('nomor', $data->nomor)->sum('qty');

                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('kode', $data->kode_kantor)->first();

                $results['detail_gudang_asal'][$data->code_data] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();

                $results['detail_gudang_tujuan'][$data->code_data] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historymutasikirimitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
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

            $results['list'] = DB::table('db_mutasi')
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('status_transaksi','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('tanggal', 'DESC')
                ->orderBy('nomor', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->id] = User::select('code_data','full_name')->where('id', $data->kode_user)->first();
                if(!$results['user_input'][$data->id]){
                    $results['user_input'][$data->id]['full_name'] = 'Belum Ditentukan';
                }

                $results['qty_kirim'][$data->id] = Mutasi::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->where('kode_barang', $data->kode_barang)->sum('qty');

                $results['detail_barang'][$data->id] = Barang::select('nama')->where('id', $data->kode_barang)->first();

                $results['detail_satuan'][$data->id] = Satuan::select('nama')->where('id', $data->kode_satuan)->first();

                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('kode', $data->kode_kantor)->first();

                $results['detail_gudang_asal'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang_asal)->first();

                $results['detail_gudang_tujuan'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang_tujuan)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function deleteprodmutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $getdata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->where('id', $request->id)->first();
            if($getdata){
                $count_produk = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->count();
                if($count_produk == 1){
                    Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)
                        ->update([
                            'kode_barang'=> null,
                            'qty'=> null,
                            'kode_satuan'=> null,
                    ]);
                }else{
                    $getdatatransaksi = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $request->id)->first();
                    $DelData = $getdatatransaksi->delete();
                }
                return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function deletemutasikirim($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','mutasikirim')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historymutasikirim')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();
            if($getdata){
                $listprod_mutasi = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->get();

                foreach($listprod_mutasi as $key => $list){
                    $getdata_prod = Mutasi::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->first();
                    $Deldata_prod = $getdata_prod->delete();
                }

                $listprod_mutasiKirim = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->get();
                if($listprod_mutasiKirim){
                    foreach($listprod_mutasiKirim as $key => $listMutasiKirim){
                        $getdata_prodMutasiKirim = MutasiKirim::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->first();
                        $Deldata_prodMutasiKirim = $getdata_prodMutasiKirim->delete();
                    }  
                }
                
                if($Deldata_prodMutasiKirim){
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData = $time."".$otp;
                    $newCodeData = ltrim($newCodeData, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Membatalkan mutasi kirim  ['.$getdata->nomor.']',
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