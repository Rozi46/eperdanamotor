<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ListPenjualanMekanik, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePenjualan
{
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
    
    public function listopmekanik($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan']);
        }else{
            $results = Karyawan::where('status_data','Aktif')->orderBy('nama', 'ASC')->get();
            return response()->json(['status_message' => 'success','results' => $results]);
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
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function saveprodpenjualan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $code_data = $request->get('code_data');
            $code_transaksi = $request->get('code_transaksi');
            $code_produk = $request->get('code_produk');


            $counttransaksi = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count();         
            $countprod = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->where('kode_barang', $code_produk)->count();

            $validator = Validator::make($request->all(), [
                'code_transaksi' => 'required|string|max:200',
                'tgl_transaksi' => 'required|string|max:200',
                'code_gudang' => 'required|string|max:200',
                'code_customer' => 'required|string|max:200',
                'code_produk' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

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
                    'nomor' => $request->get('code_transaksi'),
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
                    'jenis_penjualan' => 'Kredit',
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

                $mekanikBaru = $request->input('code_mekanik', []);

                /* jika string "A,B,C" ubah jadi array */
                if (!is_array($mekanikBaru)) {
                    $mekanikBaru = explode(',', $mekanikBaru);
                }

                $hapusMekanik = array_diff($mekanikLama, $mekanikBaru);

                if (!empty($hapusMekanik)) {
                    ListPenjualanMekanik::where('nomor', $code_transaksi)->where('kode_kantor', $viewadmin->kode_kantor)->whereIn('code_mekanik', $hapusMekanik)->delete();
                }

                foreach ($mekanikBaru as $mekanikId){
                    ListPenjualanMekanik::create([
                        'id'            => Str::uuid(),
                        'code_data'     => $newCodeData,
                        'nomor'         => $code_transaksi,
                        'code_mekanik'  => $mekanikId,
                        'tanggal'       => $tgl_transaksi,
                        'kode_kantor'   => $viewadmin->kode_kantor,
                        'kode_user'     => $viewadmin->id,
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
                        'nomor' => $request->get('code_transaksi'),
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
                                'nomor' => $request->get('code_transaksi'),
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
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function viewpenjualan($request)
    {
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

                        if($getdata['detail_produk'][$list->kode_barang]){
                            $getdata['satuan_produk'][$list->kode_barang] = Satuan::where('id', $getdata['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                            if($getdata['satuan_produk'][$list->kode_barang]){
                                $getdata['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $getdata['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get(); 
                            }
                        }                        
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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
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

    public function updiscpenjualan($request)
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
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();

            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan;  
                $harga_jual = $getdata_listpenjualan->harga;                  
                $qty_jual = $getdata_listpenjualan->jumlah_jual;      

                $diskon_persen = $request->nilai_diskon;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_jual * ($numeric_diskon_persen/100);

                $diskon_persen2 = $getdata_listpenjualan->diskon_persen2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($numeric_diskon_persen2/100);   

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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
            $getdata_listpenjualan = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data',$request->code_data)->where('id',$request->id)->first();
            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',$getdata_listpenjualan->nomor)->first();

            if($getdata_listpenjualan){  
                $kode_satuan = $getdata_listpenjualan->kode_satuan;  
                $harga_jual = $getdata_listpenjualan->harga;                  
                $qty_jual = $getdata_listpenjualan->jumlah_jual;             

                $diskon_persen = $getdata_listpenjualan->diskon_persen;
                $numeric_diskon_persen = str_replace(',', '.', $diskon_persen);
                $numeric_diskon_persen = (float) $numeric_diskon_persen;
                $diskon_harga = $harga_jual * ($numeric_diskon_persen/100);

                $diskon_persen2 = $request->nilai_diskon2;
                $numeric_diskon_persen2 = str_replace(',', '.', $diskon_persen2);
                $numeric_diskon_persen2 = (float) $numeric_diskon_persen2;
                $diskon_harga2 = ($harga_jual - $diskon_harga) * ($numeric_diskon_persen2/100);  

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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdata){
                $listprod = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $getdata->nomor)->get();
                foreach($listprod as $key => $list){
                    $getdata_prod = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $list->code_data)->first();
                    $Deldata_prod = $getdata_prod->delete();
                }

                ListPenjualanMekanik::where('nomor', $getdata->nomor)->where('kode_kantor', $viewadmin->kode_kantor)->delete();

                $getdata_piutang = piutang::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
                if($getdata_piutang){
                    $del_piutang = $getdata_piutang->delete();
                }
                
                $getdata_historykas = HistoryKas::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_historykas as $data_historykas){
                    $data_historykas->delete();
                }

                $getdata_piutangbayar = PiutangBayar::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_piutangbayar as $data_piutangbayar){
                    $data_piutangbayar->delete();
                }
                
                $getdata_historystock = HistoryStock::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_historystock as $data_historystock){
                    $data_historystock->delete();
                }

                $getdata_pengiriman = Pengiriman::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_pengiriman as $data_pengiriman){
                    $data_pengiriman->delete();
                }          

                $getdata_listpengiriman = ListPengiriman::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_listpengiriman as $data_listpengiriman){
                    $data_listpengiriman->delete();
                }           

                $getdata_jurnalumum = JurnalUmum::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_jurnalumum as $data_jurnalumum){
                    $data_jurnalumum->delete();
                }          

                $getdata_returpenjualan = ReturPenjualan::where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->get();
                foreach ($getdata_returpenjualan as $data_returpenjualan){
                    $data_returpenjualan->delete();
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
                        'activity' => 'Membatalkan penjualan barang  ['.$getdata->nomor.']',
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

    public function updatepenjualan($request)
    {
        $object = [];
        $datenow = Carbon::now()->modify("0 days")->format('Y-m-d H:i:s');
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
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
                        'status_transaksi' => 'Proses',
                    ]);                    

                $mekanikLama = ListPenjualanMekanik::where('nomor', $getdata->nomor)->where('kode_kantor', $viewadmin->kode_kantor)->pluck('code_mekanik')->toArray();
                $mekanikBaru = $request->get('code_mekanik');

                /* jika string "A,B,C" ubah jadi array */
                if (!is_array($mekanikBaru)) {
                    $mekanikBaru = explode(',', $mekanikBaru);
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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
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
            
            $results['list'] = DB::table('db_penjualan')
                ->join('db_customer', 'db_penjualan.kode_customer','=','db_customer.id')
                ->join('db_users_web', 'db_penjualan.kode_user','=','db_users_web.id')
                ->select(
                    'db_penjualan.*',
                    'db_customer.nama as nama_customer',
                    'db_users_web.full_name as nama_user_input'
                )
                ->whereBetween('db_penjualan.tanggal', [$datefilterstart, $datefilterend])
                ->where('db_penjualan.kode_kantor', $viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->where('db_penjualan.code_data','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_penjualan.nomor','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_penjualan.status_transaksi','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_penjualan.ket','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_penjualan.tanggal','LIKE', '%'.$request->keysearch.'%')
                        ->orWhere('db_customer.nama','LIKE','%'.$request->keysearch.'%')
                        ->orWhere('db_users_web.full_name','LIKE','%'.$request->keysearch.'%');
                })
                ->orderBy('db_penjualan.nomor', 'DESC')
                ->orderBy('db_penjualan.tanggal', 'DESC')
                ->paginate($vd ? $vd : 20);

            // $results['list'] = DB::table('db_customer')
            //     ->join('db_penjualan', 'db_customer.id', '=', 'db_penjualan.kode_customer')
            //     ->whereBetween('db_penjualan.tanggal', [$datefilterstart, $datefilterend])
            //     ->Where('db_penjualan.kode_kantor',$viewadmin->kode_kantor)
            //     ->where(function($query) use ($request) {
            //         $query->Where('db_penjualan.code_data','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penjualan.nomor','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penjualan.status_transaksi','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penjualan.ket','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_penjualan.tanggal','LIKE', '%'.$request->keysearch.'%')
            //         ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%');
            //     })
            //     ->orderBy('db_penjualan.tanggal', 'DESC')
            //     ->orderBy('db_penjualan.nomor', 'DESC')
            //     ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){
                
                $results['detail_penjualan'][$data->code_data] = Penjualan::where('nomor', $data->nomor)->where('kode_kantor', $data->kode_kantor)->first();
                
                $results['user_input'][$data->code_data] = User::select('code_data','full_name')->where('id', $results['detail_penjualan'][$data->code_data]->kode_user)->where('kode_kantor', $data->kode_kantor)->first();
                if(!$results['user_input'][$data->code_data]){
                    $results['user_input'][$data->code_data]['full_name'] = 'Belum Ditentukan';
                }

                $results['qty_penjualan'][$data->code_data] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->code_data]->nomor)->sum('jumlah_jual');

                $results['qty_kirim'][$data->code_data] = ListPenjualan::where('nomor', $results['detail_penjualan'][$data->code_data]->nomor)->sum('jumlah_kirim');

                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('kode', $results['detail_penjualan'][$data->code_data]->kode_kantor)->first();

                $results['detail_customer'][$data->code_data] = Customer::where('id', $results['detail_penjualan'][$data->code_data]->kode_customer)->first();

                $results['detail_gudang'][$data->code_data] = Gudang::select('nama')->where('id', $results['detail_penjualan'][$data->code_data]->kode_gudang)->first();

                $results['list_mekanik'][$data->code_data] = ListPenjualanMekanik::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->orderBy('created_at', 'ASC')->get();
        
                foreach($results['list_mekanik'][$data->code_data] as $key => $listMekanik){
                    $results['detail_mekanik'][$data->code_data][$listMekanik->code_mekanik] = Karyawan::where('code_data', $listMekanik->code_mekanik)->first();                   
                }
                
                $results['list_produk'][$data->nomor] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor',  $data->nomor)->orderBy('created_at', 'ASC')->get(); 
        
                foreach($results['list_produk'][$data->nomor] as $key => $list){
                    $results['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    $results['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                    if($results['detail_produk'][$list->kode_barang]){
                        $results['satuan_produk'][$list->kode_barang] = Satuan::where('id', $results['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                        if($results['satuan_produk'][$list->kode_barang]){
                            $results['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $results['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get(); 
                        }
                    }                                                            
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
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupenjualanbarang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypenjualanbarang')->first();
            
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
}