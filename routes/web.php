<?php
require __DIR__.'/api.php';

use Illuminate\Support\Facades\{Route, Session};
use App\Http\Controllers\{SistemController,ActionController,ApiController};

Route::prefix('admin')->name('admin.')->group(function () {

    // /admin
    Route::get('/', function () {
        return redirect()->route('admin.administration');
    });

    // /admin/administration
    Route::get('/administration', function () {
        if (Session::get('admin_login_perdana')) {
            return redirect()->route('admin.dash');
        } 
		
        return view('admin.AdminOne.login', ['url' => 'login']);
    })->name('administration');

    // redirect login ke administration
    Route::get('/login', function () {
        return redirect()->route('admin.administration');
    })->name('login');

    // auth process
    Route::post('/login', [SistemController::class, 'login']);
    Route::get('/logout', [SistemController::class, 'logout'])->name('logout');

    // dashboard
    Route::get('/dash', [SistemController::class, 'dash'])->name('dash');

});

// Route::group(['middleware' => 'auth.jwt'], function(){

	// Cashier
		Route::get('/cash', function () {
			return redirect('/cash/login');
		});

		Route::get('cash.login', function () {
			return redirect('/cash/login');
		});

		Route::get('/cash/login', function () {
			if(session('admin_login_perdana_cash')){
				return redirect('cash.dash');
			}else{
				return view('admin.AdminOne.cashier.login',['url' => 'login_cashier']);
				// return view('maintenance');
			}
		});

		Route::get('/cash.dash', function () {
			return redirect('/cash/dash');
		});

		Route::post('/cash/login',[SistemController::class, 'loginCashier']);
		Route::get('/cash/dash',[SistemController::class, 'dashCashier']);
		Route::get('/cash/logout',[SistemController::class, 'logoutCashier']);
		Route::get('/cash/listopcustomer',[SistemController::class, 'cashlistopcustomer']);
		Route::get('/cash/listbarangtransaksi',[SistemController::class, 'cashlistbarangtransaksi']);
		Route::post('/cash/saveprodpenjualan',[ActionController::class, 'cashsaveprodpenjualan']);
		Route::get('/cash/viewpenjualan',[SistemController::class, 'cashviewpenjualan']);
		Route::get('/cash/listprodpenjualan',[SistemController::class, 'cashlistprodpenjualan']);
		Route::get('/cash/summarypenjualan',[SistemController::class, 'cashsummarypenjualan']);
		Route::get('/cash/listsatuanhargapenjualan',[ActionController::class, 'cashlistsatuanhargapenjualan']);
		Route::post('/cash/uphargapenjualan',[ActionController::class, 'cashuphargapenjualan']);
		Route::post('/cash/upqtypenjualan',[ActionController::class, 'cashupqtypenjualan']);
		Route::post('/cash/updiscpenjualan',[ActionController::class, 'cashupdiscpenjualan']);
		Route::post('/cash/updiscpenjualan2',[ActionController::class, 'cashupdiscpenjualan2']);
		Route::post('/cash/upsummarypenjualan',[ActionController::class, 'cashupsummarypenjualan']);
		Route::get('/cash/deleteprodpenjualan',[ActionController::class, 'cashdeleteprodpenjualan']);
		Route::get('/cash/deletepenjualan',[ActionController::class, 'cashdeletepenjualan']);
		Route::get('/cash/updatepenjualan',[ActionController::class, 'cashupdatepenjualan']);
		Route::get('/cash/historypenjualanbarang',[SistemController::class, 'cashhistorypenjualanbarang']);
		Route::get('/cash/exportpenjualanbarang',[SistemController::class, 'cashexportpenjualanbarang']);
		Route::get('/cash/printsalesorder',[SistemController::class, 'cashprintsalesorder']);
		Route::get('/cash/persediaanbarang',[SistemController::class, 'cashpersediaanbarang']);
		Route::get('/cash/pendingpenjualan',[ActionController::class, 'cashpendingpenjualan']);
		Route::get('/cash/editaccount',[SistemController::class, 'casheditaccount']);
		Route::post('/cash/editaccount',[ActionController::class, 'casheditaccount']);
		Route::post('cash/editpassaccount',[ActionController::class, 'casheditpassaccount']);
	// end Cashier	

	Route::get('/admin/getsatuanpecahan',[SistemController::class, 'getsatuanpecahan']);

	// Auto Complete
	// Route::get('/admin/getopsupplier',[SistemController::class, 'getopsupplier']);
	Route::get('/admin/listopsupplier',[SistemController::class, 'listopsupplier']);
	Route::get('/admin/listbarangtransaksi',[SistemController::class, 'listbarangtransaksi']);
	Route::get('/admin/listopcustomer',[SistemController::class, 'listopcustomer']);
	Route::get('/admin/dash',[SistemController::class, 'dash']);

	// Data Pembelian Barang
	Route::get('/admin/getcodepembelian',[ActionController::class, 'getcodepembelian']);
	Route::get('/admin/menupembelianbarang',[SistemController::class, 'menupembelianbarang']);
	Route::post('/admin/saveprodpembelian',[ActionController::class, 'saveprodpembelian']);
	Route::get('/admin/viewpembelian',[SistemController::class, 'viewpembelian']);
	Route::get('/admin/listprodpembelian',[SistemController::class, 'listprodpembelian']);
	Route::get('/admin/summarypembelian',[SistemController::class, 'summarypembelian']);
	Route::get('/admin/deleteprodpembelian',[ActionController::class, 'deleteprodpembelian']);
	Route::get('/admin/deletepembelian',[ActionController::class, 'deletepembelian']);
	Route::post('/admin/upppnpembelian',[ActionController::class, 'upppnpembelian']);
	Route::post('/admin/uphargapembelian',[ActionController::class, 'uphargapembelian']);
	Route::post('/admin/upqtypembelian',[ActionController::class, 'upqtypembelian']);
	Route::post('/admin/updiscpembelian',[ActionController::class, 'updiscpembelian']);
	Route::post('/admin/updiscpembelian2',[ActionController::class, 'updiscpembelian2']);
	Route::post('/admin/updiscpembelian3',[ActionController::class, 'updiscpembelian3']);
	Route::post('/admin/updiscpembelianharga',[ActionController::class, 'updiscpembelianharga']);
	Route::post('/admin/updiscpembelianharga2',[ActionController::class, 'updiscpembelianharga2']);
	Route::post('/admin/updiscpembelianharga3',[ActionController::class, 'updiscpembelianharga3']);
	Route::post('/admin/upsummarypembelian',[ActionController::class, 'upsummarypembelian']);
	Route::post('/admin/upsummarypembeliancash',[ActionController::class, 'upsummarypembeliancash']);
	Route::get('/admin/listsatuanharga',[ActionController::class, 'listsatuanharga']);
	Route::get('/admin/updatepembelian',[ActionController::class, 'updatepembelian']);
	Route::get('/admin/historypembelianbarang',[SistemController::class, 'historypembelianbarang']);
	Route::get('/admin/exportpembelianbarang',[SistemController::class, 'exportpembelianbarang']);
	Route::get('/admin/printpurchaseorder',[SistemController::class, 'printpurchaseorder']);

	// Data Penerimaan Barang
	Route::get('/admin/getcodepenerimaan',[ActionController::class, 'getcodepenerimaan']);
	Route::get('/admin/menupenerimaanbarang',[SistemController::class, 'penerimaanbarang']);
	Route::get('/admin/listoppembelian',[SistemController::class, 'listoppembelian']);
	Route::get('/admin/listopprodpembelian',[SistemController::class, 'listopprodpembelian']);
	Route::get('/admin/detailoppembelian',[SistemController::class, 'detailoppembelian']);
	Route::get('/admin/listprodpenerimaan',[SistemController::class, 'listprodpenerimaan']);
	Route::post('/admin/savepenerimaan',[ActionController::class, 'savepenerimaan']);
	Route::get('/admin/viewpenerimaan',[SistemController::class, 'viewpenerimaan']);
	Route::get('/admin/deleterdobarang',[ActionController::class, 'deleterdobarang']);
	Route::get('/admin/updaterdobarang',[ActionController::class, 'updaterdobarang']);
	Route::get('/admin/historypenerimaan',[SistemController::class, 'historypenerimaan']);
	Route::get('/admin/exportpenerimaanbarang',[SistemController::class, 'exportpenerimaanbarang']);
	Route::get('/admin/printpenerimaan',[SistemController::class, 'printpenerimaan']);

	// Data Retur Pembelian
	Route::get('/admin/menupembelianretur',[\App\Http\Controllers\PembelianSistemController::class, 'menupembelianretur']);
	Route::get('/admin/listopinvpo',[SistemController::class, 'listopinvpo']);
	Route::get('/admin/listprodinvpo',[SistemController::class, 'listprodinvpo']);
	Route::get('/admin/listtablesnreturpo',[SistemController::class, 'listtablesnreturpo']);
	Route::get('/admin/listsnreturpo',[SistemController::class, 'listsnreturpo']);
	Route::post('/admin/savereturpembelian',[ActionController::class, 'savereturpembelian']);
	Route::get('/admin/viewreturpembelian',[SistemController::class, 'viewreturpembelian']);
	Route::get('/admin/deletereturpo',[ActionController::class, 'deletereturpo']);
	Route::post('/admin/updatereturpo',[ActionController::class, 'updatereturpo']);
	Route::get('/admin/historyreturpembelian',[SistemController::class, 'historyreturpembelian']);
	Route::get('/admin/exportreturpembelian',[SistemController::class, 'exportreturpembelian']);
	Route::get('/admin/printreturpo',[SistemController::class, 'printreturpo']);

	// Data Penjualan Barang
	Route::get('/admin/getcodepenjualan',[ActionController::class, 'getcodepenjualan']);
	Route::get('/admin/menupenjualanbarang',[SistemController::class, 'menupenjualanbarang']);
	Route::post('/admin/saveprodpenjualan',[ActionController::class, 'saveprodpenjualan']);
	Route::get('/admin/viewpenjualan',[SistemController::class, 'viewpenjualan']);
	Route::get('/admin/listprodpenjualan',[SistemController::class, 'listprodpenjualan']);
	Route::get('/admin/summarypenjualan',[SistemController::class, 'summarypenjualan']);
	Route::get('/admin/deleteprodpenjualan',[ActionController::class, 'deleteprodpenjualan']);
	Route::get('/admin/deletepenjualan',[ActionController::class, 'deletepenjualan']);
	Route::post('/admin/upppnpenjualann',[ActionController::class, 'upppnpenjualan']);
	Route::post('/admin/uphargapenjualan',[ActionController::class, 'uphargapenjualan']);
	Route::post('/admin/upqtypenjualan',[ActionController::class, 'upqtypenjualan']);
	Route::post('/admin/updiscpenjualan',[ActionController::class, 'updiscpenjualan']);
	Route::post('/admin/updiscpenjualan2',[ActionController::class, 'updiscpenjualan2']);
	Route::post('/admin/upsummarypenjualan',[ActionController::class, 'upsummarypenjualan']);
	Route::get('/admin/listsatuanhargapenjualan',[ActionController::class, 'listsatuanhargapenjualan']);
	Route::get('/admin/updatepenjualan',[ActionController::class, 'updatepenjualan']);
	Route::get('/admin/historypenjualanbarang',[SistemController::class, 'historypenjualanbarang']);
	Route::get('/admin/exportpenjualanbarang',[SistemController::class, 'exportpenjualanbarang']);
	Route::get('/admin/printsalesorder',[SistemController::class, 'printsalesorder']);

	// Data Pengiriman Barang
	Route::get('/admin/getcodepengiriman',[ActionController::class, 'getcodepengiriman']);
	Route::get('/admin/menupengirimanbarang',[SistemController::class, 'pengirimanbarang']);
	Route::get('/admin/listoppenjualan',[SistemController::class, 'listoppenjualan']);
	Route::get('/admin/listopprodpenjualan',[SistemController::class, 'listopprodpenjualan']);
	Route::get('/admin/detailoppenjualan',[SistemController::class, 'detailoppenjualan']);
	Route::get('/admin/listprodpengiriman',[SistemController::class, 'listprodpengiriman']);
	Route::post('/admin/savepengiriman',[ActionController::class, 'savepengiriman']);
	Route::get('/admin/viewpengiriman',[SistemController::class, 'viewpengiriman']);
	Route::get('/admin/deletersobarang',[ActionController::class, 'deletersobarang']);
	Route::get('/admin/updatersobarang',[ActionController::class, 'updatersobarang']);
	Route::get('/admin/historypengiriman',[SistemController::class, 'historypengiriman']);
	Route::get('/admin/exportpengirimanbarang',[SistemController::class, 'exportpengirimanbarang']);
	Route::get('/admin/printpengiriman',[SistemController::class, 'printpengiriman']);

	// Data Penyesuaian Stock Barang
	Route::get('/admin/getcodepenyesuaianstock',[ActionController::class, 'getcodepenyesuaianstock']);
	Route::get('/admin/penyesuaianstockbarang',[SistemController::class, 'penyesuaianstockbarang']);
	Route::post('/admin/penyesuaianstockbarang',[ActionController::class, 'penyesuaianstockbarang']);
	Route::get('/admin/listbarangstockopname',[SistemController::class, 'listbarangstockopname']);
	Route::get('/admin/liststockbarangSO',[SistemController::class, 'liststockbarangSO']);
	Route::post('/admin/savepenyesuaianstock',[ActionController::class, 'savepenyesuaianstock']);
	Route::get('/admin/historypenyesuaianstockbarang',[SistemController::class, 'historypenyesuaianstockbarang']);
	Route::get('/admin/exporthistorypenyesuaianstockbarang',[SistemController::class, 'exporthistorypenyesuaianstockbarang']);		
	Route::get('/admin/updatenomorpenyesuaian',[ActionController::class, 'updatenomorpenyesuaian']);
	// Data Harga Barang
	Route::get('/admin/hargabarang',[SistemController::class, 'hargabarang']);
	Route::get('/admin/exporthargabarang',[SistemController::class, 'exporthargabarang']);

	// Data History Stock Barang
	Route::get('/admin/historystockbarang',[SistemController::class, 'historystockbarang']);
	Route::get('/admin/exporthistorystockbarang',[SistemController::class, 'exporthistorystockbarang']);

	// Data Persediaan Barang
	Route::get('/admin/persediaanbarang',[SistemController::class, 'persediaanbarang']);
	Route::get('/admin/exportpersediaanbarang',[SistemController::class, 'exportpersediaanbarang']);

	// Mutasi Kirim
	Route::get('/admin/getcodemutasikirim',[ActionController::class, 'getcodemutasikirim']);
	Route::get('/admin/mutasikirim',[SistemController::class, 'mutasikirim']);
	Route::post('/admin/savemutasikirim',[ActionController::class, 'savemutasikirim']);
	Route::get('/admin/viewmutasikirim',[SistemController::class, 'viewmutasikirim']);
	Route::get('/admin/listprodmutasikirim',[SistemController::class, 'listprodmutasikirim']);
	Route::get('/admin/updatemutasikirim',[ActionController::class, 'updatemutasikirim']);
	Route::get('/admin/historymutasikirim',[SistemController::class, 'historymutasikirim']);
	Route::get('/admin/deleteprodmutasikirim',[ActionController::class, 'deleteprodmutasikirim']);
	Route::post('/admin/upqtymutasikirim',[ActionController::class, 'upqtymutasikirim']);
	Route::get('/admin/listsatuanmutasikirim',[ActionController::class, 'listsatuanmutasikirim']);
	Route::get('/admin/deletemutasikirim',[ActionController::class, 'deletemutasikirim']);
	Route::get('/admin/exportmutasikirim',[SistemController::class, 'exportmutasikirim']);
	Route::get('/admin/printmutasikirim',[SistemController::class, 'printmutasikirim']);

	// Mutasi Terima
	Route::get('/admin/getcodemutasiterima',[ActionController::class, 'getcodemutasiterima']);
	Route::get('/admin/mutasiterima',[SistemController::class, 'mutasiterima']);
	Route::get('/admin/listopmutasi',[SistemController::class, 'listopmutasi']);
	Route::get('/admin/detailopmutasi',[SistemController::class, 'detailopmutasi']);
	Route::get('/admin/listprodmutasiterima',[SistemController::class, 'listprodmutasiterima']);
	Route::post('/admin/savemutasiterima',[ActionController::class, 'savemutasiterima']);
	Route::get('/admin/viewmutasiterima',[SistemController::class, 'viewmutasiterima']);
	Route::get('/admin/historymutasiterima',[SistemController::class, 'historymutasiterima']);
	Route::get('/admin/deletemutasiterima',[ActionController::class, 'deletemutasiterima']);
	Route::get('/admin/updatemutasiterima',[ActionController::class, 'updatemutasiterima']);
	Route::get('/admin/printmutasiterima',[SistemController::class, 'printmutasiterima']);
	Route::get('/admin/exportmutasiterima',[SistemController::class, 'exportmutasiterima']);

	// Penerimaan Kas
	Route::get('/admin/getcodepay',[ActionController::class, 'getcodepay']);
	Route::get('/admin/menupenerimaankas',[SistemController::class, 'menupenerimaankas']);	
	Route::post('/admin/saveppenerimaankas',[ActionController::class, 'saveppenerimaankas']);
	Route::get('/admin/historypenerimaankas',[SistemController::class, 'historypenerimaankas']);
	Route::get('/admin/exportpenerimaankas',[SistemController::class, 'exportpenerimaankas']);
	Route::get('/admin/viewpenerimaankas',[SistemController::class, 'viewpenerimaankas']);
	Route::get('/admin/printpenerimaankas',[SistemController::class, 'printpenerimaankas']);
	Route::get('/admin/deletepenerimaankas',[ActionController::class, 'deletepenerimaankas']);

	// Pengeluaran Kas
	Route::get('/admin/getcodepaykas',[ActionController::class, 'getcodepaykas']);
	Route::get('/admin/menupengeluarankas',[SistemController::class, 'menupengeluarankas']);	
	Route::post('/admin/saveppengeluarankas',[ActionController::class, 'saveppengeluarankas']);
	Route::get('/admin/historypengeluarankas',[SistemController::class, 'historypengeluarankas']);
	Route::get('/admin/exportpengeluarankas',[SistemController::class, 'exportpengeluarankas']);
	Route::get('/admin/viewpengeluarankas',[SistemController::class, 'viewpengeluarankas']);
	Route::get('/admin/printpengeluarankas',[SistemController::class, 'printpengeluarankas']);
	Route::get('/admin/deletepengeluarankas',[ActionController::class, 'deletepengeluarankas']);

	// Pembayaran Pembelian - Purchase Payment - Hutang
	Route::get('/admin/getcodepurchasepayment',[ActionController::class, 'getcodepurchasepayment']);
	Route::get('/admin/menupembayaranhutang',[SistemController::class, 'menupembayaranhutang']);
	Route::get('/admin/listpurchasepayment',[SistemController::class, 'listpurchasepayment']);
	Route::get('/admin/detailpurchasepayment',[SistemController::class, 'detailpurchasepayment']);
	Route::post('/admin/savepurchasepayment',[ActionController::class, 'savepurchasepayment']);
	Route::get('/admin/historypembayaranhutang',[SistemController::class, 'historypembayaranhutang']);
	Route::get('/admin/exportpembayaranhutang',[SistemController::class, 'exportpurchasepayment']);
	Route::get('/admin/viewpurchasepayment',[SistemController::class, 'viewpurchasepayment']);
	Route::get('/admin/printpurchasepayment',[SistemController::class, 'printpurchasepayment']);

	// Pembayaran Penjualan - Sales Payment - Piutang
	Route::get('/admin/getcodesalespayment',[ActionController::class, 'getcodesalespayment']);
	Route::get('/admin/menupembayaranpiutang',[SistemController::class, 'menupembayaranpiutang']);
	Route::get('/admin/listsalespayment',[SistemController::class, 'listsalespayment']);
	Route::get('/admin/detailsalespayment',[SistemController::class, 'detailsalespayment']);
	Route::post('/admin/savesalespayment',[ActionController::class, 'savesalespayment']);
	Route::get('/admin/historypembayaranpiutang',[SistemController::class, 'historypembayaranpiutang']);
	Route::get('/admin/exportpembayaranpiutang',[SistemController::class, 'exportsalespayment']);
	Route::get('/admin/viewsalespayment',[SistemController::class, 'viewsalespayment']);
	Route::get('/admin/printsalespayment',[SistemController::class, 'printsalespayment']);

	//History Kas
	Route::get('/admin/historykas',[SistemController::class, 'historykas']);
	Route::get('/admin/exportkas',[SistemController::class, 'exportkas']);
	Route::get('/admin/printdatakas',[SistemController::class, 'printdatakas']);
	
	// Daftar Hutang
	Route::get('/admin/menuhutang',[SistemController::class, 'menuhutang']);
	Route::get('/admin/exportlisthutang',[SistemController::class, 'exportlisthutang']);
	Route::get('/admin/kartuhutang',[SistemController::class, 'kartuhutang']);
	Route::get('/admin/exportkartuhutang',[SistemController::class, 'exportkartuhutang']);
	
	// Tagihan
	Route::get('/admin/menutagihan',[SistemController::class, 'menutagihan']);
	Route::get('/admin/exportlisttagihan',[SistemController::class, 'exportlisttagihan']);
	Route::get('/admin/kartupiutang',[SistemController::class, 'kartupiutang']);
	Route::get('/admin/exportkartupiutang',[SistemController::class, 'exportkartupiutang']);

	// PPN
	Route::get('/admin/menuppn',[SistemController::class, 'menuppn']);
	Route::get('/admin/exportppn',[SistemController::class, 'exportppn']);

	// Rekap Pembelian Penjualan
	Route::get('/admin/rekappembelianpenjualan',[SistemController::class, 'rekappembelianpenjualan']);
	Route::get('/admin/exportrekappembelianpenjualan',[SistemController::class, 'exportrekappembelianpenjualan']);

	// Data Barang
	Route::get('/admin/listbarang',[SistemController::class, 'listbarang']);
	Route::get('/admin/exportlistbarang',[SistemController::class, 'exportlistbarang']);
	Route::get('/admin/newbarang',[SistemController::class, 'newbarang']);
	Route::post('/admin/newbarang',[ActionController::class, 'newbarang']);
	Route::get('/admin/editbarang',[SistemController::class, 'editbarang']);
	Route::post('/admin/editbarang',[ActionController::class, 'editbarang']);
	Route::get('/admin/deletebarang',[ActionController::class, 'deletebarang']);

	// Data Jasa
	Route::get('/admin/listjasa',[SistemController::class, 'listjasa']);
	Route::get('/admin/exportlistjasa',[SistemController::class, 'exportlistjasa']);
	Route::get('/admin/newjasa',[SistemController::class, 'newjasa']);
	Route::post('/admin/newjasa',[ActionController::class, 'newjasa']);
	Route::get('/admin/editjasa',[SistemController::class, 'editjasa']);
	Route::post('/admin/editjasa',[ActionController::class, 'editjasa']);
	Route::get('/admin/deletejasa',[ActionController::class, 'deletejasa']);

	// Satuan Barang
	Route::get('/admin/listsatuan',[SistemController::class, 'listsatuan']);
	Route::get('/admin/exportlistsatuan',[SistemController::class, 'exportlistsatuan']);
	Route::get('/admin/newsatuan',[SistemController::class, 'newsatuan']);
	Route::post('/admin/newsatuan',[ActionController::class, 'newsatuan']);
	Route::get('/admin/editsatuan',[SistemController::class, 'editsatuan']);
	Route::post('/admin/editsatuan',[ActionController::class, 'editsatuan']);
	Route::get('/admin/deletesatuan',[ActionController::class, 'deletesatuan']);

	// Kategori Barang
	Route::get('/admin/listkategori',[SistemController::class, 'listkategori']);
	Route::get('/admin/exportlistkategori',[SistemController::class, 'exportlistkategori']);
	Route::get('/admin/newkategori',[SistemController::class, 'newkategori']);
	Route::post('/admin/newkategori',[ActionController::class, 'newkategori']);
	Route::get('/admin/editkategori',[SistemController::class, 'editkategori']);
	Route::post('/admin/editkategori',[ActionController::class, 'editkategori']);
	Route::get('/admin/deletekategori',[ActionController::class, 'deletekategori']);

	// Merk Barang
	Route::get('/admin/listmerk',[SistemController::class, 'listmerk']);
	Route::get('/admin/exportlistmerk',[SistemController::class, 'exportlistmerk']);
	Route::get('/admin/newmerk',[SistemController::class, 'newmerk']);
	Route::post('/admin/newmerk',[ActionController::class, 'newmerk']);
	Route::get('/admin/editmerk',[SistemController::class, 'editmerk']);
	Route::post('/admin/editmerk',[ActionController::class, 'editmerk']);
	Route::get('/admin/deletemerk',[ActionController::class, 'deletemerk']);

	// Data Supplier
	Route::get('/admin/listsupplier',[SistemController::class, 'listsupplier']);
	Route::get('/admin/exportlistsupplier',[SistemController::class, 'exportlistsupplier']);
	Route::get('/admin/newsupplier',[SistemController::class, 'newsupplier']);
	Route::post('/admin/newsupplier',[ActionController::class, 'newsupplier']);
	Route::get('/admin/editsupplier',[SistemController::class, 'editsupplier']);
	Route::post('/admin/editsupplier',[ActionController::class, 'editsupplier']);
	Route::get('/admin/deletesupplier',[ActionController::class, 'deletesupplier']);
	Route::get('/admin/upstatussupplier',[ActionController::class, 'upstatussupplier']);

	// Data Customer
	Route::get('/admin/listcustomer',[SistemController::class, 'listcustomer']);
	Route::get('/admin/exportlistcustomer',[SistemController::class, 'exportlistcustomer']);
	Route::get('/admin/newcustomer',[SistemController::class, 'newcustomer']);
	Route::post('/admin/newcustomer',[ActionController::class, 'newcustomer']);
	Route::get('/admin/editcustomer',[SistemController::class, 'editcustomer']);
	Route::post('/admin/editcustomer',[ActionController::class, 'editcustomer']);
	Route::get('/admin/deletecustomer',[ActionController::class, 'deletecustomer']);
	Route::get('/admin/upstatuscustomer',[ActionController::class, 'upstatuscustomer']);

	// Data Karyawan
	Route::get('/admin/listkaryawan',[SistemController::class, 'listkaryawan']);
	Route::get('/admin/exportlistkaryawan',[SistemController::class, 'exportlistkaryawan']);
	Route::get('/admin/newkaryawan',[SistemController::class, 'newkaryawan']);
	Route::post('/admin/newkaryawan',[ActionController::class, 'newkaryawan']);
	Route::get('/admin/editkaryawan',[SistemController::class, 'editkaryawan']);
	Route::post('/admin/editkaryawan',[ActionController::class, 'editkaryawan']);
	Route::get('/admin/deletekaryawan',[ActionController::class, 'deletekaryawan']);
	Route::get('/admin/upstatuskaryawan',[ActionController::class, 'upstatuskaryawan']);

	// Data Gudang
	Route::get('/admin/listgudang',[SistemController::class, 'listgudang']);
	Route::get('/admin/exportlistgudang',[SistemController::class, 'exportlistgudang']);
	Route::get('/admin/newgudang',[SistemController::class, 'newgudang']);
	Route::post('/admin/newgudang',[ActionController::class, 'newgudang']);
	Route::get('/admin/editgudang',[SistemController::class, 'editgudang']);
	Route::post('/admin/editgudang',[ActionController::class, 'editgudang']);
	Route::get('/admin/deletegudang',[ActionController::class, 'deletegudang']);
	Route::get('/admin/upstatusgudang',[ActionController::class, 'upstatusgudang']);

	// Data Cabang
	Route::get('/admin/listcabang',[SistemController::class, 'listcabang']);
	Route::get('/admin/exportlistcabang',[SistemController::class, 'exportlistcabang']);
	Route::get('/admin/newcabang',[SistemController::class, 'newcabang']);
	Route::post('/admin/newcabang',[ActionController::class, 'newcabang']);
	Route::get('/admin/editcabang',[SistemController::class, 'editcabang']);
	Route::post('/admin/editcabang',[ActionController::class, 'editcabang']);
	Route::get('/admin/deletecabang',[ActionController::class, 'deletecabang']);

	// Pengguna
	Route::get('/admin/listusers',[SistemController::class, 'listusers']);
	Route::get('/admin/exportlistusers',[SistemController::class, 'exportlistusers']);
	Route::get('/admin/newusers',[SistemController::class, 'newusers']);
	Route::post('/admin/newusers',[ActionController::class, 'newusers']);
	Route::get('/admin/editusers',[SistemController::class, 'editusers']);
	Route::post('/admin/editusers',[ActionController::class, 'editusers']);
	Route::get('/admin/deleteusers',[ActionController::class, 'deleteusers']);
	
	// Level Pengguna
	Route::get('/admin/levelusers',[SistemController::class, 'levelusers']);
	Route::get('/admin/newlevelusers',[SistemController::class, 'newlevelusers']);
	Route::post('/admin/actionlevel',[ActionController::class, 'actionlevel']);
	Route::get('/admin/editlevel',[SistemController::class, 'editlevel']);
	Route::get('/admin/deletelevel',[ActionController::class, 'deletelevel']);
	
	// Admin
	Route::get('/admin/editaccount',[SistemController::class, 'editaccount']);
	Route::post('/admin/editaccount',[ActionController::class, 'editaccount']);
	Route::post('/admin/editpassaccount',[ActionController::class, 'editpassaccount']);

	// Aktivitas Pengguna
	Route::get('/admin/activityusers',[SistemController::class, 'activityusers']);
	Route::get('/admin/exportactivityusers',[SistemController::class, 'exportactivityusers']);

	// Setting
	Route::get('/admin/settingmenu',[SistemController::class, 'settingmenu']);
	Route::get('/admin/delmenu',[ActionController::class, 'delmenu']);
	Route::post('/admin/actionsettingmenu',[ActionController::class, 'actionsettingmenu']);
	Route::get('/admin/manualbook',[SistemController::class, 'manualbook']);
	Route::post('/admin/uploadmanualbook',[ActionController::class, 'uploadmanualbook']);
	Route::get('/admin/viewmanualbook',[SistemController::class, 'viewmanualbook']);
	Route::get('/admin/downloadmanualbook',[ApiController::class, 'downloadmanualbook']);
	Route::get('/admin/listcompany',[SistemController::class, 'listcompany']);
	Route::get('/admin/newcompany',[SistemController::class, 'newcompany']);
	Route::post('/admin/newcompany',[ActionController::class, 'newcompany']);
	Route::get('/admin/editcompany',[SistemController::class, 'editcompany']);
	Route::post('/admin/editcompany',[ActionController::class, 'editcompany']);
	Route::get('/admin/deletecompany',[ActionController::class, 'deletecompany']);
	Route::get('/admin/sinkron',[ActionController::class, 'sinkron']);
// });