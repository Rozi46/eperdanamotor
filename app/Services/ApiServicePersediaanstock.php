<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePersediaanstock
{  
    public function persediaanbarang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menugudang')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','persediaanbarang')->first();

            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }

            $vd = $request->vd ?: '20';
            
            // Ambil barang dan join yang dibutuhkan dalam satu query
            $results['list'] = DB::table('db_arusstock')
                ->join('db_barang', 'db_arusstock.kode_barang', '=', 'db_barang.id')
                ->join('db_satuan_barang', 'db_barang.kode_satuan_default', '=', 'db_satuan_barang.id')
                ->select(
                    'db_barang.id',
                    'db_barang.code_data',
                    'db_barang.kode',
                    'db_barang.nama AS nama_barang',
                    'db_satuan_barang.nama AS nama_satuan',
                    'db_barang.kode_satuan_default',
                    'db_arusstock.kode_barang'
                )
                ->where('db_barang.type_produk', 'Barang')
                ->where(function ($query) use ($request) {
                    $query->where('db_barang.code_data', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.kode', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_satuan_barang.nama', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_barang.kode_satuan_default', 'LIKE', '%' . $request->keysearch . '%');
                })
                ->groupBy(
                    'db_barang.id',
                    'db_barang.code_data',
                    'db_barang.kode',
                    'db_barang.nama',
                    'db_satuan_barang.nama',
                    'db_barang.kode_satuan_default',
                    'db_arusstock.kode_barang'
                )
                ->orderBy('db_barang.nama', 'ASC')
                ->paginate($vd);

            // Ambil semua kode_barang yang ada di hasil pencarian
            $kodeBarangList = $results['list']->pluck('kode_barang')->toArray();

            // Lakukan batch query untuk stok akhir, barang, kategori, merk, supplier, gudang
            // $stokAkhirList = HistoryStock::whereIn('kode_barang', $kodeBarangList)
            //     ->select('kode_barang', DB::raw('SUM(masuk - keluar) as stock_akhir'))
            //     ->groupBy('kode_barang')
            //     ->pluck('stock_akhir', 'kode_barang');

            $stokAkhirList = HistoryStock::whereIn('kode_barang', $kodeBarangList)
                ->select(
                    'kode_barang',
                    DB::raw("
                        SUM(
                            masuk +
                            CASE
                                WHEN keluar < 0 THEN keluar
                                ELSE -keluar
                            END
                        ) AS stock_akhir
                    ")
                )
                ->groupBy('kode_barang')
                ->pluck('stock_akhir', 'kode_barang');


            $barangList = Barang::whereIn('id', $kodeBarangList)->get()->keyBy('id');
            $kategoriList = Kategori::whereIn('id', $barangList->pluck('kode_jenis'))->get()->keyBy('id');
            $merkList = Merk::whereIn('id', $barangList->pluck('kode_brand'))->get()->keyBy('id');
            $supplierList = Supplier::whereIn('id', $barangList->pluck('kode_supplier'))->get()->keyBy('id');

            $results['list_gudang'] = Gudang::where('status_data', 'Aktif')
                ->orderBy('nama', 'ASC')
                ->get();

            foreach($results['list'] as $list){ 
                $kodeBarang = $list->kode_barang;
            
                // Set stok akhir dari hasil query batch
                $results['stock_akhir'][$kodeBarang] = $stokAkhirList[$kodeBarang] ?? 0;
            
                // Pastikan data barang ada sebelum diakses
                if (isset($barangList[$kodeBarang])) {
                    $results['barang'][$kodeBarang] = $barangList[$kodeBarang];
            
                    // Set kategori, merk, supplier dengan pengecekan null
                    $results['kategori'][$kodeBarang] = $kategoriList[$results['barang'][$kodeBarang]->kode_jenis] ?? null;
                    $results['merk'][$kodeBarang] = $merkList[$results['barang'][$kodeBarang]->kode_brand] ?? null;
                    $results['supplier'][$kodeBarang] = $supplierList[$results['barang'][$kodeBarang]->kode_supplier] ?? null;
                } else {
                    $results['barang'][$kodeBarang] = null;
                    $results['kategori'][$kodeBarang] = null;
                    $results['merk'][$kodeBarang] = null;
                    $results['supplier'][$kodeBarang] = null;
                }
            
                // Set stok per gudang
                // foreach($results['list_gudang'] as $gudang){
                //     $results['stok_pergudang'][$kodeBarang][$gudang->code_data] = HistoryStock::where('kode_gudang', $gudang->id)
                //         ->where('kode_barang', $kodeBarang)
                //         ->sum(DB::raw('masuk - keluar'));
                // }

                foreach ($results['list_gudang'] as $gudang) {
                    $results['stok_pergudang'][$kodeBarang][$gudang->code_data] =
                        HistoryStock::where('kode_gudang', $gudang->id)
                            ->where('kode_barang', $kodeBarang)
                            ->sum(DB::raw("
                                masuk +
                                CASE
                                    WHEN keluar < 0 THEN keluar
                                    ELSE -keluar
                                END
                            "));
                }

            
                // Ambil pembelian tertinggi dengan satu query
                $pembelianTertinggi = ListPembelian::where('kode_barang', $kodeBarang)
                ->orderBy('harga', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_beli'][$kodeBarang] = $pembelianTertinggi->harga ?? 'Belum ditentukan';
                $results['tanggal_beli'][$kodeBarang] = $pembelianTertinggi->tanggal ?? 'Belum ditentukan';
            
                // Ambil penjualan terakhir dengan satu query
                $penjualanTerakhir = ListPenjualan::where('kode_barang', $kodeBarang)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('tanggal', 'desc')
                    ->first();
                
                $results['harga_jual'][$kodeBarang] = $penjualanTerakhir->harga ?? 'Belum ditentukan';
                $results['tanggal_jual'][$kodeBarang] = $penjualanTerakhir->tanggal ?? 'Belum ditentukan';
            }
            

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $results,'vd' => $vd,'keysearch' => $request->keysearch]);
        }
    }
}