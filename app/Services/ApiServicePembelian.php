<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePembelian
{
    // Auto Complete
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

    // Pembelian Barang
    public function getcodepembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $tanggal = Carbon::parse($request->tgl_transaksi);

            $datenow = $tanggal->format('Y-m-d');
            $yearnow = $tanggal->format('Y');

            // $datenow = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 00:00:00';
            // $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');
            // $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

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
            $newCodeData = "PB-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_transaksi' => 'Proses']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function saveprodpembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $code_data = $request->get('code_data');
            $code_transaksi = $request->get('code_transaksi');
            $code_produk = $request->get('code_produk');


            $counttransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count();         
            $countprod = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang', $code_produk)->count();

            $validator = Validator::make($request->all(), [
                'code_transaksi' => 'required|string|max:200',
                'tgl_transaksi' => 'required|string|max:200',
                'code_cabang' => 'required|string|max:200',
                'code_gudang' => 'required|string|max:200',
                'code_supplier' => 'required|string|max:200',
                'code_produk' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d');     
            
            $getdatabarang = Barang::where('id', $request->code_produk)->first();
            $getdatasupplier = Supplier::where('id', $request->code_supplier)->first();

            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();
            if($getdata_pembelian){                
                $newCodeData = $getdata_pembelian->code_data; 
            }else{
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData = $time."".$otp;
                $newCodeData = ltrim($newCodeData, '0'); 
            }

            if($tgl_transaksi < '2022-04-01'){
                $get_setting_nilai_pajak = 10;
            }else{
                $get_setting_nilai_pajak = 11;
            }

            // $harga_satuan = $getdatabarang->harga_beli * $request->get('qty');
            $jenis_ppn = $request->jenis_ppn;

            // if($jenis_ppn == 'Include'){
            //     $status_ppn = 'Ya';
            //     $nilai_ppn = $harga_satuan - (($harga_satuan * 100) / ($get_setting_nilai_pajak + 100));
            //     $nilai_ppn = Round($nilai_ppn);
            // }else{
            //     $status_ppn = 'Ya';
            //     $nilai_ppn = ($get_setting_nilai_pajak / 100) * $harga_satuan;
            //     $nilai_ppn = Round($nilai_ppn);
            // } 

            $status_ppn = 'Ya';
            $nilai_ppn = 0;

            if($counttransaksi == 0){            
                $savedata = ListPembelian::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $request->get('code_transaksi'),
                    'tanggal' => $tgl_transaksi,
                    'kode_barang' => $getdatabarang->id,
                    'jumlah_beli' => $request->get('qty'),
                    'jumlah_terima' => 0,
                    'jumlah_retur' => 0,
                    'kode_satuan' => $getdatabarang->kode_satuan,
                    'harga' => $getdatabarang->harga_beli,
                    'total_harga' => $getdatabarang->harga_beli,
                    'diskon_persen' => 0,
                    'diskon_harga' => 0,
                    'diskon_persen2' => 0,
                    'diskon_harga2' => 0,
                    'harga_netto' => $getdatabarang->harga_beli,
                    'status_ppn' => $status_ppn,
                    'ppn' => $nilai_ppn,
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                    'kode_cabang' => $request->get('code_cabang'),
                ]); 

                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                if($gettransaksi){
                    $diskon_persen = $gettransaksi->diskon_persen;
                    $diskon_harga = $gettransaksi->diskon_harga;
                    $diskonCash_persen = $gettransaksi->diskonCash_persen;
                    $diskonCash_harga = $gettransaksi->diskonCash_harga;
                    $biaya_lain = $gettransaksi->biaya_kirim;

                    // if($diskon_persen == 0){
                    //     $diskon_harga = 0;
                    // }else{
                    //     $diskon_harga = ($diskon_persen/100) * $total;
                    // } 

                    // $diskonCash_persen = $gettransaksi->diskonCash_persen;
                    // if($diskonCash_persen == 0){
                    //     $diskonCash_harga = 0;
                    // }else{
                    //     $diskonCash_harga = ($diskonCash_persen/100) * $total;
                    // }   

                }else{
                    $diskon_persen = 0;
                    $diskon_harga = 0;
                    $diskonCash_persen = 0;
                    $diskonCash_harga = 0;
                    $biaya_lain = 0;
                }   

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                }                                  
                                    
                $ket = $request->get('keterangan');
                if ($ket == ''){
                    $ket = "Beli dari ".$getdatasupplier->nama;
                }

                Pembelian::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'kode_supplier' => $request->get('code_supplier'),
                    'ket' => $ket,
                    'jenis_pembelian' => 'Kredit',
                    'sub_total' => $sub_total,
                    'jenis_ppn' => $request->jenis_ppn,
                    'ppn' => $nilai_ppn,
                    'total' => $total,
                    'diskon_persen' => $diskon_persen,
                    'diskon_harga' => $diskon_harga,
                    'diskonCash_persen' => $diskonCash_persen,
                    'diskonCash_harga' => $diskonCash_harga,
                    'biaya_kirim' => $biaya_lain,
                    'grand_total' => $grand_total,
                    'status_transaksi' => 'Proses',
                    'kode_gudang' => $request->get('code_gudang'),
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                    'kode_cabang' => $request->get('code_cabang'),                         
                ]);
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Pembelian barang ['.$code_transaksi.']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);

            }else{
                if($countprod == 0){              
                    $savedata = ListPembelian::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor' => $request->get('code_transaksi'),
                        'tanggal' => $tgl_transaksi,
                        'kode_barang' => $getdatabarang->id,
                        'jumlah_beli' => $request->get('qty'),
                        'jumlah_terima' => 0,
                        'jumlah_retur' => 0,
                        'kode_satuan' => $getdatabarang->kode_satuan,
                        'harga' => $getdatabarang->harga_beli,
                        'total_harga' => $getdatabarang->harga_beli,
                        'diskon_persen' => 0,
                        'diskon_harga' => 0,
                        'diskon_persen2' => 0,
                        'diskon_harga2' => 0,
                        'harga_netto' => $getdatabarang->harga_beli,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                        'kode_cabang' => $request->get('code_cabang'),
                    ]);
                }else{
                    $countprodgift = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga',0)->where('kode_barang', $code_produk)->count();
                    if($countprodgift > 0){
                        $countprodall = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)->count();
                        if($countprodall == 0){         
                            $savedata = ListPembelian::create([
                                'id' => Str::uuid(),
                                'code_data' => $newCodeData,
                                'nomor' => $request->get('code_transaksi'),
                                'tanggal' => $tgl_transaksi,
                                'kode_barang' => $getdatabarang->id,
                                'jumlah_beli' => $request->get('qty'),
                                'jumlah_terima' => 0,
                                'jumlah_retur' => 0,
                                'kode_satuan' => $getdatabarang->kode_satuan,
                                'harga' => $getdatabarang->harga_beli,
                                'total_harga' => $getdatabarang->harga_beli,
                                'diskon_persen' => 0,
                                'diskon_harga' => 0,
                                'diskon_persen2' => 0,
                                'diskon_harga2' => 0,
                                'harga_netto' => $getdatabarang->harga_beli,
                                'status_ppn' => $status_ppn,
                                'ppn' => $nilai_ppn,
                                'kode_kantor' => $viewadmin->kode_kantor,
                                'kode_user' => $viewadmin->id,
                                'kode_cabang' => $request->get('code_cabang'),
                            ]);
                        }else{
                            $getprodtransaksi = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)->first();

                            $kode_satuan = $getprodtransaksi->kode_satuan;  
                            $harga_beli = $getprodtransaksi->harga;                  
                            $qty_beli = $getprodtransaksi->jumlah_beli + $request->get('qty');            
            
                            $diskon_persen = $getprodtransaksi->diskon_persen;
                            $diskon_harga = $harga_beli * ($diskon_persen/100);
            
                            $diskon_persen2 = $getprodtransaksi->diskon_persen2;
                            $diskon_harga2 = ($harga_beli - $diskon_harga) * ($diskon_persen2/100);
            
                            $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2;
                            $total_harga = $qty_beli * $harga_netto;
            
                            $status_ppn = 'Ya'; 
                            $nilai_ppn = 0;
                            
                            $savedata = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('harga','!=',0)->where('kode_barang', $code_produk)
                                ->update([
                                    'kode_satuan' => $kode_satuan,
                                    'harga' => $harga_beli,
                                    'jumlah_beli' => $qty_beli,
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
                        $getprodtransaksi = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang', $code_produk)->first();

                        $kode_satuan = $getprodtransaksi->kode_satuan;  
                        $harga_beli = $getprodtransaksi->harga;                  
                        $qty_beli = $getprodtransaksi->jumlah_beli + $request->get('qty');            
        
                        $diskon_persen = $getprodtransaksi->diskon_persen;
                        $diskon_harga = $harga_beli * ($diskon_persen/100);
        
                        $diskon_persen2 = $getprodtransaksi->diskon_persen2;
                        $diskon_harga2 = ($harga_beli - $diskon_harga) * ($diskon_persen2/100);
        
                        $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2;
                        $total_harga = $qty_beli * $harga_netto;
        
                        $status_ppn = 'Ya'; 
                        $nilai_ppn = 0;
                        
                        $savedata = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang',$getdatabarang->id)
                            ->update([
                                'kode_satuan' => $kode_satuan,
                                'harga' => $harga_beli,
                                'jumlah_beli' => $qty_beli,
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
                $counttransaksi = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count();
                    $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                    // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('ppn');
                    $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                    if($gettransaksi){
                        $diskon_persen = $gettransaksi->diskon_persen;
                        $diskon_harga = $gettransaksi->diskon_harga;
                        $diskonCash_persen = $gettransaksi->diskonCash_persen;
                        $diskonCash_harga = $gettransaksi->diskonCash_harga;
                        $biaya_lain = $gettransaksi->biaya_kirim; 
                     }else{
                        $diskon_persen = 0;
                        $diskon_harga = 0;
                        $diskonCash_persen = 0;
                        $diskonCash_harga = 0;
                        $biaya_lain = 0;
                    }      


                    if($jenis_ppn == 'Include'){
                        $grand_total = $total - $diskon_harga + $biaya_lain; 
                        $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                        $sub_total = Round($sub_total);

                        $nilai_ppn = $grand_total - $sub_total;
                        $nilai_ppn = Round($nilai_ppn);
                    }else{
                        $grand_total = $total - $diskon_harga + $biaya_lain; 
                        $sub_total = $grand_total;
                        $sub_total = Round($sub_total);

                        $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                        $nilai_ppn = Round($nilai_ppn);

                        $grand_total = $sub_total + $nilai_ppn; 
                    }     
    
                    Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$code_transaksi)
                        ->update([
                            'sub_total' => $sub_total,
                            'ppn' => $nilai_ppn,
                            'total' => $total,
                            'diskon_persen' => $diskon_persen,
                            'diskon_harga' => $diskon_harga,
                            'diskonCash_persen' => $diskonCash_persen,
                            'diskonCash_harga' => $diskonCash_harga,
                            'biaya_kirim' => $biaya_lain,
                            'grand_total' => $grand_total,
                    ]);
                // }
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $code_transaksi]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function viewpembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();
            if($getdata['detail']){                 
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                $getdata['detail_cabang'] = Cabang::where('id', $getdata['detail']->kode_cabang)->first();
                $getdata['detail_gudang'] = Gudang::where('id', $getdata['detail']->kode_gudang)->first();
                $getdata['detail_supplier'] = Supplier::where('id', $getdata['detail']->kode_supplier)->first();

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

                $qty_pembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->sum('jumlah_beli');

                $qty_terima = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->sum('jumlah_terima');

                $getdata['counttransaksi'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata['detail']->code_data)->count();
                
                    $getdata['list_produk'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata['detail']->nomor)->orderBy('created_at', 'ASC')->get(); 
            
                    foreach($getdata['list_produk'] as $key => $list){
                        $getdata['qty_pembelian'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_beli');

                        $getdata['qty_terima'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_terima');

                        $getdata['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                        
                        $getdata['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                        $getdata['satuan_produk'][$list->kode_barang] = Satuan::where('id', $getdata['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                        
                        $getdata['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $getdata['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get();                      
                    }

                // $qty_pembelian = ListPembelian::sum('jumlah_beli');
                $qty_pembelian = ListPembelian::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$getdata['detail']->code_data)->sum('jumlah_beli');

                // return ['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata];
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function listsatuanharga($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();
            if($getdata_listpembelian){                 
                $getdatasatuan = Satuan::Where('id',$request->harga_satuan)->first();  
                if($getdata_listpembelian->kode_satuan == $request->harga_satuan){
                    $harga_beli = $getdata_listpembelian->harga;
                }elseif($getdatasatuan->isi == 1){ 
                    $getdatasatuan1 = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                    $harga_beli = $getdata_listpembelian->harga / $getdatasatuan1->isi;
                }else{
                    $getdatasatuan1 = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                    $harga_beli = $getdatasatuan->isi * $getdata_listpembelian->harga;
                }   
                
                $qty_beli = $getdata_listpembelian->jumlah_beli;
                
                // $harga_beli = str_replace(",",".",$request->get('harga'));

                // if($getdata->harga == 0){
                //     $counttransaksi = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->where('kode_barang', $getdata->kode_barang)->count();
                //     if($counttransaksi > 1){
                //         $qty_pembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->where('kode_barang', $getdata->kode_barang)->sum('jumlah_beli');

                //         $getdatapembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->where('kode_barang', $getdata->kode_barang)->where('harga', '!=',0.0000)->first();

                //         // if($getdatapembelian->tipe_diskon == 'Persen'){
                //         //     $harga_diskon = $harga_beli - ($harga_beli * $getdatapembelian->nilai_diskon/100);
                //         // }elseif($getdatapembelian->tipe_diskon == 'Jumlah'){
                //             // $harga_diskon = $harga_beli - $getdatapembelian->diskon_harga;
                //         // }else{
                //         //     $harga_diskon = $harga_beli;
                //         // }

                //         $diskon_persen = $getdatapembelian->diskon_persen;
                //         $diskon_harga = $getdatapembelian->diskon_harga;

                //         $harga_diskon =  ($harga_beli * $getdata->jumlah_beli) - $getdata->diskon_harga;

                //         if($getdatapembelian->status_ppn == 'Yes'){
                //             $nilai_pajak = ($harga_diskon * 10/100);
                //             // $total_harga = $harga_diskon + $nilai_pajak;
                //             $total_harga = $harga_diskon;
                //         }else{
                //             $nilai_pajak = 0;
                //             $total_harga = $harga_diskon;
                //         }
                        
                //         $harga_netto = $harga_beli - (($diskon_persen/100)*$harga_beli);

                //         // ListPembelian::where('code_data',$request->code_data)->delete();                        

                        
                        
                //         $diskon_persen = $getdatapembelian->diskon_persen;
                //         $diskon_persen2 = $getdatapembelian->diskon_persen2;

                //         if($tgl_transaksi < '2022-04-01'){
                //             $get_setting_nilai_pajak = 10;
                //         }else{
                //             $get_setting_nilai_pajak = 11;
                //         }

                //         $harga_satuan = $harga_beli * $request->get('qty');
                //         $jenis_ppn = $request->jenis_ppn;

                //         if($jenis_ppn == 'Include'){
                //             $status_ppn = 'Ya';
                //             $nilai_ppn = $harga_satuan - (($harga_satuan * 100) / ($get_setting_nilai_pajak + 100));
                //             $nilai_ppn = Round($nilai_ppn);
                //         }else{
                //             $status_ppn = 'Ya';
                //             $nilai_ppn = ($get_setting_nilai_pajak / 100) * $harga_satuan;
                //             $nilai_ppn = Round($nilai_ppn);
                //         } 
                        
                //         ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->where('code_data',$request->code_data)
                //             ->update([
                //                 'kode_satuan' => $request->harga_satuan,
                //                 'harga' => $harga_beli,
                //                 'jumlah_beli' => $qty_pembelian,
                //                 'diskon_persen' => $diskon_persen,
                //                 'diskon_harga' => $diskon_harga,
                //                 'diskon_persen2' => $diskon_persen2,
                //                 'diskon_harga2' => $diskon_harga2,
                //                 'harga_netto' => $harga_netto,
                //                 'total_harga' => $total_harga,
                //                 'status_ppn' => $getdatapembelian->status_ppn,
                //                 'ppn' => $nilai_pajak,

                                
                //                 'id' => Str::uuid(),
                //                 'code_data' => $newCodeData,
                //                 'nomor' => $request->get('code_transaksi'),
                //                 'tanggal' => $tgl_transaksi,
                //                 'kode_barang' => $getdatabarang->id,
                //                 'jumlah_beli' => $request->get('qty'),
                //                 'jumlah_terima' => 0,
                //                 'jumlah_retur' => 0,
                //                 'kode_satuan' => $getdatabarang->kode_satuan,
                //                 'harga' => $getdatabarang->harga_beli,
                //                 'total_harga' => $getdatabarang->harga_beli,
                //                 'diskon_persen' => 0,
                //                 'diskon_harga' => 0,
                //                 'diskon_persen2' => 0,
                //                 'diskon_harga2' => 0,
                //                 'harga_netto' => $getdatabarang->harga_beli,
                //                 'status_ppn' => $status_ppn,
                //                 'ppn' => $nilai_ppn,
                //                 'kode_kantor' => $viewadmin->kode_kantor,
                //                 'kode_user' => $viewadmin->id,
                //                 'kode_cabang' => $request->get('code_cabang'),
                //         ]);

                //     }else{
                //         // if($getdata->tipe_diskon == 'Persen'){
                //         //     $harga_diskon = $harga_beli - ($harga_beli * $getdata->nilai_diskon/100);
                //         // }elseif($getdata->tipe_diskon == 'Jumlah'){
                //             // $harga_diskon = $harga_beli - $getdata->diskon_harga;
                //         // }else{
                //         //     $harga_diskon = $harga_beli;
                //         // }
                        
                //         $qty_pembelian = $getdata->jumlah_beli;

                //         $diskon_persen = $getdata->diskon_persen;
                //         $diskon_harga = $getdata->diskon_harga;

                //         $harga_diskon =  ($harga_beli * $getdata->jumlah_beli) - $getdata->diskon_harga;

                //         if($getdata->status_ppn == 'Yes'){
                //             $nilai_pajak = ($harga_diskon * 10/100);
                //             // $total_harga = $harga_diskon + $nilai_pajak;
                //             $total_harga = $harga_diskon;
                //         }else{
                //             $nilai_pajak = 0;
                //             $total_harga = $harga_diskon;
                //         }
                        
                //         $harga_netto = $harga_beli - (($diskon_persen/100)*$harga_beli);

                //         ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata->nomor)->where('code_data',$request->code_data)
                //             ->update([
                //                 'kode_satuan' => $request->harga_satuan,
                //                 'harga' => $harga_beli,
                //                 'jumlah_beli' => $qty_pembelian,
                //                 'diskon_persen' => $diskon_persen,
                //                 'diskon_harga' => $harga_diskon,
                //                 'harga_netto' => $harga_netto,
                //                 'status_ppn' => $getdata->status_pajak,
                //                 'ppn' => $nilai_pajak,
                //                 'total_harga' => $total_harga,
                //         ]);
                //     }
                // }else{

                    // if($getdata->tipe_diskon == 'Persen'){
                    //     $harga_diskon = $harga_beli - ($harga_beli * $getdata->nilai_diskon/100);
                    // }elseif($getdata->tipe_diskon == 'Jumlah'){
                        // $harga_diskon = $harga_beli - $getdata->diskon_harga;
                    // }else{
                    //     $harga_diskon = $harga_beli;
                    // }
                    // $diskon_persen = $getdata->diskon_persen;
                    // $diskon_harga = $getdata->diskon_harga;

                    // $diskon_persen = $getdata_listpembelian->diskon_persen;
                    // $diskon_harga = ($harga_beli * $getdata_listpembelian->jumlah_beli) * ($getdata_listpembelian->diskon_persen/100);
                    // $harga_diskon =  ($harga_beli * $getdata_listpembelian->jumlah_beli) - $diskon_harga;

                    // if($getdata->status_ppn == 'Yes'){
                    //     $nilai_pajak = ($harga_diskon * (10/100));
                    //     // $total_harga = $harga_diskon + $nilai_pajak;
                    //     $total_harga = $harga_diskon;
                    // }else{
                    //     $nilai_pajak = 0;
                    //     $total_harga = $harga_diskon;
                    // }
                    
                    // $harga_netto = $harga_beli - (($diskon_persen/100)*$harga_beli);

                    

                    $diskon_persen = $getdata_listpembelian->diskon_persen;
                    $diskon_harga = $harga_beli * ($diskon_persen/100);

                    $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                    $diskon_harga2 = ($harga_beli - $diskon_harga) * ($diskon_persen2/100);

                    $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2;
                    $total_harga = $qty_beli * $harga_netto;

                    $tgl_transaksi = $getdata_listpembelian->tanggal;
                    $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                    if($tgl_transaksi < '2022-04-01'){
                        $get_setting_nilai_pajak = 10;
                    }else{
                        $get_setting_nilai_pajak = 11;
                    }

                    // $harga_satuan = $harga_netto * $qty_beli;
                    // $jenis_ppn = $getdata_pembelian->jenis_ppn;

                    // if($jenis_ppn == 'Include'){
                    //     $status_ppn = 'Ya';
                    //     $nilai_ppn = $harga_satuan - (($harga_satuan * 100) / ($get_setting_nilai_pajak + 100));
                    //     $nilai_ppn = Round($nilai_ppn);
                    // }else{
                    //     $status_ppn = 'Ya';
                    //     $nilai_ppn = ($get_setting_nilai_pajak / 100) * $harga_satuan;
                    //     $nilai_ppn = Round($nilai_ppn);
                    // } 

                    $jenis_ppn = $getdata_pembelian->jenis_ppn;
                    $status_ppn = 'Ya'; 
                    $nilai_ppn = 0;
                    
                    $code_transaksi = $getdata_listpembelian->nomor;
                    ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                        ->update([
                            'kode_satuan' => $request->harga_satuan,
                            'harga' => $harga_beli,
                            'jumlah_beli' => $qty_beli,
                            'diskon_persen' => $diskon_persen,
                            'diskon_harga' => $diskon_harga,
                            'diskon_persen2' => $diskon_persen2,
                            'diskon_harga2' => $diskon_harga2,
                            'harga_netto' => $harga_netto,
                            'total_harga' => $total_harga,
                            'status_ppn' => $status_ppn,
                            'ppn' => $nilai_ppn,
                    ]);
                
                    // if($harga_beli > 0){
                    //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                    //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                    //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                    //     ->update([                        
                    //         'harga_beli' => $hargabeli_item,
                    //     ]);
                    // }
                // }

                // $sub_total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');

                // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('ppn');
                
                // $total = $sub_total + $pajak;

                // $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();

                // $diskon_harga = $total * ( $gettransaksi->diskon_persen/100);
                // $total_diskon = $total - $diskon_harga;

                // // if($gettransaksi->tipe_diskon == 'Persen'){
                // //     $total_diskon = $total - ($total * $gettransaksi->diskon_faktur/100);
                // // }elseif($gettransaksi->tipe_diskon == 'Jumlah'){
                //     //  $total_diskon = $total - $gettransaksi->diskon_harga;
                // // }else{
                // //     $total_diskon = $total;
                // // }

                // $biaya_lain = $gettransaksi->biaya_kirim;

                // $grand_total = $total_diskon + $biaya_lain;


                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('ppn'); 
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

                // if($gettransaksi){
                //     $diskon_persen = $gettransaksi->diskon_persen;
                //     $diskon_harga = $gettransaksi->diskon_harga;
                //     $biaya_lain = $gettransaksi->biaya_kirim;
                // }else{
                //     $diskon_persen = 0;
                //     $diskon_harga = 0;
                //     $biaya_lain = 0;
                // }                    
                
                // if($jenis_ppn == 'Include'){
                //     $sub_total = $total - $pajak;
                //     $grand_total = $total - $diskon_harga + $biaya_lain;
                // }else{
                //     $sub_total = $total;
                //     $grand_total = $total + $pajak - $diskon_harga + $biaya_lain;
                // }  

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


                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                }   

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function uphargapembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                
                $harga_beli = $request->harga; 
                // Ganti tanda koma dengan titik
                $harga_beli = str_replace(',', '.', $harga_beli);        
                // Konversi ke tipe numerik (float)
                $harga_beli = floatval($harga_beli);  

                $qty_beli = $getdata_listpembelian->jumlah_beli;            

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $diskon_harga = $harga_beli * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                // $harga_satuan = $harga_netto * $qty_beli;
                // $jenis_ppn = $getdata_pembelian->jenis_ppn;

                // if($jenis_ppn == 'Include'){
                //     $status_ppn = 'Ya';
                //     $nilai_ppn = $harga_satuan - (($harga_satuan * 100) / ($get_setting_nilai_pajak + 100));
                //     $nilai_ppn = Round($nilai_ppn);
                // }else{
                //     $status_ppn = 'Ya';
                //     $nilai_ppn = ($get_setting_nilai_pajak / 100) * $harga_satuan;
                //     $nilai_ppn = Round($nilai_ppn);
                // } 

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                // if($harga_beli > 0){
                //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                //     ->update([                        
                //         'harga_beli' => $hargabeli_item,
                //     ]);
                // }
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('ppn'); 
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function upqtypembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $request->qty;            

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $diskon_harga = $harga_beli * ($diskon_persen/100);

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($diskon_persen2/100);

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                // $harga_satuan = $harga_netto * $qty_beli;
                // $jenis_ppn = $getdata_pembelian->jenis_ppn;

                // if($jenis_ppn == 'Include'){
                //     $status_ppn = 'Ya';
                //     $nilai_ppn = $harga_satuan - (($harga_satuan * 100) / ($get_setting_nilai_pajak + 100));
                //     $nilai_ppn = Round($nilai_ppn);
                // }else{
                //     $status_ppn = 'Ya';
                //     $nilai_ppn = ($get_setting_nilai_pajak / 100) * $harga_satuan;
                //     $nilai_ppn = Round($nilai_ppn);
                // } 

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                // if($harga_beli > 0){
                //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                //     ->update([                        
                //         'harga_beli' => $hargabeli_item,
                //     ]);
                // }
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('ppn'); 
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;        

                $diskon_persen = $request->nilai_diskon;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_beli * ($numeric_diskon_persen/100);

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($numeric_diskon_persen2/100);  

                $diskon_persen3 = $getdata_listpembelian->diskon_persen3;
                $numeric_diskon_persen3 = str_replace(',', '.', $diskon_persen3);
                $numeric_diskon_persen3 = (float) $numeric_diskon_persen3;
                $diskon_harga3 = ($harga_beli - $diskon_harga - $diskon_harga2) * ($numeric_diskon_persen3/100);  

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga2;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                // if($harga_beli > 0){
                //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                //     ->update([                        
                //         'harga_beli' => $hargabeli_item,
                //     ]);
                // }
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelian2($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;             

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_beli * ($numeric_diskon_persen/100);

                $diskon_persen2 = $request->nilai_diskon2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($numeric_diskon_persen2/100);

                $diskon_persen3 = $getdata_listpembelian->diskon_persen3;
                $numeric_diskon_persen3 = str_replace(',', '.', $diskon_persen3);
                $numeric_diskon_persen3 = (float) $numeric_diskon_persen3;
                $diskon_harga3 = ($harga_beli - $diskon_harga - $diskon_harga2) * ($numeric_diskon_persen3/100);  

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga3;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                // if($harga_beli > 0){
                //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                //     ->update([                        
                //         'harga_beli' => $hargabeli_item,
                //     ]);
                // }
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelian3($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;             

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_beli * ($numeric_diskon_persen/100);

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($numeric_diskon_persen2/100);

                $diskon_persen3 = $request->nilai_diskon3;
                $numeric_diskon_persen3 = str_replace(',', '.', $diskon_persen3);
                $numeric_diskon_persen3 = (float) $numeric_diskon_persen3;
                $diskon_harga3 = ($harga_beli - $diskon_harga - $diskon_harga2) * ($numeric_diskon_persen3/100);

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga3;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                
                // if($harga_beli > 0){
                //     $getdataSatuan = Satuan::Where('id',$getdata_listpembelian->kode_satuan)->first();
                //     $hargabeli_item = $harga_netto / $getdataSatuan->isi;
                //     Barang::Where('id',$getdata_listpembelian->kode_barang)
                //     ->update([                        
                //         'harga_beli' => $hargabeli_item,
                //     ]);
                // }
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelianharga($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;    
                
                $diskon_harga = str_replace(',', '.', $request->get('nilai_diskonharga'));
                $numeric_diskon_persen = round(($diskon_harga / $harga_beli) * 100, 2); //ambil sampai 2 angka dibelakang koma

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($numeric_diskon_persen2/100);

                $diskon_persen3 = $request->nilai_diskon3;
                $numeric_diskon_persen3 = str_replace(',', '.', $diskon_persen3);
                $numeric_diskon_persen3 = (float) $numeric_diskon_persen3;
                $diskon_harga3 = ($harga_beli - $diskon_harga - $diskon_harga2) * ($numeric_diskon_persen3/100);

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga3;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelianharga2($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;     

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_beli * ($numeric_diskon_persen/100);
                
                $diskon_harga2 = str_replace(',', '.', $request->get('nilai_diskonharga2'));
                $numeric_diskon_persen2 = round(($diskon_harga2 / ($harga_beli - $diskon_harga)) * 100, 2); //ambil sampai 2 angka dibelakang koma

                $diskon_persen3 = $getdata_listpembelian->diskon_persen3;
                $numeric_diskon_persen3 = str_replace(',', '.', $diskon_persen3);
                $numeric_diskon_persen3 = (float) $numeric_diskon_persen3;
                $diskon_harga3 = ($harga_beli - $diskon_harga - $diskon_harga2) * ($numeric_diskon_persen3/100);

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga3;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function updiscpembelianharga3($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpembelian = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpembelian->nomor)->first();

            if($getdata_listpembelian){  
                $kode_satuan = $getdata_listpembelian->kode_satuan;  
                $harga_beli = $getdata_listpembelian->harga;                  
                $qty_beli = $getdata_listpembelian->jumlah_beli;     

                $diskon_persen = $getdata_listpembelian->diskon_persen;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_beli * ($numeric_diskon_persen/100);

                $diskon_persen2 = $getdata_listpembelian->diskon_persen2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_beli - $diskon_harga) * ($numeric_diskon_persen2/100);
                
                $diskon_harga3 = str_replace(',', '.', $request->get('nilai_diskonharga3'));
                $numeric_diskon_persen3 = round(($diskon_harga3 / ($harga_beli - $diskon_harga - $diskon_harga2)) * 100, 2); //ambil sampai 2 angka dibelakang koma

                $harga_netto = $harga_beli - $diskon_harga - $diskon_harga2 - $diskon_harga3;
                $total_harga = $qty_beli * $harga_netto;

                $tgl_transaksi = $getdata_listpembelian->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }

                $jenis_ppn = $getdata_pembelian->jenis_ppn;
                $status_ppn = 'Ya'; 
                $nilai_ppn = 0;
                
                $code_transaksi = $getdata_listpembelian->nomor;
                ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('code_data',$request->code_data)->where('id',$request->id)
                    ->update([
                        'kode_satuan' => $kode_satuan,
                        'harga' => $harga_beli,
                        'jumlah_beli' => $qty_beli,
                        'diskon_persen' => $numeric_diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskon_persen2' => $numeric_diskon_persen2,
                        'diskon_harga2' => $diskon_harga2,
                        'diskon_persen3' => $numeric_diskon_persen3,
                        'diskon_harga3' => $diskon_harga3,
                        'harga_netto' => $harga_netto,
                        'total_harga' => $total_harga,
                        'status_ppn' => $status_ppn,
                        'ppn' => $nilai_ppn,
                ]);
                                
                $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->sum('total_harga');
                $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->first();

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

                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                } 

                Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)
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

    public function upsummarypembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->first();
            if($getdata){ 
                $sub_total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');

                // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('ppn');
                
                // $total = $sub_total + $pajak;

                $diskon_faktur = str_replace(".","",$request->get('nilai_diskon'));
                $diskon_faktur = str_replace(",",".",$diskon_faktur);

                // $biaya_lain = str_replace(".","",$request->get('biaya_lain'));
                // $biaya_lain = str_replace(",",".",$biaya_lain);

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

                // if($jenis_ppn == 'Include'){
                //     $grand_total = $total_diskon + $biaya_lain; 
                //     $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                //     $sub_total = Round($sub_total);

                //     $nilai_ppn = $grand_total - $sub_total;
                //     $nilai_ppn = Round($nilai_ppn);
                // }else{                    
                //     $grand_total = $total_diskon + $biaya_lain; 
                //     $sub_total = $grand_total - $total_diskon;
                //     $sub_total = Round($sub_total);

                //     $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                //     $nilai_ppn = Round($nilai_ppn);

                //     $grand_total = $sub_total + $nilai_ppn; 
                // }      


                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga + $biaya_lain; 
                    // $sub_total = $grand_total - $total_diskon;
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                }                  

                $savedata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)
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

    public function upsummarypembeliancash($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->first();
            if($getdata){ 
                $sub_total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');
                $grand_total = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('grand_total');

                $diskon_cash = str_replace(".","",$request->get('nilai_diskon'));
                $diskon_cash = str_replace(",",".",$diskon_cash);

                $total = $sub_total;
                $jenis_ppn = $getdata->jenis_ppn;
                $diskon_harga = $getdata->diskon_harga;
                $diskon_persen = $getdata->diskon_persen;
                $biaya_lain = $getdata->biaya_kirim;

                $tipe_diskon = $request->tipe_diskon;

                if( $tipe_diskon == 'Persen'){
                    $diskonCash_harga = $grand_total * ($diskon_cash/100);
                    $diskonCash_harga = Round($diskonCash_harga);
                    $diskonCash_persen = $request->get('nilai_diskon');
                    $diskoCashn_persen = str_replace(",",".",$diskonCash_persen);
                    $total_diskon = $grand_total - ($grand_total * ($diskon_cash/100));
                }elseif( $tipe_diskon == 'Jumlah'){
                    $diskonCash_harga = $request->get('nilai_diskon');
                    $diskonCash_harga = str_replace(",",".",$diskonCash_harga);
                    $diskonCash_persen = ($diskonCash_harga/$grand_total) * 100;
                    $totalCash_diskon = $grand_total - $diskon_cash;
                }else{
                    $diskonCash_harga = 0;
                    $diskonCash_persen = 0;
                    $total_diskon = 0;
                }
 

                $tgl_transaksi = $getdata->tanggal;
                $tgl_transaksi = Carbon::parse($tgl_transaksi)->format('Y-m-d');

                if($tgl_transaksi < '2022-04-01'){
                    $get_setting_nilai_pajak = 10;
                }else{
                    $get_setting_nilai_pajak = 11;
                }    


                if($jenis_ppn == 'Include'){
                    $grand_total = $total - $diskon_harga - $diskonCash_harga + $biaya_lain; 
                    $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                    $sub_total = Round($sub_total);

                    $nilai_ppn = $grand_total - $sub_total;
                    $nilai_ppn = Round($nilai_ppn);
                }else{
                    $grand_total = $total - $diskon_harga - $diskonCash_harga + $biaya_lain; 
                    $sub_total = $grand_total;
                    $sub_total = Round($sub_total);

                    $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                    $nilai_ppn = Round($nilai_ppn);

                    $grand_total = $sub_total + $nilai_ppn; 
                }                  

                $savedata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)
                    ->update([
                        'sub_total' => $sub_total,
                        'ppn' => $nilai_ppn,
                        'total' => $total,
                        'diskon_persen' => $diskon_persen,
                        'diskon_harga' => $diskon_harga,
                        'diskonCash_persen' => $diskonCash_persen,
                        'diskonCash_harga' => $diskonCash_harga,
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

    public function deleteprodpembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $getdata = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->where('id', $request->id)->first();
            if($getdata){
                $getdatatransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();
                $DelData = $getdata->delete();
                if($DelData){
                    // if($getdata->code_permintaan != null OR $getdata->code_permintaan != ''){
                        // $getdatareq = ListPermintaan::where('code_permintaan', $getdata->code_permintaan)->where('code_varian', $getdata->code_varian)->first();
                        // $qty_pembelian_up = $getdatareq->qty_pembelian - $getdata->qty_pembelian;
                        // ListPermintaan::where('code_permintaan', $getdata->code_permintaan)->where('code_varian', $getdata->code_varian)->update(['qty_pembelian'=>$qty_pembelian_up,]);
                        // $countreqpo = ListPembelian::where('code_permintaan', $getdata->code_permintaan)->where('status_input','!=','Batal')->count();
                        // if($countreqpo == 0){
                        //     Permintaan::where('code_data', $getdata->code_permintaan)->update(['status_data' => 'Permintaan','status_input' => 'Finish',]);
                        // }

                        // $cekqtyreq = ListPermintaan::get(); 
                        // foreach($cekqtyreq as $key => $listcek){
                        //     if($listcek->qty_permintaan == $listcek->qty_pembelian){
                        //         ListPermintaan::where('id', $listcek->id)
                        //             ->update([
                        //                 'status_terpenuhi' => 'Terpenuhi'
                        //         ]);
                        //     }else if($listcek->qty_permintaan > $listcek->qty_pembelian){
                        //         ListPermintaan::where('id', $listcek->id)
                        //             ->update([
                        //                 'status_terpenuhi' => 'Proses'
                        //         ]);
                        //     }
                        // }
                    // }
                    
                    // $sub_total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');

                    // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('ppn');
                    
                    // $total = $sub_total - $pajak;

                    // $diskon_persen = $getdatatransaksi->diskon_persen;
                    // $diskon_harga = $total * ($diskon_persen/100);

                    // if($getdatatransaksi->tipe_diskon == 'Persen'){
                    //     $total_diskon = $total - ($total * $getdatatransaksi->diskon_faktur/100);
                    // }elseif($getdatatransaksi->tipe_diskon == 'Jumlah'){
                        // $total_diskon = $total - $getdatatransaksi->diskon_harga;
                    // }else{
                    //     $total_diskon = $total;
                    // }

                    // $biaya_lain = $getdatatransaksi->biaya_kirim;

                    // $grand_total = $total_diskon + $biaya_lain;

                    // Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdatatransaksi->code_data)
                    //     ->update([
                    //         'sub_total' => $sub_total,
                    //         'ppn' => $pajak,
                    //         'total' => $total,
                    //         'diskon_persen' => $diskon_persen,
                    //         'diskon_harga' => $diskon_harga,
                    //         'biaya_kirim' => $biaya_lain,
                    //         'grand_total' => $grand_total,
                    // ]);

                    $total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');
                    // $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('ppn');
                    $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first(); 
                    
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
    
                    if($jenis_ppn == 'Include'){
                        $grand_total = $total - $diskon_harga + $biaya_lain; 
                        $sub_total = (($grand_total * 100) / ($get_setting_nilai_pajak + 100));
                        $sub_total = Round($sub_total);
    
                        $nilai_ppn = $grand_total - $sub_total;
                        $nilai_ppn = Round($nilai_ppn);
                    }else{
                        $grand_total = $total - $diskon_harga + $biaya_lain; 
                        $sub_total = $grand_total;
                        $sub_total = Round($sub_total);
    
                        $nilai_ppn = ($sub_total * ($get_setting_nilai_pajak / 100));
                        $nilai_ppn = Round($nilai_ppn);
    
                        $grand_total = $sub_total + $nilai_ppn; 
                    } 
    
                    Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdatatransaksi->nomor)
                        ->update([
                            'sub_total' => $sub_total,
                            'ppn' => $nilai_ppn,
                            'total' => $total,
                            'diskon_persen' => $diskon_persen,
                            'diskon_harga' => $diskon_harga,
                            'biaya_kirim' => $biaya_lain,
                            'grand_total' => $grand_total,
                    ]);

                    $count_produk = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->count();
                    if($count_produk == 0){
                        Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdatatransaksi->nomor)
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

                    // $qty_pembelian_up = ListPembelian::where('nomor', $getdata->nomor)->where('kode_barang', $getdata->kode_barang)->sum('jumlah_beli');
                    
                    // ListPenerimaan::where('code_pembelian', $getdata->code_pembelian)
                    //     ->where('code_varian', $getdata->code_varian)
                    //     ->update([
                    //         'qty_pembelian' => $qty_pembelian_up,
                    // ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function deletepembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdata){
                $listprod = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($listprod as $data_listprod){
                    $data_listprod->delete();
                }

                $getdata_hutang = Hutang::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
                if($getdata_hutang){
                    $del_hutang = $getdata_hutang->delete();
                }
                
                $getdata_historykas = HistoryKas::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_historykas as $data_historykas){
                    $data_historykas->delete();
                }

                $getdata_hutangbayar = HutangBayar::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_hutangbayar as $data_hutangbayar){
                    $data_hutangbayar->delete();
                }
                
                $getdata_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_historystock as $data_historystock){
                    $data_historystock->delete();
                }

                $getdata_penerimaan = Penerimaan::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_penerimaan as $data_penerimaan){
                    $data_penerimaan->delete();
                }          

                $getdata_listpenerimaan = ListPenerimaan::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_listpenerimaan as $data_listpenerimaan){
                    $data_listpenerimaan->delete();
                }           

                $getdata_jurnalumum = JurnalUmum::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_jurnalumum as $data_jurnalumum){
                    $data_jurnalumum->delete();
                }          

                $getdata_returpembelian = ReturPembelian::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_returpembelian as $data_returpembelian){
                    $data_returpembelian->delete();
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
                        'activity' => 'Membatalkan pembelian barang  ['.$getdata->nomor.']',
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

    // public function upppnpembelian($request)
    // {
    //     $uuid = Str::uuid();
    //     $object = [];

    //     $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

    //     if(!$viewadmin){
    //         return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
    //     }else{
    //         // $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
    //         // $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
    //         // if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
    //         //     return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
    //         // }
            
    //         $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
    //         $getdata = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->first();
    //         $getdatamutasi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata->nomor)->first();
    //         if($getdata){ 
    //             // if($getdata->tipe_diskon == 'Persen'){
    //             //     $harga_diskon = $getdata->harga - ($getdata->harga_beli * $getdata->nilai_diskon/100);
    //             // }elseif($getdata->tipe_diskon == 'Jumlah'){
    //                 // $harga_diskon = $getdata->harga - $getdata->diskon_harga;
    //             // }else{
    //             //     $harga_diskon = $getdata->harga;
    //             // }
    //             $diskon_persen = $getdata->diskon_persen;
    //             $diskon_harga = $getdata->diskon_harga;
    //             $harga_diskon =  ($getdata->harga * $getdata->jumlah_beli) - $getdata->diskon_harga;

    //             if($request->ppn_up == 'Yes'){
    //                 $nilai_pajak = ($harga_diskon * (10/100));
    //                 // $total_harga = $harga_diskon + $nilai_pajak;
    //                 $total_harga = $harga_diskon;   
    //             }else{
    //                 $nilai_pajak = 0;
    //                 $total_harga = $harga_diskon;
    //             }
                
    //             ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)
    //                 ->update([
    //                     'harga' => $getdata->harga,
    //                     'jumlah_beli' => $getdata->jumlah_beli,
    //                     'diskon_persen' => $diskon_persen,
    //                     'diskon_harga' => $diskon_harga,
    //                     'status_ppn' => $request->ppn_up,
    //                     'ppn' => $nilai_pajak,
    //                     'total_harga' => $total_harga,
    //             ]);

    //             $sub_total = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('total_harga');

    //             $pajak = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->sum('ppn');
                
    //             $total = $sub_total + $pajak;

    //             $gettransaksi = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->first();

    //             $diskon_persen = $gettransaksi->diskon_persen;
    //             $diskon_harga = $total * ( $diskon_persen/100);
    //             $total_diskon = $total - $diskon_harga;

    //             // if($gettransaksi->tipe_diskon == 'Persen'){
    //             //     $total_diskon = $total - ($total * $gettransaksi->diskon_faktur/100);
    //             // }elseif($gettransaksi->tipe_diskon == 'Jumlah'){
    //                 // $total_diskon = $total - $gettransaksi->diskon_harga;
    //             // }else{
    //             //     $total_diskon = $total;
    //             // }

    //             // $diskon_persen_faktur = $gettransaksi->diskon_persen;
    //             // $diskon_harga_faktur =  $total_diskon * ($diskon_persen_faktur/100);

    //             $biaya_lain = $gettransaksi->biaya_kirim;

    //             $grand_total = $total_diskon + $biaya_lain;

    //             Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)
    //                 ->update([
    //                     'sub_total' => $sub_total,
    //                     'ppn' => $pajak,
    //                     'total' => $total,
    //                     'diskon_persen' => $diskon_persen,
    //                     'diskon_harga' => $diskon_harga,
    //                     'biaya_kirim' => $biaya_lain,
    //                     'grand_total' => $grand_total,
    //             ]);
    //             return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
    //         }else{
    //             return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
    //         }
    //     }
    // }

    public function updatepembelian($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->first();

            $getdata['counttransaksi'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->count();

            if($getdata){  
                $validator = Validator::make($request->all(), [
                    'supplier' => 'required|string|max:200',
                ]);
    
                if($validator->fails()){
                    return response()->json(['status_message' => 'error','note' => $validator->errors()]);
                }

                $savedata = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)
                    ->update([
                        'kode_supplier' => $request->get('supplier'),
                        'ket' => $request->get('keterangan'),
                        'status_transaksi' => 'Proses',
                    ]);

                if($savedata){  
                    if($getdata['counttransaksi'] == 0){       
                        $savedata = Hutang::create([
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
                            'kode_akun' =>'117',
                            'uraian' => 'Persediaan',
                            'debet' => $getdata->grand_total,
                            'kredit' => 0,
                            'kode_user' => $viewadmin->id,
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]);   

                        $savedata = JurnalUmum::create([
                            'code_data' => $getdata->code_data,
                            'nomor' => $getdata->nomor,
                            'tanggal' => $getdata->tanggal,
                            'kode_akun' =>'211',
                            'uraian' => 'Utang Dagang',
                            'debet' => 0,
                            'kredit' => $getdata->grand_total,
                            'kode_user' => $viewadmin->id,
                            'kode_kantor' => $viewadmin->kode_kantor,
                        ]); 
                    }else{   
                        $updatedata = Hutang::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)
                            ->update([
                                'nomor' => $getdata->nomor,
                                'tanggal' => $getdata->tanggal,
                                'jumlah' => $getdata->grand_total,
                                'bayar' => 0,
                                'sisa' => $getdata->grand_total,
                        ]);

                        $updatedata = JurnalUmum::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->where('nomor', $getdata->nomor)->where('tanggal', $getdata->tanggal)->where('kode_akun', '117')->where('uraian', 'Persediaan')->where('debet', 0)
                            ->update([
                                'debet' => $getdata->grand_total,
                        ]);  

                        $updatedata = JurnalUmum::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->where('nomor', $getdata->nomor)->where('tanggal', $getdata->tanggal)->where('kode_akun', '211')->where('uraian', 'Utang Dagang')->where('kredit', 0)
                            ->update([
                                'kredit' => $getdata->grand_total,
                        ]); 
                    }

                    $getdata['listpembelian'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_data)->get();
                    foreach($getdata['listpembelian'] as $key => $listpembelian){
                        if($listpembelian->harga > 0){
                            $getdataSatuan = Satuan::Where('id',$listpembelian->kode_satuan)->first();
                            $hargabeli_item = Round($listpembelian->harga_netto / $getdataSatuan->isi);

                            if($getdata->diskon_persen > 0){
                                $hargabelidiskon = $hargabeli_item - ($hargabeli_item * ($getdata->diskon_persen / 100));
                            }else{
                                $hargabelidiskon = $hargabeli_item;
                            }

                            if($getdata->diskonCash_persen > 0){
                                $hargabelidiskoncash = $hargabelidiskon - ($hargabelidiskon * ($getdata->diskonCash_persen / 100));
                            }else{
                                $hargabelidiskoncash = $hargabelidiskon;
                            }

                            if($getdata->tanggal < '2022-04-01'){
                                $get_ppn = 10;
                            }else{
                                $get_ppn = 11;
                            }

                            if($getdata->jenis_ppn == 'Exclude'){
                                $nilai_ppn = Round($hargabelidiskoncash * ($get_ppn / 100));                           
                                $hargabeliakhir = $hargabelidiskoncash + $nilai_ppn;
                            }else{
                                $hargabeliakhir = $hargabelidiskoncash;
                            }
                            
                            Barang::Where('id',$listpembelian->kode_barang)
                            ->update([                        
                                'harga_beli' => $hargabeliakhir,
                            ]);
                        }                 
                    }

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

    public function historypembelian($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
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
            
            $results['list'] = DB::table('db_pembelian')
                ->join('db_supplier', 'db_pembelian.kode_supplier','=','db_supplier.id')
                ->join('db_users_web', 'db_pembelian.kode_user','=','db_users_web.id')
                ->join('db_cabang', 'db_pembelian.kode_cabang','=','db_cabang.id')
                ->select(
                    'db_pembelian.*',
                    'db_supplier.nama as nama_supplier',
                    'db_users_web.full_name as nama_user_input'
                )
                ->whereBetween('db_pembelian.tanggal', [$datefilterstart, $datefilterend])
                ->where('db_pembelian.kode_kantor', $viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->where('db_pembelian.code_data','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_pembelian.nomor','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_pembelian.status_transaksi','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_pembelian.ket','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_pembelian.tanggal','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_supplier.nama','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_users_web.full_name','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_cabang.nama_cabang','LIKE','%'.$request->keysearch.'%');
                })
                ->orderBy('db_pembelian.nomor', 'DESC')
                ->orderBy('db_pembelian.tanggal', 'DESC')
                ->paginate($vd ? $vd : 20);


            // $results['list'] = DB::table('db_pembelian')
            //     ->join('db_supplier', 'db_pembelian.kode_supplier','=','db_supplier.id' )
            //     ->whereBetween('db_pembelian.tanggal', [$datefilterstart, $datefilterend])
            //     ->Where('db_pembelian.kode_kantor',$viewadmin->kode_kantor)
            //     ->where(function($query) use ($request) {
            //         $query->Where('db_pembelian.code_data','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_pembelian.nomor','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_pembelian.status_transaksi','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_pembelian.ket','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_pembelian.tanggal','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%');
            //     })
            //     ->orderBy('db_pembelian.nomor', 'DESC')
            //     ->orderBy('db_pembelian.tanggal', 'DESC')
            //     ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['detail_pembelian'][$data->code_data] = Pembelian::select('code_data','nomor','kode_user','kode_kantor','kode_cabang','kode_supplier','kode_gudang','status_transaksi')->where('nomor', $data->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->first();                
                $results['user_input'][$data->code_data] = User::select('code_data','full_name')->where('id', $results['detail_pembelian'][$data->code_data]->kode_user)->first();
                $results['qty_pembelian'][$data->code_data] = ListPembelian::where('nomor', $results['detail_pembelian'][$data->code_data]->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->sum('jumlah_beli');
                $results['qty_terima'][$data->code_data] = ListPembelian::where('nomor', $results['detail_pembelian'][$data->code_data]->nomor)->where('kode_kantor',$viewadmin->kode_kantor)->sum('jumlah_terima');$results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('kode', $results['detail_pembelian'][$data->code_data]->kode_kantor)->first();                
                $results['detail_cabang'][$data->code_data] = Cabang::select('nama_cabang')->where('id', $results['detail_pembelian'][$data->code_data]->kode_cabang)->first();
                $results['detail_supplier'][$data->code_data] = Supplier::where('id', $results['detail_pembelian'][$data->code_data]->kode_supplier)->first();
                $results['detail_gudang'][$data->code_data] = Gudang::select('nama')->where('id', $results['detail_pembelian'][$data->code_data]->kode_gudang)->first();
                $results['detail_cabang'][$data->code_data] = Cabang::select('nama_cabang')->where('id', $results['detail_pembelian'][$data->code_data]->kode_cabang)->first();                
                $results['list_produk'][$data->nomor] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',  $data->nomor)->orderBy('created_at', 'ASC')->get(); 
        
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

    public function historypembelianitem($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembelianbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembelianbarang')->first();
            
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
                ->join('db_pembeliand', 'db_pembelian.nomor', '=', 'db_pembeliand.nomor')
                ->join('db_barang', 'db_pembeliand.kode_barang', '=', 'db_barang.id')
                ->whereBetween('db_pembelian.tanggal', [$datefilterstart, $datefilterend])
                ->Where('db_pembelian.kode_kantor',$viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_pembelian.code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.status_transaksi','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.ket','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_pembelian.nomor', 'DESC')
                ->orderBy('db_pembelian.tanggal', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                
                $results['detail_pembelian'][$data->id] = Pembelian::select('code_data','nomor','kode_user','kode_kantor','kode_cabang','kode_supplier','kode_gudang','status_transaksi','ket')->where('nomor', $data->nomor)->first();
                
                $results['user_input'][$data->id] = User::select('code_data','full_name')->where('id', $results['detail_pembelian'][$data->id]->kode_user)->first();
                
                $results['kategori_prod'][$data->id] = Kategori::select('nama')->where('id',$data->kode_jenis)->orderBy('created_at','DESC')->first();
                
                $results['produk'][$data->id] = Barang::select('code_data','nama')->where('id', $data->kode_barang)->first();
                
                $results['satuan_prod'][$data->id] = Satuan::select('code_data','nama')->where('id', $data->kode_satuan)->first();

                $results['qty_pembelian'][$data->id] = ListPembelian::where('nomor', $results['detail_pembelian'][$data->id]->nomor)->sum('jumlah_beli');

                $results['qty_terima'][$data->id] = ListPembelian::where('nomor', $results['detail_pembelian'][$data->id]->nomor)->sum('jumlah_terima');

                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('kode', $results['detail_pembelian'][$data->id]->kode_kantor)->first();

                $results['detail_cabang'][$data->id] = Cabang::select('nama_cabang')->where('id', $results['detail_pembelian'][$data->id]->kode_cabang)->first();

                $results['detail_supplier'][$data->id] = Supplier::where('id', $results['detail_pembelian'][$data->id]->kode_supplier)->first();

                $results['detail_gudang'][$data->id] = Gudang::select('nama')->where('id', $results['detail_pembelian'][$data->id]->kode_gudang)->first();

            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }
}