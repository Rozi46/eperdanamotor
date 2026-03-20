<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceHistorykas
{
    public function historykas($request)
    {
        $object = [];    
        // Validasi user berdasarkan ID dan token
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
    
        if (!$viewadmin) {
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        } else {
            // Cek akses menu dan action level admin
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', 'menufinance')->first();
                                    
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', 'historykas')->first();
    
            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
    
            // Default pagination value
            $vd = $request->vd ?? '999999999999999';
    
            // Date range filter
            $datefilterstart = Carbon::now()->subDays(30)->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->addDay()->format('Y-m-d') . ' 23:59:59';
    
            // Jika ada pencarian tanggal khusus
            if ($request->has('searchdate') && $request->searchdate != '') {
                $searchdate = explode("sd", $request->searchdate);
                $datefilterstart = Carbon::parse(trim($searchdate[0]))->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse(trim($searchdate[1]))->format('Y-m-d') . ' 23:59:59';
            }
    
            // Menghitung saldo awal
            $results['saldo_awal'] = DB::table('db_aruskas')
                ->selectRaw('SUM(debet) - SUM(kredit) as total')
                ->where('tanggal', '<', $datefilterstart)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->first();
    
            // Menghitung saldo akhir
            $results['saldo_akhir'] = DB::table('db_aruskas')
                ->selectRaw('SUM(debet) - SUM(kredit) as total')
                ->where('tanggal', '<=', $datefilterend)
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->first();
    
            // Menentukan urutan sort untuk hasil query
            $sort = $request->type == 'export' ? 'ASC' : 'DESC';
    
            // Query data transaksi dengan filter
            $results['list'] = DB::table('db_aruskas')
                ->where('kode_kantor', $viewadmin->kode_kantor)
                ->whereBetween('tanggal', [$datefilterstart, $datefilterend])
                ->where(function($query) use ($request) {
                    $query->where('code_data', 'LIKE', '%' . $request->keysearch . '%')
                          ->orWhere('nomor', 'LIKE', '%' . $request->keysearch . '%')
                          ->orWhere('tanggal', 'LIKE', '%' . $request->keysearch . '%')
                          ->orWhere('debet', 'LIKE', '%' . $request->keysearch . '%')
                          ->orWhere('kredit', 'LIKE', '%' . $request->keysearch . '%');
                })
                ->orderBy('tanggal', $sort)
                ->orderBy('created_at', $sort)
                ->paginate($vd ? (int)$vd : 999999999999999);
    
            // Ambil detail perusahaan dan user transaksi berdasarkan ID
            foreach ($results['list'] as $key => $data) {
                $results['detail_perusahaan'][$data->id] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['user_transaksi'][$data->id] = User::select('code_data', 'full_name')->where('id', $data->kode_user)->first();
            }
    
            // Return hasil dalam format JSON
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }
    
}