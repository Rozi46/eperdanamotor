<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceHistorystock
{   
    public function historystockbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menugudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','historystockbarang')->first();

            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $kode_gudang = $request->get('nama_gudang');
            if($kode_gudang == Null or $kode_gudang ==''){
                $results['nama_gudang']['nama'] = 'Semua Gudang';
            }else{   
                $results['nama_gudang'] = Gudang::where('id',  $kode_gudang)->first();
            }
            
            $dateyear = Carbon::now()->modify("-1 year")->format('Y-m-d') . ' 00:00:00';

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("+1 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $searchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($searchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($searchdate[1])->format('Y-m-d') . ' 23:59:59';
            }  

            if($kode_gudang == Null or $kode_gudang ==''){
                $results['list'] = DB::table('db_arusstock')
                    ->join('db_barang', 'db_arusstock.kode_barang', '=', 'db_barang.id')
                    ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
                    ->select(
                        'db_barang.code_data',
                        'db_barang.kode',
                        'db_barang.nama AS nama_barang',
                        'db_satuan_barang.nama',
                        'db_barang.kode_satuan_default',
                        'db_arusstock.kode_barang'
                    )
                    // ->where('db_arusstock.kode_kantor', $viewadmin->kode_kantor)
                    ->where('db_barang.type_produk','LIKE','Barang')
                    ->where(function ($query) use ($request) {
                        $query->where('db_barang.code_data', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.kode', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_satuan_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.kode_satuan_default', 'LIKE', '%' . $request->keysearch . '%');
                    })
                    ->groupBy(
                        'db_barang.code_data',
                        'db_barang.kode',
                        'db_barang.nama',
                        'db_satuan_barang.nama',
                        'db_barang.kode_satuan_default',
                        'db_arusstock.kode_barang'
                    )
                    ->orderBy('db_barang.nama', 'ASC')
                    ->paginate($vd ?: 20);
            
                foreach($results['list'] as $key => $list){ 
                    // $results['stock_awal'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->where('db_arusstock.tanggal','<',$datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk - keluar'));

                    // $results['stock_masuk'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('masuk');

                    // $results['stock_keluar'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('keluar');

                    $results['stock_awal'][$list->kode_barang] = HistoryStock::where('db_arusstock.tanggal', '<', $datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk')) - HistoryStock::where('db_arusstock.tanggal', '<', $datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('ABS(keluar)'));

                    $results['stock_masuk'][$list->kode_barang] = HistoryStock::whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('masuk');

                    $results['stock_keluar'][$list->kode_barang] = HistoryStock::whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum(DB::raw('ABS(keluar)'));



                    // $results['stock_awal'][$list->kode_barang] = HistoryStock::where('db_arusstock.tanggal','<',$datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk - keluar'));

                    // $results['stock_masuk'][$list->kode_barang] = HistoryStock::whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('masuk');

                    // $results['stock_keluar'][$list->kode_barang] = HistoryStock::whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('keluar');

                    $results['stock_akhir'][$list->kode_barang] = $results['stock_awal'][$list->kode_barang] + $results['stock_masuk'][$list->kode_barang] - $results['stock_keluar'][$list->kode_barang];
        
                    $results['barang'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    $results['kategori'][$list->kode_barang] = Kategori::where('id', $results['barang'][$list->kode_barang]->kode_jenis)->first();
                    $results['merk'][$list->kode_barang] = Merk::where('id', $results['barang'][$list->kode_barang]->kode_brand)->first();
                    $results['supplier'][$list->kode_barang] = Supplier::where('id', $results['barang'][$list->kode_barang]->kode_supplier)->first();
                }
            }else{ 
                $results['list'] = DB::table('db_arusstock')
                    ->join('db_barang', 'db_arusstock.kode_barang', '=', 'db_barang.id')
                    ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
                    ->select(
                        'db_barang.code_data',
                        'db_barang.kode',
                        'db_barang.nama AS nama_barang',
                        'db_satuan_barang.nama',
                        'db_barang.kode_satuan_default',
                        'db_arusstock.kode_barang'
                    )
                    // ->where('db_arusstock.kode_kantor', $viewadmin->kode_kantor)
                    ->where('db_arusstock.kode_gudang', $kode_gudang)
                    ->where('db_barang.type_produk','LIKE','Barang')
                    ->where(function ($query) use ($request) {
                        $query->where('db_barang.code_data', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.kode', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_satuan_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                            ->orWhere('db_barang.kode_satuan_default', 'LIKE', '%' . $request->keysearch . '%');
                    })
                    ->groupBy(
                        'db_barang.code_data',
                        'db_barang.kode',
                        'db_barang.nama',
                        'db_satuan_barang.nama',
                        'db_barang.kode_satuan_default',
                        'db_arusstock.kode_barang'
                    )
                    ->orderBy('db_barang.nama', 'ASC')
                    ->paginate($vd ?: 20);
            
                foreach($results['list'] as $key => $list){ 
                    // $results['stock_awal'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->where('kode_gudang',$kode_gudang)->where('db_arusstock.tanggal','<',$datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk - keluar'));

                    // $results['stock_masuk'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->where('kode_gudang',$kode_gudang)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('masuk');

                    // $results['stock_keluar'][$list->kode_barang] = HistoryStock::where('kode_kantor', $viewadmin->kode_kantor)->where('kode_gudang',$kode_gudang)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('keluar');

                    // $results['stock_awal'][$list->kode_barang] = HistoryStock::where('kode_gudang',$kode_gudang)->where('db_arusstock.tanggal','<',$datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk - keluar'));

                    $results['stock_awal'][$list->kode_barang] = HistoryStock::where('kode_gudang',$kode_gudang)->where('db_arusstock.tanggal', '<', $datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('masuk')) - HistoryStock::where('kode_gudang',$kode_gudang)->where('db_arusstock.tanggal', '<', $datefilterstart)->where('kode_barang', $list->kode_barang)->sum(DB::raw('ABS(keluar)'));

                    $results['stock_masuk'][$list->kode_barang] = HistoryStock::where('kode_gudang',$kode_gudang)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum('masuk');

                    $results['stock_keluar'][$list->kode_barang] = HistoryStock::where('kode_gudang',$kode_gudang)->whereBetween('db_arusstock.tanggal', [$datefilterstart, $datefilterend])->where('kode_barang', $list->kode_barang)->sum(DB::raw('ABS(keluar)'));

                    $results['stock_akhir'][$list->kode_barang] = $results['stock_awal'][$list->kode_barang] + $results['stock_masuk'][$list->kode_barang] - $results['stock_keluar'][$list->kode_barang];
        
                    $results['barang'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    $results['kategori'][$list->kode_barang] = Kategori::where('id', $results['barang'][$list->kode_barang]->kode_jenis)->first();
                    $results['merk'][$list->kode_barang] = Merk::where('id', $results['barang'][$list->kode_barang]->kode_brand)->first();
                    $results['supplier'][$list->kode_barang] = Supplier::where('id', $results['barang'][$list->kode_barang]->kode_supplier)->first();
                }
            }
                
            return response()->json([
                'status_message' => 'success',
                'note' => 'Proses data berhasil',
                'count_all_data' => $results['list']->total(),
                'count_view_data' => $vd,
                'keysearch' => $request->keysearch,
                'results' => $results
            ]);
        }
    }
}