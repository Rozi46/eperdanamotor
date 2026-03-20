<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePengeluarankas
{
    public function generateCode($length = 4, $type = 'letters') {
        switch ($type) {
            case 'letters': // huruf saja A-Z
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);

            case 'numbers': // angka saja 0-9
                return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

            case 'mixed': // huruf + angka
                return strtoupper(Str::random($length));

            default:
                throw new InvalidArgumentException("Type harus 'letters', 'numbers', atau 'mixed'");
        }
    }
    
    public function getcodepaykas($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){        
            $tanggal = Carbon::parse($request->tgl_transaksi);

            $datenow = $tanggal->format('Y-m-d');
            $yearnow = $tanggal->format('Y');

            $getdata = KasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->where('created_at','<=', $datenow)->get();

            $dataAll = KasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->orderBy('created_at', 'DESC')->first();
            $countData = kasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->whereYear('tanggal','=', $yearnow)->count();

            if($countData <> 0){
                $newCodeData = substr($dataAll->nomor,-7);
                $newCodeData = $newCodeData + 1;
            }else{
                $newCodeData = 1;
            }

            $kantor = $viewadmin->kode_kantor;
            $newCodeData = str_pad($newCodeData, 7, "0", STR_PAD_LEFT);
            $datenow = Carbon::parse($request->get('tgl_transaksi'))->format('Y'); 
            $newCodeData = "KK-".$datenow.'.'.$newCodeData;

            return response()->json(['status_message' => 'success','note' => 'Data berhasil diambil','results' => $newCodeData,'status_data' => 'No']);
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }

    }

    public function savepengeluarankas($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengeluarankas')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','inputpengeluarankas')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $code_transaksi = $request->get('code_data');

            $counttransaksi = KasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $code_transaksi)->count(); 
            $get_akun = Akun::Where('id',$request->get('akun_biaya'))->first(); 

            $validator = Validator::make($request->all(), [
                'tgl_transaksi' => 'required|string|max:200',
                'nomor_transaksi' => 'required|string|max:200',
                'akun_biaya' => 'required|string|max:200',
                'jumlah' => 'required|string|max:200',
                'keterangan' => 'required|string|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $tgl_transaksi = Carbon::parse($request->get('in_tgl_transaksi'))->format('Y-m-d'); 

            $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
            $time = Carbon::now()->format('Ymdhis');
            $newCodeData = $time."".$otp;
            $newCodeData = ltrim($newCodeData, '0');
            
            if($counttransaksi == 0){
                $savedata = KasKeluar::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData,
                    'nomor' => $code_transaksi,
                    'tanggal' => $tgl_transaksi,
                    'kode_akun' => $get_akun->kode,
                    'jenis' => $get_akun->nama,
                    'keterangan' => $request->get('keterangan'),
                    'nilai' => $request->get('jumlah'),
                    'kode_kantor' => $viewadmin->kode_kantor,
                    'kode_user' => $viewadmin->id,
                ]);

                if($savedata){
                    HistoryKas::create([     
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'debet' => 0,
                        'kredit' => $request->get('jumlah'),
                        'keterangan' => 'Kas keluar untuk ['.$request->get('keterangan').']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);

                    JurnalUmum::create([  
                        'code_data' => $newCodeData,
                        'nomor' => $code_transaksi,
                        'tanggal' => $tgl_transaksi,
                        'kode_akun' => $get_akun->kode,
                        'uraian' => $get_akun->nama,
                        'debet' => $request->get('jumlah'),
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
                        'kredit' => $request->get('jumlah'),
                        'kode_kantor' => $viewadmin->kode_kantor,
                        'kode_user' => $viewadmin->id,
                    ]);

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Tambah data kas keluar ['.$code_transaksi.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object,'code' => $newCodeData]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }
        }
    }

    public function historypengeluarankas($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengeluarankas')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengeluarankas')->first();
            
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
            
            $results['list'] = DB::table('db_kaskeluar')
                ->Where('kode_kantor',$viewadmin->kode_kantor)
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->Where('code_data','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('jenis','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('keterangan','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('nilai','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){                
                $results['user_input'][$data->code_data] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
            }

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function viewpengeluarankas($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $getdata['detail'] = KasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if($getdata['detail']){                 
                $getdata['user_transaksi'] = User::where('id', $getdata['detail']->kode_user)->first();
                $getdata['detail_perusahaan'] = Kantor::where('id', $getdata['detail']->kode_kantor)->first();

                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function deletepengeluarankas($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menupengeluarankas')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historypengeluarankas')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $getdata = KasKeluar::Where('kode_kantor',$viewadmin->kode_kantor)->where('code_data', $request->code_data)->first();
            if(!$getdata){
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }else{
                try {
                    DB::beginTransaction();
                    HistoryKas::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->delete();
                    JurnalUmum::where('kode_kantor', $viewadmin->kode_kantor)->where('code_data', $getdata->code_data)->delete();
                    $Deldata = $getdata->delete();
                    if (!$Deldata) {
                        throw new \Exception('Gagal menghapus data kas keluar utama');
                    }  

                    $time = now()->format('YmdHis');
                    $otp  = $this->generateCode(1, 'letters'); 
                    $newCodeData = ltrim($time . $otp, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Membatalkan kas keluar  ['.$getdata->nomor.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);

                    DB::commit();
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);   
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan: ' . $e->getMessage(),'results' => $object]);
                }
            }
        }
    }
}