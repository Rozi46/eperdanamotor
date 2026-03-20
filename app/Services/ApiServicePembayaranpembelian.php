<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePembayaranpembelian
{
    public function getcodepurchasepayment ($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 
            $yearnow = Carbon::parse($request->get('tgl_transaksi'))->format('Y');
            $getdata = HutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = HutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = HutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "PH-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_data' => 'No']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function listpurchasepayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $getdata = array();
            $results['list'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)
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

    public function detailpurchasepayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results['detail'] = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            $results['detail_supplier'] = Supplier::where('id',$results['detail']->kode_supplier)->first();
            $results['detail_hutang'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function savepurchasepayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembayaranhutang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpembayaranhutang')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $code_transaksi = $request->get('code_data');
            $counttransaksi = HutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count(); 

            $validator = Validator::make($request->all(), [
                'tgl_transaksi' => 'required|string|max:200',
                'nomor_transaksi' => 'required|string|max:200',
                'nama_supplier' => 'required|string|max:200',
                'nomor_pembelian' => 'required|string|max:200',
                'pembayaran' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','code' =>  $code_transaksi,'note' => $validator->errors()]);}

            $tgl_transaksi = Carbon::parse($request->get('tgl_transaksi'))->format('Y-m-d'); 

            // $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            // $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
            // $time = Carbon::now()->format('Ymdhis');
            // $newCodeData = $time."".$otp;
            // $newCodeData = ltrim($newCodeData, '0');  

            $getdata_pembelian = Pembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->get('nomor_pembelian'))->first();             
            $newCodeData = $getdata_pembelian->code_data; 
            
            if($counttransaksi == 0){
                $savedata = HutangBayar::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'nomor_hutang' => $request->get('nomor_pembelian'),
                    'jumlah' => $request->get('pembayaran'),
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]);

                if($savedata){ 
                    $bayar = $request->jumlah_bayar + $request->pembayaran;
                    $sisa = $request->sisa_hutang - $request->pembayaran;
                    
                    $updatedata = Hutang::where('nomor', $request->get('nomor_pembelian'))->where('kode_kantor', $viewadmin->kode_kantor)
                        ->update([
                            'bayar' => $bayar,
                            'sisa' => $sisa,
                    ]);

                    HistoryKas::create([     
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'debet' => 0,
                        'kredit' => $request->get('pembayaran'),
                        'keterangan' => 'Pembayaran pembelian nomor ['.$request->get('nomor_pembelian').']',
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
                        'activity' => 'Tambah data pembayaran pembelian ['.$request->get('nomor_pembelian').']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }
        }
    }

    public function historypembayaranhutang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupembayaranhutang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypembayaranhutang')->first();
            
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
            
            $results['list'] = DB::table('db_hutang_bayar')
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor_hutang','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('jumlah','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->code_data] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_pembelian'][$data->code_data] = Pembelian::where('nomor', $data->nomor_hutang)->first();
                $results['detail_supplier'][$data->code_data] = Supplier::where('id', $results['detail_pembelian'][$data->code_data]->kode_supplier)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function viewpurchasepayment($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = HutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $request->code_transaksi)->first();
            if($getdata['detail']){                 
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();
                $getdata['detail_pembelian'] = Pembelian::where('kode_kantor', $getdata['detail']->kode_kantor)->where('nomor', $getdata['detail']->nomor_hutang)->first();
                $getdata['detail_supplier'] = Supplier::where('id', $getdata['detail_pembelian']->kode_supplier)->first();
                $getdata['detail_hutang'] = Hutang::where('kode_kantor', $getdata['detail']->kode_kantor)->where('nomor', $getdata['detail']->nomor_hutang)->first();
                $getdata['jumlah_bayar'] = $getdata['detail_hutang']->bayar - $getdata['detail']->jumlah;
                $getdata['sisa_hutang'] = $getdata['detail_hutang']->sisa + $getdata['detail']->jumlah;

                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }
}