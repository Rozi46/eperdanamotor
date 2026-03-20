<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Kategori, Satuan, Barang, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Akun, JurnalUmum, Pembelian, ListPembelian, ReturPembelian, ListReturPembelian, Penerimaan, ListPenerimaan, Hutang, HutangBayar, Penjualan, ListPenjualan, ReturPenjualan, ListReturPenjualan, Pengiriman, ListPengiriman, Piutang, PiutangBayar, HistoryStock, HistoryKas, KasKeluar, KasMasuk, Mutasi, MutasiKirim, MutasiTerima, PenyesuaianStock, FetchData};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServiceHutang
{
    public function listhutang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menuhutang')->first();
            
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

            $results['list'] = DB::table('db_hutang')
                ->join('db_pembelian', 'db_hutang.code_data', '=', 'db_pembelian.code_data')
                ->join('db_supplier', 'db_pembelian.kode_supplier', '=', 'db_supplier.id')
                ->where('db_hutang.sisa', '<>', 0)
                ->where('db_hutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_hutang.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_hutang.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_hutang.jumlah','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_hutang.nomor', 'DESC')
                ->orderBy('db_hutang.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);


            foreach($results['list'] as $key => $data){             
                $results['user_input'][$data->code_data] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_pembelian'][$data->code_data] = Pembelian::where('nomor', $data->nomor)->first();
                $results['detail_supplier'][$data->code_data] = Supplier::where('id', $results['detail_pembelian'][$data->code_data]->kode_supplier)->first();
                
                // Ambil semua tanggal bayar terkait dengan transaksi ini
                $results['detail_tanggal_bayar'][$data->nomor] = DB::table('db_hutang_bayar')
                    ->where('nomor_hutang', $data->nomor)
                    ->select('tanggal', 'jumlah')
                    ->get();
            }

            $results['sum_jumlah'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('jumlah');
            $results['sum_bayar'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('bayar');
            $results['sum_sisa'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('sisa');

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function listhutangpersupplier($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error', 'note' => 'Data tidak ditemukan', 'results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menuhutang')->first();

            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error', 'note' => 'Tidak ada akses', 'results' => $object]);
            }

            // Set pagination value
            $vd = $request->vd ?: '20';

            $results['list'] = DB::table('db_hutang')
                ->join('db_pembelian', 'db_hutang.code_data', '=', 'db_pembelian.code_data')
                ->join('db_supplier', 'db_pembelian.kode_supplier', '=', 'db_supplier.id')
                ->select(
                    'db_supplier.id as id_data',
                    'db_supplier.code_data as code_data', 
                    'db_supplier.nama as supplier_nama',
                    DB::raw('SUM(db_hutang.sisa) as total_sisa'), // Total piutang yang tersisa
                    DB::raw('COUNT(DISTINCT db_pembelian.nomor) as jumlah_pembelian'), // Jumlah transaksi unik
                    'db_pembelian.kode_kantor as kode_kantor'
                )
                ->where('db_hutang.sisa', '<>', 0)
                ->where('db_hutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function ($query) use ($request) {
                    $keysearch = $request->keysearch; // Pastikan keysearch sudah divalidasi
                    $query->where('db_hutang.nomor', 'LIKE', '%' . $keysearch . '%')
                        ->orWhere('db_supplier.nama', 'LIKE', '%' . $keysearch . '%')
                        ->orWhere('db_pembelian.nomor', 'LIKE', '%' . $keysearch . '%');
                })
                ->groupBy('db_supplier.id','db_supplier.code_data', 'db_supplier.nama', 'db_pembelian.kode_kantor')
                ->orderBy('db_supplier.nama', 'ASC') // Mengurutkan berdasarkan nama supplier
                ->paginate($vd);

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function kartuhutang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error', 'note' => 'Data tidak ditemukan', 'results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menuhutang')->first();

            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error', 'note' => 'Tidak ada akses', 'results' => $object]);
            }

            // Set pagination value
            $vd = $request->vd ?: '20';

            $results['list'] = DB::table('db_hutang')
                ->join('db_pembelian', 'db_hutang.code_data', '=', 'db_pembelian.code_data')
                ->join('db_supplier', 'db_pembelian.kode_supplier', '=', 'db_supplier.id')
                ->where('db_pembelian.kode_supplier', $request->id_supplier)
                ->where('db_hutang.sisa', '<>', 0)
                ->where('db_hutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_hutang.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_hutang.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_supplier.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_pembelian.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_hutang.jumlah','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_hutang.nomor', 'DESC')
                ->orderBy('db_hutang.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){           
                $results['user_input'][$data->kode_user] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_pembelian'][$data->code_data] = Pembelian::where('nomor', $data->nomor)->first();
                $results['detail_supplier'][$data->code_data] = Supplier::where('id', $results['detail_pembelian'][$data->code_data]->kode_supplier)->first();
                
                // Ambil semua tanggal bayar terkait dengan transaksi ini
                $results['detail_tanggal_bayar'][$data->nomor] = HutangBayar::where('nomor_hutang', $data->nomor)
                    ->select('tanggal', 'jumlah')
                    ->get();
                
                $results['list_produk'][$data->nomor] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->orderBy('created_at', 'ASC')->get(); 
        
                foreach($results['list_produk'][$data->nomor] as $key => $list){
                    $results['qty_pembelian'] = ListPembelian::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_beli');

                    $results['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    
                    $results['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                    $results['satuan_produk'][$list->kode_barang] = Satuan::where('id', $results['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                    
                    $results['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $results['satuan_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get();                        
                }
            }

            $results['sum_jumlah'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('jumlah');
            $results['sum_bayar'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('bayar');
            $results['sum_sisa'] = Hutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('sisa');

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function listtagihan($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error', 'note' => 'Data tidak ditemukan', 'results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menutagihan')->first();

            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error', 'note' => 'Tidak ada akses', 'results' => $object]);
            }

            // Set pagination value
            $vd = $request->vd ?: '20';

            // Filter tanggal
            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("+1 days")->format('Y-m-d') . ' 23:59:59';
            if ($request->searchdate != '') {
                $searchdate = explode("sd", $request->searchdate);
                $datefilterstart = Carbon::parse($searchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($searchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            // Query utama dengan join ke db_hutang_bayar untuk mendapatkan semua tanggal bayar
            $results['list'] = DB::table('db_piutang')
                ->join('db_penjualan', 'db_piutang.code_data', '=', 'db_penjualan.code_data')
                ->join('db_customer', 'db_penjualan.kode_customer', '=', 'db_customer.id')
                ->where('db_piutang.sisa', '<>', 0)
                ->where('db_piutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function ($query) use ($request) {
                    $query->Where('db_piutang.nomor', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_piutang.tanggal', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_customer.nama', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_penjualan.nomor', 'LIKE', '%' . $request->keysearch . '%')
                        ->orWhere('db_piutang.jumlah', 'LIKE', '%' . $request->keysearch . '%');
                })
                ->orderBy('db_piutang.nomor', 'DESC')
                ->orderBy('db_piutang.created_at', 'DESC')
                ->paginate($vd);

            // Ambil data tanggal bayar untuk setiap transaksi
            foreach ($results['list'] as $key => $data) {
                $results['user_input'][$data->code_data] = User::where('kode_kantor', $viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->code_data] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_penjualan'][$data->code_data] = Penjualan::where('nomor', $data->nomor)->first();
                $results['detail_customer'][$data->code_data] = Customer::where('id', $results['detail_penjualan'][$data->code_data]->kode_customer)->first();
                
                // Ambil semua tanggal bayar terkait dengan transaksi ini
                $results['detail_tanggal_bayar'][$data->nomor] = DB::table('db_piutang_bayar')
                    ->where('nomor_piutang', $data->nomor)
                    ->select('tanggal', 'jumlah')
                    ->get();
            }

            $results['sum_jumlah'] = Piutang::where('kode_kantor', $viewadmin->kode_kantor)->where('sisa', '<>', 0)->sum('jumlah');
            $results['sum_bayar'] = Piutang::where('kode_kantor', $viewadmin->kode_kantor)->where('sisa', '<>', 0)->sum('bayar');
            $results['sum_sisa'] = Piutang::where('kode_kantor', $viewadmin->kode_kantor)->where('sisa', '<>', 0)->sum('sisa');

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function listtagihanpercustomer($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error', 'note' => 'Data tidak ditemukan', 'results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menutagihan')->first();

            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error', 'note' => 'Tidak ada akses', 'results' => $object]);
            }

            // Set pagination value
            $vd = $request->vd ?: '20';

            $results['list'] = DB::table('db_piutang')
                ->join('db_penjualan', 'db_piutang.code_data', '=', 'db_penjualan.code_data')
                ->join('db_customer', 'db_penjualan.kode_customer', '=', 'db_customer.id')
                ->select(
                    'db_customer.id as id_data',
                    'db_customer.code_data as code_data', 
                    'db_customer.nama as customer_nama',
                    DB::raw('SUM(db_piutang.sisa) as total_sisa'), // Total piutang yang tersisa
                    DB::raw('COUNT(DISTINCT db_penjualan.nomor) as jumlah_penjualan'), // Jumlah transaksi unik
                    'db_penjualan.kode_kantor as kode_kantor'
                )
                ->where('db_piutang.sisa', '<>', 0)
                ->where('db_piutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function ($query) use ($request) {
                    $keysearch = $request->keysearch; // Pastikan keysearch sudah divalidasi
                    $query->where('db_piutang.nomor', 'LIKE', '%' . $keysearch . '%')
                        ->orWhere('db_customer.nama', 'LIKE', '%' . $keysearch . '%')
                        ->orWhere('db_penjualan.nomor', 'LIKE', '%' . $keysearch . '%');
                })
                ->groupBy('db_customer.id','db_customer.code_data', 'db_customer.nama', 'db_penjualan.kode_kantor')
                ->orderBy('db_customer.nama', 'ASC') // Mengurutkan berdasarkan nama customer
                ->paginate($vd);

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function kartupiutang($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if (!$viewadmin) {
            return response()->json(['status_message' => 'error', 'note' => 'Data tidak ditemukan', 'results' => $object]);
        } else {
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu', '=', 'menutagihan')->first();

            if ($level_menu->access_rights == 'No' || $level_action->access_rights == 'No') {
                return response()->json(['status_message' => 'error', 'note' => 'Tidak ada akses', 'results' => $object]);
            }

            // Set pagination value
            $vd = $request->vd ?: '20';

            $results['list'] = DB::table('db_piutang')
                ->join('db_penjualan', 'db_piutang.code_data', '=', 'db_penjualan.code_data')
                ->join('db_customer', 'db_penjualan.kode_customer', '=', 'db_customer.id')
                ->where('db_penjualan.kode_customer', $request->id_customer)
                ->where('db_piutang.sisa', '<>', 0)
                ->where('db_piutang.kode_kantor', $viewadmin->kode_kantor)
                ->where(function($query) use ($request) {
                    $query->Where('db_piutang.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_piutang.tanggal','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_customer.nama','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_penjualan.nomor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('db_piutang.jumlah','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('db_piutang.nomor', 'DESC')
                ->orderBy('db_piutang.created_at', 'DESC')
                ->paginate($vd ? $vd : 20);

            foreach($results['list'] as $key => $data){             
                $results['user_input'][$data->nomor] = User::Where('kode_kantor',$viewadmin->kode_kantor)->where('id', $data->kode_user)->first();
                $results['detail_perusahaan'][$data->nomor] = Kantor::select('kantor')->where('id', $data->kode_kantor)->first();
                $results['detail_penjualan'][$data->nomor] = Penjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->first();
                $results['detail_customer'][$data->nomor] = Customer::where('id', $results['detail_penjualan'][$data->nomor]->kode_customer)->first();
                
                // Ambil semua tanggal bayar terkait dengan transaksi ini
                $results['detail_tanggal_bayar'][$data->nomor] = PiutangBayar::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor_piutang', $data->nomor)
                    ->select('tanggal', 'jumlah')
                    ->get();
                
                $results['list_produk'][$data->nomor] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $data->nomor)->orderBy('created_at', 'ASC')->get(); 
        
                foreach($results['list_produk'][$data->nomor] as $key => $list){
                    $results['qty_penjualan'] = ListPenjualan::Where('kode_kantor',$viewadmin->kode_kantor)->where('nomor', $list->nomor)->sum('jumlah_jual');

                    $results['detail_produk'][$list->kode_barang] = Barang::where('id', $list->kode_barang)->first();
                    
                    $results['satuan_barang_produk'][$list->kode_barang] = Satuan::where('id', $list->kode_satuan)->first();

                    // $results['satuan_produk'][$list->kode_barang] = Satuan::where('id', $results['detail_produk'][$list->kode_barang]->kode_satuan)->first();
                    
                    $results['satuan_barang_pecahan'][$list->kode_barang] = Satuan::where('kode_pecahan', $results['satuan_barang_produk'][$list->kode_barang]->id)->orderBy('nama', 'ASC')->get();                        
                }
            }


            // $results['list'] = Piutang::with([
            //     'penjualan.customer',
            //     'penjualan.user',
            //     'penjualan.kantor',
            //     'penjualan.listPenjualan.barang',
            //     'penjualan.listPenjualan.satuan',
            //     'piutangBayar'
            // ])
            // // ->withSum('penjualan.listPenjualan as qty_penjualan', 'jumlah_jual')
            // ->whereHas('penjualan', fn($q) =>
            //     $q->where('kode_customer', $request->id_customer)
            // )
            // ->where('sisa','<>',0)
            // ->where('kode_kantor',$viewadmin->kode_kantor)
            // ->paginate($vd ?? 20);


            $results['sum_jumlah'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('jumlah');
            $results['sum_bayar'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('bayar');
            $results['sum_sisa'] = Piutang::Where('kode_kantor',$viewadmin->kode_kantor)->where('sisa', '<>',0)->sum('sisa');


            // $results['list'] = Piutang::with([
            //         // relasi penjualan
            //         'penjualan.customer',
            //         'penjualan.user',
            //         'penjualan.kantor',

            //         // list produk + relasi turunannya
            //         'penjualan.listPenjualan.barang',
            //         'penjualan.listPenjualan.satuan',

            //         // pembayaran piutang
            //         'piutangBayar:tanggal,jumlah,nomor_piutang'
            //     ])
            //     ->whereHas('penjualan', function ($q) use ($request) {
            //         $q->where('kode_customer', $request->id_customer);
            //     })
            //     ->where('sisa', '<>', 0)
            //     ->where('kode_kantor', $viewadmin->kode_kantor)
            //     ->where(function ($query) use ($request) {

            //         $query->where('nomor', 'ILIKE', "%{$request->keysearch}%")
            //             ->orWhere('tanggal', 'ILIKE', "%{$request->keysearch}%")
            //             ->orWhere('jumlah', 'ILIKE', "%{$request->keysearch}%")
            //             ->orWhereHas('penjualan', function ($q) use ($request) {

            //                 $q->where('nomor', 'ILIKE', "%{$request->keysearch}%")
            //                     ->orWhereHas('customer', function ($qc) use ($request) {
            //                         $qc->where('nama', 'ILIKE', "%{$request->keysearch}%");
            //                     });

            //             });

            //     })
            //     ->orderBy('nomor', 'DESC')
            //     ->orderBy('created_at', 'DESC')
            //     ->paginate($vd ?? 20);


            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['list']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }    
}