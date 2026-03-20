<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePenerimaanbarang
{
    public function listopsupplier($request)
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

    public function getcodepenerimaan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            // $datenow = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 00:00:00';
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor_penerimaan,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            // $datenow = Carbon::now()->modify("0 days")->format('Ymd');
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PNB-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function listoppenjualan($request)
    {
        $object = [];    
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
    
        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
    
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
    

    public function listoppembelian($request)
    {
        $object = [];    
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
    
        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        } else {
    
            $getdata = array();
            
            // Gunakan pagination untuk membatasi data yang diambil sekaligus
            $results['list'] = Pembelian::where('kode_user', '!=', null)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->where('nomor', 'LIKE', '%' . $request->term . '%')
                ->where('status_transaksi', 'Proses')
                ->orderBy('created_at', 'ASC')
                ->get();
                // ->paginate(20); // Batasi jumlah data, misal 20 per page
    
            // Mengambil semua nomor dan kode_kantor yang dibutuhkan sekaligus
            $nomor_list = $results['list']->pluck('nomor')->toArray();
            $code_data_list = $results['list']->pluck('code_data')->toArray();
            
            // Ambil data qty_pembelian dan qty_terima sekali untuk semua nomor
            $qty_pembelian = ListPembelian::whereIn('nomor', $nomor_list)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->select('nomor', DB::raw('SUM(jumlah_beli) as total_beli'))
                ->groupBy('nomor')
                ->get()
                ->pluck('total_beli', 'nomor')
                ->toArray();
    
            $qty_terima = ListPembelian::whereIn('nomor', $nomor_list)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->select('nomor', DB::raw('SUM(jumlah_terima) as total_terima'))
                ->groupBy('nomor')
                ->get()
                ->pluck('total_terima', 'nomor')
                ->toArray();
    
            // Ambil data supplier untuk semua code_data sekaligus
            $suppliers = Supplier::whereIn('id', $results['list']->pluck('kode_supplier')->toArray())
                ->get()
                ->keyBy('id')
                ->toArray();
    
            // Loop untuk membangun hasil data
            foreach ($results['list'] as $list) {
                $qty_beli = $qty_pembelian[$list->nomor] ?? 0;
                $qty_ter = $qty_terima[$list->nomor] ?? 0;
    
                // Hitung qty_ready
                $qty_ready[$list->code_data] = $qty_beli - $qty_ter;
    
                if ($qty_ready[$list->code_data] > 0) {
                    $getdata[] = [
                        'label' => $list->nomor,
                        'code_data' => $list->code_data
                    ];
                }
            }
    
            // Return response sebagai JSON
            return response()->json($getdata);
        }
    }
    

    public function detailoppembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{

            $results['detail'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();

            $results['detail_supplier'] = Supplier::where('id',$results['detail']->kode_supplier)->first();

            $results['detail_gudang'] = Gudang::where('id',$results['detail']->kode_gudang)->first();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function listprodpembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdatapo = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdatapo){ 

                $getdata['list_produk'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdatapo->nomor)->orderBy('created_at', 'ASC')->get(); 
                
                foreach($getdata['list_produk'] as $key => $list){                
                    $id = $list->id;
                    $id = str_replace('-','',$id);

                    $getdata['detail_produk_po'][$id] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->first();

                    $getdata['qty_pembelian_po'][$id] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_beli');

                    $getdata['qty_terima_po'][$id] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_terima');

                    $countrdo = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pembelian', $list->nomor)->count();

                    if($countrdo > 0){
                        // $get_rdo_po = ListPenerimaan::where('nomor_pembelian', $list->nomor)->groupBy('nomor_penerimaan','nomor_pembelian','kode_barang')->orderBy('created_at', 'DESC')->first();
                        
                        $get_rdo_po = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_pembelian', $list->nomor)->orderBy('created_at', 'DESC')->first();

                        $getdata['qty_pembelian_rdo'][$id] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $get_rdo_po->nomor_penerimaan)->where('nomor_pembelian', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_beli');

                        $getdata['qty_terima_rdo'][$id] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $get_rdo_po->nomor_penerimaan)->where('nomor_pembelian', $list->nomor)->where('kode_barang', $list->kode_barang)->where('id', $list->id)->sum('jumlah_terima'); 
                    }
                
                    $qty_pembelian_all = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_beli');
                    
                    $qty_terima_all = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_terima');

                    $getdata['qty_ready'][$id] = $qty_pembelian_all - $qty_terima_all;

                    $getdata['detail_produk'][$id] = Barang::where('id', $getdata['detail_produk_po'][$id]->kode_barang)->first();

                    $getdata['satuan_produk'][$id] = Satuan::where('id', $list->kode_satuan)->first();

                    // $getdata['detail_varian'][$id] = VarianProduk::select('nama_sub_varian','stok_akhir','sku_produk')->where('code_data', $list->kode_barang)->first();
                }

                return response()->json(['status_message' => 'success','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function savepenerimaan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenerimaanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpenerimaanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $id = $request->get('id');
            // $code_data = $request->get('code_data');
            $nomor_penerimaan = $request->get('code_transaksi');
            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');            
            // $nomor_pembelian = $request->get('code_pembelian');
            $keterangan = $request->get('keterangan');
            $kode_barang = $request->get('code_produk');
            $qty_beli = $request->get('qty_pembelian');
            $qty_terima_in = $request->get('qty');
            $kode_satuan = $request->get('kode_satuan');

            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_pembelian)->first();
            
            $nomor_pembelian = $getdata_pembelian->nomor;
            $kode_gudang = $getdata_pembelian->kode_gudang;     

            $get_data_po = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->first();
            $list_prod_po = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->orderBy('created_at', 'ASC')->get();
            $get_list_po = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->where('kode_barang', $kode_barang)->first();

            $getdata_supplier = Supplier::where('id',$get_data_po->kode_supplier)->first();          
            if($keterangan == null){
                $keterangan = 'Penerimaan barang ['.$nomor_pembelian.' - '.$getdata_supplier->nama.']';
            }

            $newCodeData = $get_data_po->code_data;

            $counttransaksi = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $nomor_penerimaan)->count();            
            if($counttransaksi == 0){
                $savedata = Penerimaan::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor_penerimaan' => $nomor_penerimaan,
                    'nomor_pembelian' => $nomor_pembelian,
                    'tanggal' =>  $tgl_transaksi,
                    'ket' => $keterangan,
                    'kode_gudang' => $get_data_po->kode_gudang,
                    'kode_kantor' => $get_data_po->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]);

                if($savedata){
                    foreach($list_prod_po as $key => $list){
                        $uuidrdo = Str::uuid();
                        $uuid_stock = Str::uuid();
                                
                        $qty_pembelian = $list->jumlah_beli - $list->jumlah_terima;

                        if($list->jumlah_beli != $list->jumlah_terima){
                            ListPenerimaan::create([                                    
                                // 'id' => $list->id,
                                'id' => $uuidrdo,
                                'code_data' => $newCodeData,
                                'nomor_penerimaan' => $nomor_penerimaan,
                                'nomor_pembelian' => $nomor_pembelian,
                                'tanggal' => $tgl_transaksi,
                                'kode_barang' => $list->kode_barang,
                                'jumlah_beli' => $qty_pembelian,
                                'jumlah_terima' => 0,
                                'kode_satuan' => $list->kode_satuan,
                                'kode_kantor' => $list->kode_kantor,
                                'kode_user' => $list->kode_user,
                            ]);
                        }
                    }
                    
                    $updateqty_penerimaan = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $nomor_penerimaan)->where('kode_barang', $kode_barang)->where('jumlah_beli', $qty_beli)->update(['jumlah_terima' => $qty_terima_in]);

                    if($updateqty_penerimaan){
                        $get_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->first();
                        $jumlah_terima = $get_listpembelian->jumlah_terima;
                        $jumlah_terima = $jumlah_terima + $qty_terima_in;

                        ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->update(['jumlah_terima' => $jumlah_terima]);

                        $getdata_satuan = Satuan::Where('id',$kode_satuan)->first();
                        $total_isi = $qty_terima_in * $getdata_satuan->isi;

                        $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penerimaan)->where('kode_barang', $kode_barang)->count();
                        if($count_historystock == 0){
                            HistoryStock::create([  
                                'id' => $uuid_stock,
                                'code_data' => $newCodeData,
                                'nomor' => $nomor_penerimaan,
                                'kode_barang' => $kode_barang,
                                'tanggal' => $tgl_transaksi,
                                'masuk' => $total_isi,
                                'keluar' => 0,
                                'ket' => $keterangan,
                                'kode_gudang' => $kode_gudang,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                            ]);
                        }else{
                            HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penerimaan)->where('kode_barang', $kode_barang)->update(['masuk' => $total_isi]);
                        }
                    }

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Penerimaan barang ['.$nomor_pembelian.' - '.$request->code_transaksi.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData,'nomor_penerimaan' => $nomor_penerimaan]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }

            }else{
                    
                $updateqty_penerimaan = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $nomor_penerimaan)->where('kode_barang', $kode_barang)->where('jumlah_beli', $qty_beli)->update(['jumlah_terima' => $qty_terima_in,]);

                if($updateqty_penerimaan){
                    $get_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->first();
                    $jumlah_terima = $get_listpembelian->jumlah_terima;
                    $jumlah_terima = $jumlah_terima + $qty_terima_in;

                    ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->update(['jumlah_terima' => $jumlah_terima]);

                    $getdata_barang = Barang::Where('id',$kode_barang)->first();
                    $getdata_satuan = Satuan::Where('id',$getdata_barang->kode_satuan)->first();
                    $total_isi = $qty_terima_in * $getdata_satuan->isi;

                    $count_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penerimaan)->where('kode_barang', $kode_barang)->count();
                    if($count_historystock == 0){                        
                        $uuid_stock = Str::uuid();
                        HistoryStock::create([  
                            'id' => $uuid_stock,
                            'code_data' => $newCodeData,
                            'nomor' => $nomor_penerimaan,
                            'kode_barang' => $kode_barang,
                            'tanggal' => $tgl_transaksi,
                            'masuk' => $total_isi,
                            'keluar' => 0,
                            'ket' => $keterangan,
                            'kode_gudang' => $kode_gudang,
                            'kode_kantor' => $viewadmin->kode_kantor,
                            'kode_user' => $viewadmin->id,
                        ]);
                    }else{
                        HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_penerimaan)->where('kode_barang', $kode_barang)->update(['masuk' => $total_isi]);
                    }
                }

                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData]);
            }
        }
    }

    public function viewpenerimaan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $request->nomor_penerimaan)->first();
            if($getdata['detail']){                 
                $getdata['detail_pembelian'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail']->nomor_pembelian)->first();
                
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                if(!$getdata['user_transaksi']){
                    $getdata['user_transaksi']['full_name'] = 'Belum Ditentukan';
                }

                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                
                $detail_cabang = Cabang::where('id', $getdata['detail_pembelian']->kode_cabang)->first();
                
                $getdata['detail_cabang'] = [
                    "nama_cabang"=>$detail_cabang->nama_cabang ?? null,
                    "alamat"=>$detail_cabang->alamat ?? null,
                    "nomor_pic"=>$detail_cabang->nomor_pic ?? null,
                ];

                $getdata['detail_gudang'] = Gudang::where('id', $getdata['detail']->kode_gudang)->first();

                $getdata['detail_supplier'] = Supplier::where('id', $getdata['detail_pembelian']->kode_supplier)->first();

                $getdata['list_produk_group'] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)
                    ->where('nomor_penerimaan', $getdata['detail']->nomor_penerimaan)
                    ->where('nomor_pembelian', $getdata['detail']->nomor_pembelian)
                    // ->where('jumlah_terima','>',0)
                    // ->groupBy('nomor_penerimaan','nomor_pembelian','kode_barang')
                    ->orderBy('created_at', 'ASC')->get();

                $getdata['list_produk'] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)
                ->where('nomor_penerimaan', $getdata['detail']->nomor_penerimaan)
                // ->where('jumlah_terima','>',0)
                ->orderBy('created_at', 'ASC')->get(); 
            
                foreach($getdata['list_produk'] as $key => $list){               
                    $id = $list->id;
                    $id = str_replace('-','',$id);
                    $getdata['detail_produk'][$id] = Barang::where('id', $list->kode_barang)->first();
                    $getdata['detail_satuan'][$id] = ListPembelian::where('nomor', $list->nomor_pembelian)->where('kode_barang', $list->kode_barang)->first();
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

    public function historypenerimaan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenerimaanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenerimaanbarang')->first();
            
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
            
            $results['list'] = DB::table('db_supplier')
                ->join('db_pembelian', 'db_supplier.id', '=', 'db_pembelian.kode_supplier')
                ->join('db_penerimaan_barang', 'db_pembelian.nomor', '=', 'db_penerimaan_barang.nomor_pembelian')
                ->join('db_gudang', 'db_penerimaan_barang.kode_gudang', '=', 'db_gudang.id')
                ->Where('db_penerimaan_barang.kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('db_penerimaan_barang.tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('db_penerimaan_barang.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barang.nomor_penerimaan','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barang.nomor_pembelian','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barang.ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barang.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_gudang.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_penerimaan_barang.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                
                $results['detail_pembelian'][$data->nomor_penerimaan] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor_pembelian)->first();
                
                $results['user_pembelian'][$data->nomor_penerimaan] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $results['detail_pembelian'][$data->nomor_penerimaan]->kode_user)->first();
                if(!$results['user_pembelian'][$data->nomor_penerimaan]){
                    $results['user_pembelian'][$data->nomor_penerimaan]['full_name'] = 'Belum Ditentukan';
                }
                
                $results['user_input'][$data->nomor_penerimaan] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                if(!$results['user_input'][$data->nomor_penerimaan]){
                    $results['user_input'][$data->nomor_penerimaan]['full_name'] = 'Belum Ditentukan';
                }

                $results['qty_penerimaan'][$data->nomor_penerimaan] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $data->nomor_penerimaan)->sum('jumlah_terima');

                $results['detail_perusahaan'][$data->nomor_penerimaan] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                if(!$results['detail_perusahaan'][$data->nomor_penerimaan]){
                    $results['detail_perusahaan'][$data->nomor_penerimaan]['Kantor'] = 'Belum Ditentukan';
                }
                
                $results['detail_cabang'][$data->nomor_penerimaan] = Cabang::select('nama_cabang')->where('id', $data->kode_cabang)->first();
                if(!$results['detail_cabang'][$data->nomor_penerimaan]){
                    $results['detail_cabang'][$data->nomor_penerimaan]['nama_cabang'] = 'Belum Ditentukan';
                }

                $results['detail_supplier'][$data->nomor_penerimaan] = Supplier::select('nama')->where('id', $data->kode_supplier)->first();
                if(!$results['detail_supplier'][$data->nomor_penerimaan]){
                    $results['detail_supplier'][$data->nomor_penerimaan]['nama'] = 'Belum Ditentukan';
                }

                $results['detail_gudang'][$data->nomor_penerimaan] = Gudang::select('nama')->where('id', $data->kode_gudang)->first();
                if(!$results['detail_gudang'][$data->nomor_penerimaan]){
                    $results['detail_gudang'][$data->nomor_penerimaan]['nama'] = 'Belum Ditentukan';
                }
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historypenerimaanitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenerimaanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenerimaanbarang')->first();
            
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
            
            $results['list'] = DB::table('db_supplier')
                ->join('db_pembelian', 'db_supplier.id', '=', 'db_pembelian.kode_supplier')
                ->join('db_penerimaan_barangd', 'db_pembelian.nomor', '=', 'db_penerimaan_barangd.nomor_pembelian')
                ->join('db_penerimaan_barang', 'db_penerimaan_barangd.nomor_pembelian', '=', 'db_penerimaan_barang.nomor_pembelian')
                ->join('db_gudang', 'db_penerimaan_barang.kode_gudang', '=', 'db_gudang.id')
                ->Where('db_penerimaan_barangd.kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('db_penerimaan_barangd.tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('db_penerimaan_barangd.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barangd.nomor_penerimaan','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barangd.nomor_pembelian','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penerimaan_barangd.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_gudang.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_penerimaan_barangd.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                
                $results['detail_pembelian'][$data->id] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor_pembelian)->first();
                
                $results['user_pembelian'][$data->id] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $results['detail_pembelian'][$data->id]->kode_user)->first();
                
                $results['user_input'][$data->id] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();

                $results['detail_barang'][$data->id] = Barang::select('nama')->where('id', $data->kode_barang)->first();

                $results['qty_penerimaan'][$data->id] = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $data->nomor_penerimaan)->where('kode_barang', $data->kode_barang)->where('jumlah_beli', $data->jumlah_beli)->first();

                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                
                $results['detail_cabang'][$data->id] = Cabang::select('nama_cabang')->where('id', $data->kode_cabang)->first();

                $results['detail_supplier'][$data->id] = Supplier::select('nama')->where('id', $data->kode_supplier)->first();

                $results['detail_gudang'][$data->id] = Gudang::select('nama')->where('id', $data->kode_gudang)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function deleterdobarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenerimaanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenerimaanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Penerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $request->cdpo)->first();
            $nomor_pembelian = $getdata->nomor_pembelian;
            if($getdata){
                $listprod = ListPenerimaan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $getdata->nomor_penerimaan)->get();

                foreach($listprod as $key => $list){
                    $kode_barang = $list->kode_barang;
                    $qty_beli = $list->jumlah_beli;                    
                    $qty_terima = $list->jumlah_terima;

                    // Update data pembelian  
                    $jumlah_terima = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->sum('jumlah_terima');
                    $qty_terima_in =  $jumlah_terima - $qty_terima;  

                    ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->where('kode_barang', $kode_barang)->update(['jumlah_terima' => $qty_terima_in]);
                }      

                // ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$nomor_pembelian)->update(['jumlah_terima' => 0]);            

                $getdata_listpenerimaan = ListPenerimaan::where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_penerimaan', $getdata->nomor_penerimaan)->get();
                foreach ($getdata_listpenerimaan as $data_listpenerimaan){
                    $data_listpenerimaan->delete();
                }         

                $getdata_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor_penerimaan)->get();
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
                        'activity' => 'Membatalkan penerimaan barang  ['.$getdata->nomor_penerimaan.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    
                    $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->first();
                    $qty_beli = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->sum('jumlah_beli');
                    $qty_terima = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->sum('jumlah_terima');

                    if($qty_beli == $qty_terima){
                        $status_transaksi = 'Finish';
                    }else{
                        $status_transaksi = 'Proses';
                    }

                    Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->update(['status_transaksi' => $status_transaksi]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function updaterdobarang($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenerimaanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenerimaanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            $nomor_pembelian = $getdata->nomor;
            if($getdata){                     
                $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->first();
                $qty_beli = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->sum('jumlah_beli');
                $qty_terima = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->sum('jumlah_terima');

                if($qty_beli == $qty_terima){
                    $status_transaksi = 'Finish';
                }else{
                    $status_transaksi = 'Proses';
                }

                $savedata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $nomor_pembelian)->update(['status_transaksi' => $status_transaksi,]);

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