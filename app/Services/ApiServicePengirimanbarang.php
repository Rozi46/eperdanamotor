<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePengirimanbarang
{
    public function listopcustomer($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results = Supplier::select('id','code_data','nama','no_telp','alamat')
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
    
    public function getcodepengiriman($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            // $datenow = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 00:00:00';
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor_pengiriman,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            // $datenow = Carbon::now()->modify("0 days")->format('Ymd');
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PGB-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function listoppenjualan(Request $request)
    {
        $object = array();
    
        // Validasi admin berdasarkan token
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
    
        if(!$viewadmin){
            return response()->json(['status_message' => 'failed','note' => 'Data tidak ditemukan','results' => $object]);
        } else {
    
            $getdata = array();
    
            // Batasi jumlah data dengan pagination untuk mencegah overload
            $results['list'] = Penjualan::where('kode_user', '!=', null)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->where('nomor', 'LIKE', '%' . $request->term . '%')
                ->where('status_transaksi', 'Proses')
                ->orderBy('created_at', 'ASC')
                ->get();
                // ->paginate(20); // Pagination dengan 20 data per page
    
            // Ambil nomor penjualan dari hasil list
            $nomor_list = $results['list']->pluck('nomor')->toArray();
    
            // Ambil data qty_penjualan dan qty_kirim sekaligus menggunakan whereIn
            $qty_penjualan = ListPenjualan::whereIn('nomor', $nomor_list)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->select('nomor', DB::raw('SUM(jumlah_jual) as total_jual'))
                ->groupBy('nomor')
                ->get()
                ->pluck('total_jual', 'nomor')
                ->toArray();
    
            $qty_kirim = ListPenjualan::whereIn('nomor', $nomor_list)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->select('nomor', DB::raw('SUM(jumlah_kirim) as total_kirim'))
                ->groupBy('nomor')
                ->get()
                ->pluck('total_kirim', 'nomor')
                ->toArray();
    
            // Ambil data customer sekali untuk semua penjualan dalam list
            $customers = Customer::whereIn('id', $results['list']->pluck('kode_customer')->toArray())
                ->get()
                ->keyBy('id')
                ->toArray();
    
            // Loop untuk memproses hasil list penjualan
            foreach ($results['list'] as $list) {
                $qty_jual = $qty_penjualan[$list->nomor] ?? 0;
                $qty_kir = $qty_kirim[$list->nomor] ?? 0;
    
                // Hitung qty_ready
                $qty_ready[$list->code_data] = $qty_jual - $qty_kir;
    
                if ($qty_ready[$list->code_data] > 0) {
                    $getdata[] = [
                        'label' => $list->nomor,
                        'code_data' => $list->code_data
                    ];
                }
            }
    
            // Return hasil response JSON
            return response()->json($getdata);
        }
    }

    public function detailoppenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{

            $results['detail'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();

            $results['detail_customer'] = Customer::where('id',$results['detail']->kode_customer)->first();

            $results['detail_gudang'] = Gudang::where('id',$results['detail']->kode_gudang)->first();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listprodpenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdataso = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdataso){ 

                $getdata['list_produk'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdataso->nomor)->orderBy('created_at', 'ASC')->get(); 
                
                foreach($getdata['list_produk'] as $key => $list){                
                    $id = $list->id;
                    $id = str_replace('-','',$id);

                    $getdata['detail_produk_so'][$id] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->first();

                    $getdata['qty_penjualan_so'][$id] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_jual');

                    $getdata['qty_kirim_so'][$id] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_kirim');

                    $countrdo = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penjualan', $list->nomor)->count();

                    if($countrdo > 0){                        
                        $get_rdo_so = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penjualan', $list->nomor)->orderBy('created_at', 'DESC')->first();

                        $getdata['qty_pembelian_rso'][$id] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $get_rdo_so->nomor_pengiriman)->where('nomor_penjualan', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_jual');

                        $getdata['qty_kirim_rdo'][$id] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $get_rdo_so->nomor_pengiriman)->where('nomor_penjualan', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_kirim'); 
                    }
                
                    $qty_penjualan_all = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_jual');
                    
                    $qty_kirim_all = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_kirim');

                    $getdata['qty_ready'][$id] = $qty_penjualan_all - $qty_kirim_all;

                    $getdata['detail_produk'][$id] = Barang::where('id', $getdata['detail_produk_so'][$id]->kode_barang)->first();

                    $getdata['satuan_produk'][$id] = Satuan::where('id', $list->kode_satuan)->first();
                }

                return response()->json(['status_message' => 'success','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function savepengiriman($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengirimanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpengirimanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $id = $request->get('id');
            $nomor_pengiriman = $request->get('code_transaksi');
            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');   
            $keterangan = $request->get('keterangan');
            $kode_barang = $request->get('code_produk');
            $qty_jual = $request->get('qty_penjualan');
            $qty_kirim_in = $request->get('qty');
            $kode_satuan = $request->get('kode_satuan');

            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_penjualan)->first();
            
            $nomor_penjualan = $getdata_penjualan->nomor;
            $kode_gudang = $getdata_penjualan->kode_gudang;       

            $get_data_so = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->first();
            $list_prod_so = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->orderBy('created_at', 'ASC')->get();
            $get_list_so = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->where('kode_barang', $kode_barang)->first();

            $getdata_customer = Customer::where('id',$get_data_so->kode_customer)->first();          
            if($keterangan == null){
                $keterangan = 'Pengiriman barang ['.$nomor_penjualan.' - '.$getdata_customer->nama.']';
            }

            $newCodeData = $get_data_so->code_data;

            $counttransaksi = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $nomor_pengiriman)->count();            
            if($counttransaksi == 0){
                $savedata = Pengiriman::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor_pengiriman' => $nomor_pengiriman,
                    'nomor_penjualan' => $nomor_penjualan,
                    'tanggal' =>  $tgl_transaksi,
                    'ket' => $keterangan,
                    'kode_gudang' => $get_data_so->kode_gudang,
                    'kode_kantor' => $get_data_so->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]);

                if($savedata){
                    foreach($list_prod_so as $key => $list){
                        $uuidrdo = Str::uuid();
                        $uuid_stock = Str::uuid();
                                
                        $qty_penjualan = $list->jumlah_jual - $list->jumlah_kirim;

                        if($list->jumlah_jual != $list->jumlah_kirim){
                            ListPengiriman::create([  
                                'id' => $uuidrdo,
                                'code_data' => $newCodeData,
                                'nomor_pengiriman' => $nomor_pengiriman,
                                'nomor_penjualan' => $nomor_penjualan,
                                'tanggal' => $tgl_transaksi,
                                'kode_barang' => $list->kode_barang,
                                'jumlah_jual' => $qty_penjualan,
                                'jumlah_kirim' => 0,
                                'kode_satuan' => $list->kode_satuan,
                                'kode_kantor' => $list->kode_kantor,
                                'kode_user' => $list->kode_user,
                            ]);
                        }
                    }
                    
                    $updateqty_pengiriman = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $nomor_pengiriman)->where('kode_barang', $kode_barang)->where('jumlah_jual', $qty_jual)->update(['jumlah_kirim' => $qty_kirim_in]);

                    if($updateqty_pengiriman){
                        $get_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->first();
                        $jumlah_kirim = $get_listpenjualan->jumlah_kirim;
                        $jumlah_kirim = $jumlah_kirim + $qty_kirim_in;

                        ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->update(['jumlah_kirim' => $jumlah_kirim]);

                        $getdata_satuan = Satuan::Where('id',$kode_satuan)->first();
                        $total_isi = $qty_kirim_in * $getdata_satuan->isi;

                        $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pengiriman)->where('kode_barang', $kode_barang)->count();
                        if($count_historystock == 0){
                            HistoryStock::create([  
                                'id' => $uuid_stock,
                                'code_data' => $newCodeData,
                                'nomor' => $nomor_pengiriman,
                                'kode_barang' => $kode_barang,
                                'tanggal' => $tgl_transaksi,
                                'masuk' => 0,
                                'keluar' => $total_isi,
                                'ket' => $keterangan,
                                'kode_gudang' => $kode_gudang,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);
                        }else{
                            HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)
                                ->where('nomor', $nomor_pengiriman)
                                ->where('kode_barang', $kode_barang)
                                ->update(['keluar' => $total_isi]);
                        }
                    }

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Pengiriman barang ['.$nomor_penjualan.' - '.$request->code_transaksi.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData,'nomor_pengiriman' => $nomor_pengiriman]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }

            }else{
                    
                $updateqty_pengiriman = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $nomor_pengiriman)->where('kode_barang', $kode_barang)->where('jumlah_jual', $qty_jual)->update(['jumlah_kirim' => $qty_kirim_in,]);

                if($updateqty_pengiriman){
                        $get_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->first();
                        $jumlah_kirim = $get_listpenjualan->jumlah_kirim;
                        $jumlah_kirim = $jumlah_kirim + $qty_kirim_in;

                        ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->update(['jumlah_kirim' => $jumlah_kirim]);

                    $getdata_barang = Barang::Where('id',$kode_barang)->first();
                    $getdata_satuan = Satuan::Where('id',$getdata_barang->kode_satuan)->first();
                    $total_isi = $qty_kirim_in * $getdata_satuan->isi;

                    $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pengiriman)->where('kode_barang', $kode_barang)->count();
                    if($count_historystock == 0){                        
                        $uuid_stock = Str::uuid();
                        HistoryStock::create([  
                            'id' => $uuid_stock,
                            'code_data' => $newCodeData,
                            'nomor' => $nomor_pengiriman,
                            'kode_barang' => $kode_barang,
                            'tanggal' => $tgl_transaksi,
                            'masuk' => 0,
                            'keluar' => $total_isi,
                            'ket' => $keterangan,
                            'kode_gudang' => $kode_gudang,
                            'kode_kantor' => $viewadmin->kode_kantor,
                            'kode_user' => $viewadmin->id,
                        ]);
                    }else{
                        HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pengiriman)->where('kode_barang', $kode_barang)->update(['keluar' => $total_isi]);
                    }
                }

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData]);
            }
        }
    }

    public function viewpengiriman($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $request->nomor_pengiriman)->first();
            if($getdata['detail']){ 
                
                $getdata['detail_penjualan'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail']->nomor_penjualan)->first();$getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                if(!$getdata['user_transaksi']){
                    $getdata['user_transaksi']['full_name'] = 'Belum Ditentukan';
                }
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                $getdata['detail_gudang'] = Gudang::where('id', $getdata['detail']->kode_gudang)->first();
                $getdata['detail_customer'] = Customer::where('id', $getdata['detail_penjualan']->kode_customer)->first();

                $getdata['list_produk_group'] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)
                    ->where('nomor_pengiriman', $getdata['detail']->nomor_pengiriman)
                    ->where('nomor_penjualan', $getdata['detail']->nomor_penjualan)
                    ->orderBy('created_at', 'ASC')->get();

                $getdata['list_produk'] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)
                ->where('nomor_pengiriman', $getdata['detail']->nomor_pengiriman)
                ->orderBy('created_at', 'ASC')->get(); 
            
                foreach($getdata['list_produk'] as $key => $list){               
                    $id = $list->id;
                    $id = str_replace('-','',$id);
                    $getdata['detail_produk'][$id] = Barang::where('id', $list->kode_barang)->first();
                    $getdata['detail_satuan'][$id] = ListPenjualan::where('nomor', $list->nomor_penjualan)->where('kode_barang', $list->kode_barang)->first();
                    $getdata['satuan_produk'][$id] = Satuan::where('id', $getdata['detail_satuan'][$id]->kode_satuan)->first();
                }

                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function historypengiriman($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengirimanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengirimanbarang')->first();
            
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
            
            $results['list'] = DB::table('db_customer')
                ->join('db_penjualan', 'db_customer.id', '=', 'db_penjualan.kode_customer')
                ->join('db_pengiriman_barang', 'db_penjualan.nomor', '=', 'db_pengiriman_barang.nomor_penjualan')
                ->join('db_gudang', 'db_pengiriman_barang.kode_gudang', '=', 'db_gudang.id')
                ->Where('db_pengiriman_barang.kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('db_pengiriman_barang.tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('db_pengiriman_barang.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barang.nomor_pengiriman','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barang.nomor_penjualan','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barang.ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barang.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_gudang.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_pengiriman_barang.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                $results['detail_penjualan'][$data->nomor_pengiriman] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor_penjualan)->first();
                $results['user_penjualan'][$data->nomor_pengiriman] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $results['detail_penjualan'][$data->nomor_pengiriman]->kode_user)->first();
                if(!$results['user_penjualan'][$data->nomor_pengiriman]){
                    $results['user_penjualan'][$data->nomor_pengiriman]['full_name'] = 'Belum Ditentukan';
                }
                $results['user_input'][$data->nomor_pengiriman] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                if(!$results['user_input'][$data->nomor_pengiriman]){
                    $results['user_input'][$data->nomor_pengiriman]['full_name'] = 'Belum Ditentukan';
                }
                $results['qty_pengiriman'][$data->nomor_pengiriman] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $data->nomor_pengiriman)->sum('jumlah_kirim');
                $results['detail_perusahaan'][$data->nomor_pengiriman] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_customer'][$data->nomor_pengiriman] = Customer::select('nama')->where('id', $data->kode_customer)->first();
                $results['detail_gudang'][$data->nomor_pengiriman] = Gudang::select('nama')->where('id', $data->kode_gudang)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historypengirimanitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengirimanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengirimanbarang')->first();
            
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
            
            $results['list'] = DB::table('db_customer')
                ->join('db_penjualan', 'db_customer.id', '=', 'db_penjualan.kode_customer')
                ->join('db_pengiriman_barangd', 'db_penjualan.nomor', '=', 'db_pengiriman_barangd.nomor_penjualan')
                ->join('db_pengiriman_barang', 'db_pengiriman_barangd.nomor_penjualan', '=', 'db_pengiriman_barang.nomor_penjualan')
                ->join('db_gudang', 'db_pengiriman_barang.kode_gudang', '=', 'db_gudang.id')
                ->Where('db_pengiriman_barangd.kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('db_pengiriman_barangd.tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('db_pengiriman_barangd.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barangd.nomor_pengiriman','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barangd.nomor_penjualan','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pengiriman_barangd.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_gudang.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_pengiriman_barangd.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                $results['detail_penjualan'][$data->id] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor_penjualan)->first();$results['user_penjualan'][$data->id] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $results['detail_penjualan'][$data->id]->kode_user)->first();
                $results['user_input'][$data->id] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_barang'][$data->id] = Barang::select('nama')->where('id', $data->kode_barang)->first();
                $results['qty_pengiriman'][$data->id] = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $data->nomor_pengiriman)->where('kode_barang', $data->kode_barang)->where('jumlah_jual', $data->jumlah_jual)->first();
                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_customer'][$data->id] = Customer::select('nama')->where('id', $data->kode_customer)->first();
                $results['detail_gudang'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function deletersobarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengirimanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengirimanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Pengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $request->cdpo)->first();
            $nomor_penjualan = $getdata->nomor_penjualan;
            if($getdata){
                $listprod = ListPengiriman::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $getdata->nomor_pengiriman)->get();

                foreach($listprod as $key => $list){
                    $kode_barang = $list->kode_barang;
                    $qty_jual = $list->jumlah_jual;                    
                    $qty_kirim = $list->jumlah_kirim;

                    // Update data penjualan  
                    $jumlah_kirim = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->sum('jumlah_kirim');
                    $qty_kirim_in =  $jumlah_kirim - $qty_kirim;  

                    ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_penjualan)->where('kode_barang', $kode_barang)->update(['jumlah_kirim' => $qty_kirim_in]);
                }             

                $getdata_listpengiriman = ListPengiriman::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pengiriman', $getdata->nomor_pengiriman)->get();
                foreach ($getdata_listpengiriman as $data_listpengiriman){
                    $data_listpengiriman->delete();
                }         

                $getdata_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor_pengiriman)->get();
                foreach ($getdata_historystock as $data_historystock){
                    $data_historystock->delete();
                }

                $Deldata = $getdata->delete(); 
                if($Deldata){
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData = $time."".$otp;
                    $newCodeData = ltrim($newCodeData, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Membatalkan pengiriman barang  ['.$getdata->nomor_pengiriman.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    
                    $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->first();
                    $qty_jual = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->sum('jumlah_jual');
                    $qty_kirim = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->sum('jumlah_kirim');

                    if($qty_jual == $qty_kirim){
                        $status_transaksi = 'Finish';
                    }else{
                        $status_transaksi = 'Proses';
                    }

                    Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->update(['status_transaksi' => $status_transaksi]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function updatersobarang($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengirimanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengirimanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            $nomor_penjualan = $getdata->nomor;
            if($getdata){                     
                $getdata_listpembelian = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->first();
                $qty_jual = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->sum('jumlah_jual');
                $qty_kirim = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->sum('jumlah_kirim');

                if($qty_jual == $qty_kirim){
                    $status_transaksi = 'Finish';
                }else{
                    $status_transaksi = 'Proses';
                }

                $savedata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penjualan)->update(['status_transaksi' => $status_transaksi,]);

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