<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePembayaranpenjualan
{
    public function getcodesalespayment ($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PP-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_data' => 'No']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function listsalespayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $getdata = array();
            $results['list'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)
                ->where('nomor','LIKE', '%'.$request->term.'%')
                ->where(function($query) use ($request) {
                    $query->Where('sisa','!=','0');
                })
                ->orderBy('created_at', 'ASC')
                ->get();

            foreach($results['list'] as $key => $list){  
                $getdata[] = array(
                    'label' => $list->nomor,
                    'code_data' => $list->code_data
                );
            }
                
            return response()->json($getdata);
        }
    }

    public function detailsalespayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results['detail'] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            $results['detail_customer'] = Customer::where('id',$results['detail']->kode_customer)->first();
            $results['detail_piutang'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function savesalespayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembayaranpiutang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpembayaranpiutang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $code_transaksi = $request->get('code_data');

            $counttransaksi = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count(); 

            $validator = Validator::make($request->all(), [                
                'tgl_transaksi' => 'required|string|max:200',
                'nomor_transaksi' => 'required|string|max:200',
                'nama_customer' => 'required|string|max:200',
                'nomor_penjualan' => 'required|string|max:200',
                'pembayaran' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','code' =>  $code_transaksi,'note' => $validator->errors()]);}

            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 

            // $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            // $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
            // $time = Carbon::now()->format('Ymdhis');
            // $newCodeData = $time."".$otp;
            // $newCodeData = ltrim($newCodeData, '0'); 

            $getdata_penjualan = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->get('nomor_penjualan'))->first();             
            $newCodeData = $getdata_penjualan->code_data;
            
            if($counttransaksi == 0){
                $savedata = PiutangBayar::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'nomor_piutang' => $request->get('nomor_penjualan'),
                    'jumlah' => $request->get('pembayaran'),
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]);

                if($savedata){ 
                    $bayar = $request->jumlah_bayar + $request->pembayaran;
                    $sisa = $request->sisa_piutang - $request->pembayaran;
                    
                    $updatedata = Piutang::where('nomor', $request->get('nomor_penjualan'))->where('kode_kantor', $viewadmin->kode_kantor)
                        ->update([
                            'bayar' => $bayar,
                            'sisa' => $sisa,
                    ]);
                    
                                HistoryKas::create([     
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'debet' => $request->get('pembayaran'),
                        'kredit' => 0,
                        'keterangan' => 'Diterima Pembayaran penjualan nomor ['.$request->get('nomor_penjualan').']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);

                    JurnalUmum::create([  
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'kode_akun' => '211',
                        'uraian' => 'Hutang Dagang',
                        'debet' => $request->get('pembayaran'),
                        'kredit' => 0,
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);

                    JurnalUmum::create([  
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'kode_akun' => '111',
                        'uraian' => 'Kas',
                        'debet' => 0,
                        'kredit' => $request->get('pembayaran'),
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Tambah data pembayaran penjualan ['.$request->get('nomor_penjualan').']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }
        }
    }

    public function historypembayaranpiutang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembayaranpiutang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembayaranpiutang')->first();
            
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
            
            $results['list'] = DB::table('db_piutang_bayar')
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor_piutang','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('jumlah','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->code_data] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_penjualan'][$data->code_data] = Penjualan::where('nomor', $data->nomor_piutang)->first();
                if($results['detail_penjualan'][$data->code_data]){
                    $results['detail_customer'][$data->code_data] = Customer::where('id', $results['detail_penjualan'][$data->code_data]->kode_customer)->first();
                }else{                    
                    $results['detail_customer'][$data->code_data] = 'Belum ditentukan';
                }
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function viewsalespayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdata['detail']){                 
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                $getdata['detail_penjualan'] = Penjualan::where('kode_kantor', $getdata['detail']->kode_kantor)->where('nomor', $getdata['detail']->nomor_piutang)->first();
                $getdata['detail_customer'] = Customer::where('id', $getdata['detail_penjualan']->kode_customer)->first();
                $getdata['detail_piutang'] = Piutang::where('kode_kantor', $getdata['detail']->kode_kantor)->where('nomor', $getdata['detail']->nomor_piutang)->first();
                $getdata['jumlah_bayar'] = $getdata['detail_piutang']->bayar - $getdata['detail']->jumlah;
                $getdata['sisa_piutang'] = $getdata['detail_piutang']->sisa + $getdata['detail']->jumlah;

                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }
}