<?php

namespace App\Http\Controllers;

use Illuminate\Http\{Request, Response, UploadedFile};
use Illuminate\Support\Facades\{Http, Route, Session, Hash, Artisan, Cookie};
use Illuminate\Support\Carbon;
use App\Http\Controllers\{Controller, ApiController, ApiControllerPembelian, ApiControllerPenjualan, ApiControllerGudang, ApiControllerFinance};
use Tymon\JWTAuth\Facades\JWTAuth;
use Maatwebsite\Excel\Facades\Excel;
use Jenssegers\Date\Date;
use App\Exports\{DataPengguna, AktivitasPengguna, Kategori, Satuan, Gudang, Cabang, Karyawan, Customer, Supplier, Merk, Barang, Jasa, DataPembelian, DataPembelianAll, DataPenerimaan, DataPenerimaanAll, DataPenjualan, DataPenjualanAll, DataPengiriman, DataPengirimanAll, DataPenerimaanKas, DataPengeluaranKas, DataPurchasePayment, DataSalesPayment, HistoryStockBarang, PersediaanBarang, DataPenyesuaianStockBarang, DataPPN, DataHutang, DataKartuHutang, DataHutangAll, DataTagihan, DataKartuPiutang, DataTagihanAll, DataKas, DataRekapitulasi, DataMutasiKirim, DataMutasiKirimAll, DataMutasiTerima, DataMutasiTerimaAll};
use App\Services\CabangService;

class SistemController extends Controller
{
    public function formlogin(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');

        if(empty(session('key_token_perdana'))){            
            return view('admin.AdminOne.login',['url' => 'login']);
        }else{
            return redirect('/admin/dash');
        }
    }

    public function login(Request $request)
    {   
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = [];
        $email = $request->email;
        $password = $request->password;

        $request->validate([
            'email' => 'required|min:1|max:200',
            'password' => 'required|min:1|max:200',
        ]);

        $response = app('App\Http\Controllers\ApiController')->login($request);
        $results = is_array($response) ? $response : $response->getData(true); 
		
		$status = $results['status_message'];
		$note = $results['note'];
        $getdata = $results['results'];

        if($status == 'success'){
        	$detailadmin = $getdata[0]['detailadmin'][0];
            
            if($detailadmin['level']=='LV7622003'){
                Session::put('key_token_perdana_cash',$results['key_token']);
                Session::put('admin_login_perdana_cash',$detailadmin['id']);    
                $this->backup_database();    
                return redirect('/cash/dash');
            }else{
                Session::put('key_token_perdana',$results['key_token']);
                Session::put('admin_login_perdana',$detailadmin['id']);
                $this->backup_database();
                return redirect('/admin/dash');
            }
        }else{
        	return redirect('/admin/administration')->with($status,$note);
        }
    }

    public function logout(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
    	$key_token = session('key_token_perdana');
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $response = app('App\Http\Controllers\ApiController')->logout($request);  
        $results = is_array($response) ? $response : $response->getData(true); 

        Session::forget('key_token_perdana');
        Session::forget('admin_login_perdana');

        Cookie::queue(Cookie::forget('key_token_perdana'));

        $this->backup_database();

        return redirect('/admin/login');
    }

    public function dash(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            $response = app('App\Http\Controllers\ApiController')->getdash($request);
            $results = is_array($response) ? $response : $response->getData(true); 
            
            return view('admin.AdminOne.home',['url_api' => $url_api,'app' => 'dash','url_active' => 'dash','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'listdata' => $results['results'] ]);
        }
    }

    public function settingmenu(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            if($res_user['level'] == 'LV5677001'){
                return view('admin.AdminOne.pengaturan.settingmenu',['url_api' => $url_api,'app' => 'setting','url_active' => 'settingmenu','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
            }else{
                return redirect('/admin/dash')->with('error','Tidak ada akses');
            }
        }
    }

    public function listcompany(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            $request['vd'] = $vd;           

            $response= app('App\Services\ApiServicePengaturan')->listcompany($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.pengaturan.listcompany',['url_api' => $url_api,'app' => 'setting','url_active' => 'listcompany','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function newcompany(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company'];

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }     

            return view('admin.AdminOne.pengaturan.newcompany',['url_api' => $url_api,'app' => 'setting','url_active' => 'listcompany','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editcompany(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            $request['id'] = $request['d'];

            $response= app('App\Services\ApiServicePengaturan')->viewcompany($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.pengaturan.editcompany',['url_api' => $url_api,'app' => 'setting','url_active' => 'listcompany','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    public function manualbook(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            if($res_user['level'] == 'LV5677001'){   
                $response= app('App\Services\ApiServicePengaturan')->viewManualBook($request);
                $results = is_array($response) ? $response : $response->getData(true); 
    
                if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

                return view('admin.AdminOne.pengaturan.manualbook',['url_api' => $url_api,'app' => 'setting','url_active' => 'manualbook','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
            }else{
                return redirect('/admin/dash')->with('error','Tidak ada akses');
            }
        }
    }

    public function viewmanualbook(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            $request['tipe_page'] = 'full';
            $request['file_manualbook'] = $request['d'];
            $request['title_manualbook'] = 'Manual Book';
            
            return view('admin.AdminOne.manualbook.tempmanualbook',['app' => 'tempmanualbook','url_active' => 'tempmanualbook','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Cashier
    public function loginCashier(Request $request)
    {   
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $url_app =  env('APP_URL');
        $object = [];
        $email = $request->email;
        $password = $request->password;

        $request->validate([
            'email' => 'required|min:1|max:200',
            'password' => 'required|min:1|max:200',
        ]);

        $response = app('App\Services\ApiServiceCashier')->loginCashier($request);  
        $results = is_array($response) ? $response : $response->getData(true); 
		
		$status = $results['status_message'];
		$note = $results['note'];
        $results = $results['results'];

        if($status == 'success'){            
        	$detailadmin = $results[0]['detailadmin'][0];
            $level = $results[0]['detailadmin'][0]['level'];
            if($level == 'LV7622003'){
                Session::put('key_token_perdana_cash',$results['key_token']);
                Session::put('admin_login_perdana_cash',$detailadmin['id']);    
                $this->backup_database();    
                return redirect('/cash/dash');
            }else{
                return redirect('/cash/login')->with($status,$note);
            }
        }else{
        	return redirect('/cash/login')->with($status,$note);
        }
    }

    public function logoutCashier(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $response = app('App\Services\ApiServiceCashier')->logoutCashier($request);  
        $results = is_array($response) ? $response : $response->getData(true); 

        Session::forget('key_token_perdana_cash');
        Session::forget('admin_login_perdana_cash');

        Cookie::queue(Cookie::forget('key_token_perdana_cash'));

        $this->backup_database();

        // return redirect('/cash/login');
        return redirect('/admin/administration');
    }

    public function dashCashier(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user_cashier($request);       
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/cash/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting_cashier($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
           
            $list_gudang = $this->get_op_gudang_cashier($request);
            $list_mekanik = $this->get_op_mekanik_cashier($request);

            return view('admin.AdminOne.cashier.home',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_gudang' => $list_gudang['results'],'list_mekanik' => $list_mekanik['results']]);
        }
    }

    public function cashlistbarangtransaksi(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            $response = app('App\Services\ApiServiceCashier')->listbarangtransaksi($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }
    
    public function cashlistopcustomer(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            $response = app('App\Services\ApiServiceCashier')->listopcustomer($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function cashviewpenjualan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{    
            $get_user = $this->get_user_cashier($request);          
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServiceCashier')->viewpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $list_gudang = $this->get_op_gudang_cashier($request);  
            $list_mekanik = $this->get_op_mekanik_cashier($request);        

            return view('admin.AdminOne.cashier.viewpenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_gudang' => $list_gudang['results'],'list_mekanik' => $list_mekanik['results']]);
        }
    }

    public function cashlistprodpenjualan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{    
            $get_user = $this->get_user_cashier($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServiceCashier')->viewpenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            // return $results;     

            return view('admin.AdminOne.cashier.listprodpenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function cashsummarypenjualan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{   
            $get_user = $this->get_user_cashier($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServiceCashier')->viewpenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            // return $results;    

            return view('admin.AdminOne.cashier.summarypenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function cashhistorypenjualanbarang(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user_cashier($request);          
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServiceCashier')->historypenjualanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                // return $results;

                return view('admin.AdminOne.cashier.historypenjualanitem',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServiceCashier')->historypenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                // return $results;

                return view('admin.AdminOne.cashier.historypenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function cashpersediaanbarang(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user_cashier($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServiceCashier')->persediaanbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_gudang = $this->get_op_gudang_cashier($request);

            return view('admin.AdminOne.cashier.persediaanbarang',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend,'list_gudang' => $list_gudang['results']]);
        }
    }

    public function cashprintsalesorder(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user_cashier($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {$level_user[$menu['data_menu']] = $menu['access_rights'];}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServiceCashier')->viewpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'cashsalesorder';
            $request['title_print'] = 'Sales Order';
            
            return view('admin.AdminOne.cashier.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function casheditaccount(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana_cash');
    	$key_token = session('key_token_perdana_cash');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana_cash'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user_cashier($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            $request['data_company'] = $get_user['results']['data_company']; 

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);

            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses_cashier($request);
            $level_user = array();

            for ($x = 0; $x <= count($res_level_user) - 1; $x++) {$access_rights[''.$res_level_user[$x]['data_menu'].''] = $res_level_user[$x]['access_rights'];}
            array_push($level_user, $access_rights);

            return view('admin.AdminOne.cashier.account',['url_api' => $url_api,'app' => 'editaccount','url_active' => 'editaccount','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }
    // End Cashier

    // Isi Combobox
    public function getsatuanpecahan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServiceSatuan')->listsatuanpecahan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_satuan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopsatuan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_kategori(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopkategori($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_merk(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopmerk($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_supplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopsupplier($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_gudang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopgudang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function get_cabang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Http\Controllers\ApiController')->listopcabang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    // Auto Complete
    public function listopsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePembelian')->listopsupplier($request);
            $results = is_array($response) ? $response : $response->getData(true);  
            
            return $results;
        }
    }

    public function listbarangtransaksi(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $response = app('App\Services\ApiServicePembelian')->listbarangtransaksi($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }
    
    public function listopcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePenjualan')->listopcustomer($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    //Admin
    public function editaccount(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            return view('admin.AdminOne.masterpengguna.editdata.account',['url_api' => $url_api,'app' => 'editaccount','url_active' => 'editaccount','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Barang
    public function listbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceBarang')->listbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.databarang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceBarang')->listbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Barang-".$datetime_now.".xls" ;
            return Excel::download(new Barang($request), $nama_file);
        }
    }

    public function newbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company'];

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listbarang'] == 'No' OR $level_user['newbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}          
            
            $response = app('App\Services\ApiServiceBarang')->getgenerate($request);
            $results = is_array($response) ? $response : $response->getData(true); 
            
            $list_satuan = $this->get_op_satuan($request);
            $list_kategori = $this->get_op_kategori($request);
            $list_merk = $this->get_op_merk($request);
            $list_supplier = $this->get_op_supplier($request);

            // return $list_pro;

            return view('admin.AdminOne.masterdata.newdata.databarang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_satuan' => $list_satuan['results'],'list_kategori' => $list_kategori['results'],'list_merk' => $list_merk['results'],'list_supplier' => $list_supplier['results']]);
        }
    }

    public function editbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceBarang')->viewbarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $list_satuan = $this->get_op_satuan($request);
            $list_kategori = $this->get_op_kategori($request);
            $list_merk = $this->get_op_merk($request);
            $list_supplier = $this->get_op_supplier($request);

            return view('admin.AdminOne.masterdata.editdata.databarang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_satuan' => $list_satuan['results'],'list_kategori' => $list_kategori['results'],'list_merk' => $list_merk['results'],'list_supplier' => $list_supplier['results']]);
        }
    }

    // Jasa
    public function listjasa(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listjasa'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceJasa')->listjasa($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datajasa',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listjasa','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistjasa(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceJasa')->listjasa($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Jasa-".$datetime_now.".xls" ;
            return Excel::download(new Jasa($request), $nama_file);
        }
    }

    public function newjasa(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listjasa'] == 'No' OR $level_user['newjasa'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}          
            
            $response = app('App\Services\ApiServiceJasa')->getgenerate($request);
            $results = is_array($response) ? $response : $response->getData(true); 
            
            $list_satuan = $this->get_op_satuan($request);

            // return $list_pro;

            return view('admin.AdminOne.masterdata.newdata.datajasa',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listjasa','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_satuan' => $list_satuan['results']]);
        }
    }

    public function editjasa(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listjasa'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceJasa')->viewjasa($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $list_satuan = $this->get_op_satuan($request);

            return view('admin.AdminOne.masterdata.editdata.datajasa',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listjasa','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_satuan' => $list_satuan['results']]);
        }
    }

    // Satuan
    public function listsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listsatuan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceSatuan')->listsatuan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datasatuan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsatuan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceSatuan')->listsatuan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Satuan-".$datetime_now.".xls" ;
            return Excel::download(new Satuan($request), $nama_file);
        }
    }

    public function newsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listsatuan'] == 'No' OR $level_user['newsatuan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $list_pro;
            $list_satuan = $this->get_op_satuan($request);

            return view('admin.AdminOne.masterdata.newdata.datasatuan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsatuan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_satuan' => $list_satuan['results']]);
        }
    }

    public function editsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listsatuan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceSatuan')->viewsatuan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datasatuan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsatuan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Kategori
    public function listkategori(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listkategori'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceKategori')->listkategori($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datakategori',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkategori','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistkategori(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceKategori')->listkategori($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Kategori-".$datetime_now.".xls" ;
            return Excel::download(new Kategori($request), $nama_file);
        }
    }

    public function newkategori(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listkategori'] == 'No' OR $level_user['newkategori'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $list_pro;

            return view('admin.AdminOne.masterdata.newdata.datakategori',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkategori','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editkategori(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listkategori'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceKategori')->viewkategori($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datakategori',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkategori','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Merk
    public function listmerk(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listmerk'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceMerk')->listmerk($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datamerk',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listmerk','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistmerk(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceMerk')->listmerk($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Merk-".$datetime_now.".xls" ;
            return Excel::download(new Merk($request), $nama_file);
        }
    }

    public function newmerk(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listmerk'] == 'No' OR $level_user['newmerk'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $list_pro;

            return view('admin.AdminOne.masterdata.newdata.datamerk',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listmerk','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editmerk(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listmerk'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceMerk')->viewmerk($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datamerk',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listmerk','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }
    
    // Data Supplier
    public function listsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listsupplier'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceSupplier')->listsupplier($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datasupplier',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsupplier','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceSupplier')->listsupplier($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Supplier-".$datetime_now.".xls" ;
            return Excel::download(new Supplier($request), $nama_file);
        }
    }

    public function newsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listsupplier'] == 'No' OR $level_user['newsupplier'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $list_pro;

            return view('admin.AdminOne.masterdata.newdata.datasupplier',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsupplier','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listsupplier'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceSupplier')->viewsupplier($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datasupplier',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listsupplier','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Data Customer
    public function listcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listcustomer'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceCustomer')->listcustomer($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.masterdata.listdata.datacustomer',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcustomer','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceCustomer')->listcustomer($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Customer-".$datetime_now.".xls" ;
            return Excel::download(new Customer($request), $nama_file);
        }
    }

    public function newcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listcustomer'] == 'No' OR $level_user['newcustomer'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.newdata.datacustomer',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcustomer','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listcustomer'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceCustomer')->viewcustomer($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datacustomer',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcustomer','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Data Karyawan
    public function listkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listkaryawan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceKaryawan')->listkaryawan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.listdata.datakaryawan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkaryawan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $request['vd'] = $vd;
            $request['type'] = 'export';
            
            $response = app('App\Services\ApiServiceKaryawan')->listkaryawan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Karyawan-".$datetime_now.".xls" ;
            return Excel::download(new Karyawan($request), $nama_file);
        }
    }

    public function newkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listkaryawan'] == 'No' OR $level_user['newkaryawan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.newdata.datakaryawan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkaryawan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listkaryawan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceKaryawan')->viewkaryawan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datakaryawan',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listkaryawan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Data Gudang
    public function listgudang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listgudang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceGudang')->listgudang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.listdata.datagudang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listgudang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['listdata'],'listdata' => $results['results']]);
        }
    }

    public function exportlistgudang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Gudang-".$datetime_now.".xls" ;
            return Excel::download(new Gudang($request), $nama_file);
        }
    }

    public function newgudang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listgudang'] == 'No' OR $level_user['newgudang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.newdata.datagudang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listgudang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editgudang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company'];

            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listgudang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceGudang')->viewgudang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datagudang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listgudang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Data Cabang
    public function listcabang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listcabang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;

            $response= app('App\Services\ApiServiceCabang')->listcabang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.listdata.datacabang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcabang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
        }
    }

    public function exportlistcabang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');        
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Cabang-".$datetime_now.".xls" ;
            return Excel::download(new Cabang($request), $nama_file);
        }
    }

    public function newcabang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listcabang'] == 'No' OR $level_user['newcabang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterdata.newdata.datacabang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcabang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editcabang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['masterdata'] == 'No' OR $level_user['listcabang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['id'] = $request['d'];

            $response= app('App\Services\ApiServiceCabang')->viewcabang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterdata.editdata.datacabang',['url_api' => $url_api,'app' => 'masterdata','url_active' => 'listcabang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Pengguna
    public function listusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }
            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}

            if($level_user['users'] == 'No' OR $level_user['listusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceMasterpengguna')->listusers($request);            
            $results = is_array($response) ? $response : $response->getData(true);           

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterpengguna.listdata.datapengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'listusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
        }
    }

    public function exportlistusers(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Data-Pengguna-".$datetime_now.".xls" ;
            return Excel::download(new DataPengguna($request), $nama_file);
        }
    }

    public function newusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listusers'] == 'No' OR $level_user['newusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_level = $this->get_op_level($request);

            return view('admin.AdminOne.masterpengguna.newdata.datapengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'listusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_level' => $list_level['results']]);
        }
    }

    public function editusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['listusers'] == 'No' OR $level_user['newusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_level = $this->get_op_level($request);

            $request['id'] = $request['d'];
            
            $response = app('App\Services\ApiServiceMasterpengguna')->viewusers($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterpengguna.editdata.datapengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'listusers' ,'request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_level' => $list_level['results'],'get_data' => $results['results'],'results' => $results['results'][0],'detailadmin' => $results['results'][0]['detailadmin'][0]]);
        }
    }

    // Level Pengguna
    public function levelusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['users'] == 'No' OR $level_user['levelusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            $response = app('App\Services\ApiServiceMasterpengguna')->listlevelusers($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterpengguna.listdata.levelpengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'levelusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']]);
        }
    }

    public function newlevelusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['levelusers'] == 'No' OR $level_user['newlevelusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.masterpengguna.newdata.levelpengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'levelusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function editlevel(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['users'] == 'No' OR $level_user['levelusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_level = $this->get_op_level($request);

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServiceMasterpengguna')->viewlevel($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            return view('admin.AdminOne.masterpengguna.editdata.levelpengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'levelusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results]);
        }
    }

    // Aktifitas Pengguna
    public function activityusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['users'] == 'No' OR $level_user['activityusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['type'] = 'list';
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;   
            
            $response = app('App\Services\ApiServiceMasterpengguna')->activityusers($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

             return view('admin.AdminOne.masterpengguna.listdata.aktivitaspengguna',['url_api' => $url_api,'app' => 'users','url_active' => 'activityusers','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results'],'searchdate' => '&searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportactivityusers(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-30 days")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['type'] = 'export';
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServiceMasterpengguna')->activityusers($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $datetime_now = date('Y-m-d-His');
            $nama_file = "Aktifitas-Pengguna-".$datetime_now.".xls" ;
            return Excel::download(new AktivitasPengguna($request), $nama_file);
        }
    }

    // Data Pembelian Barang
    public function menupembelianbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
                       
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelian'] == 'No' OR $level_user['menupembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpembelianbarang'] == 'No'){
                if($level_user['historypembelianbarang'] == 'Yes'){
                    return redirect('/admin/historypembelianbarang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_data');
            $request['code_data'] = $code_data;

            $response = app('App\Services\ApiServicePembelian')->getcodepembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            $code_data = $results['results'];

            Cookie::queue(Cookie()->forever('code_data', $code_data));
           
            $list_gudang = $this->get_op_gudang($request);
            $list_cabang = $this->get_op_cabang($request);

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewpembelian?d='.$code_data);
            }else{
                return view('admin.AdminOne.menupembelian/newdata.pembelian',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_gudang' => $list_gudang['results'],'list_cabang' => $list_cabang['results'],'code_data' => $code_data,'status_data' => $results['status_transaksi']]);
            }
        }
    }

    public function viewpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            

            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelianbarang'] == 'No' OR $level_user['historypembelianbarang'] == 'No'){return redirect('dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];

            $response= app('App\Services\ApiServicePembelian')->viewpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request['d'];
            $request['code_data'] = $code_data;

            $get_code = app('App\Services\ApiServicePembelian')->getcodepembelian($request);  
            $get_code = is_array($get_code) ? $get_code : $get_code->getData(true);

            $list_gudang = $this->get_gudang($request);
            $list_cabang = $this->get_cabang($request);

            return view('admin.AdminOne.menupembelian.editdata.pembelian',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_gudang' => $list_gudang,'code_data' => $code_data]);
        }
    }

    public function listprodpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelian'] == 'No' OR $level_user['menupembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request->get('code_data');
            $results = app('App\Services\ApiServicePembelian')->viewpembelian($request);
            $results = is_array($results) ? $results : $results->getData(true);

            return view('admin.AdminOne.menupembelian/inputdata.listprodpembelian',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function summarypembelian(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelian'] == 'No' OR $level_user['menupembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServicePembelian')->viewpembelian($request);
            $results = is_array($response) ? $response : $response->getData(true); 
            
            return view('admin.AdminOne.menupembelian/inputdata.summarypembelian',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historypembelianbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelianbarang'] == 'No' OR $level_user['historypembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePembelian')->historypembelianitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menupembelian/listdata.historypembelianitem',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServicePembelian')->historypembelian($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menupembelian/listdata.historypembelian',['url_api' => $url_api,'app' => 'menupembelian','url_active' => 'menupembelianbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function exportpembelianbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePembelian')->historypembelianitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Pembelian Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPembelianAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServicePembelian')->historypembelian($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Pembelian ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPembelian($request), $nama_file);
            }
        }
    }

    public function printpurchaseorder(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembelianbarang'] == 'No' OR $level_user['historypembelianbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'purchaseorder';
            $request['title_print'] = 'Purchase Order';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Penjualan Barang
    public function menupenjualanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenjualan'] == 'No' OR $level_user['menupenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpenjualanbarang'] == 'No'){
                if($level_user['historypenjualanbarang'] == 'Yes'){
                    return redirect('/admin/historypenjualanbarang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_data');
            $request['code_data'] = $code_data;

            $response = app('App\Services\ApiServicePenjualan')->getcodepenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            $code_data = $results['results'];

            Cookie::queue(Cookie()->forever('code_data', $code_data));
           
            $list_gudang = $this->get_op_gudang($request);            
            $list_mekanik = $this->get_op_mekanik($request);

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewpenjualan?d='.$code_data);
            }else{
                return view('admin.AdminOne.menupenjualan/newdata.penjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_gudang' => $list_gudang['results'],'code_data' => $code_data,'status_data' => $results['status_transaksi'],'list_mekanik' => $list_mekanik['results']]);
            }
        }
    }

    public function viewpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            

            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);

            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }


            if($level_user['menupenjualan'] == 'No' OR $level_user['historypenjualanbarang'] == 'No'){return redirect('dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];

            $response= app('App\Services\ApiServicePenjualan')->viewpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request['d'];
            $request['code_data'] = $code_data;

            $get_code = app('App\Services\ApiServicePenjualan')->getcodepenjualan($request);  
            $get_code = is_array($get_code) ? $get_code : $get_code->getData(true);

            $list_gudang = $this->get_gudang($request);
            $list_cabang = $this->get_cabang($request);
            $list_mekanik = $this->get_op_mekanik($request); 

            return view('admin.AdminOne.menupenjualan.editdata.penjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_gudang' => $list_gudang,'code_data' => $code_data,'list_mekanik' => $list_mekanik['results']]);
        }
    }

    public function listprodpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenjualan'] == 'No' OR $level_user['menupenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return view('admin.AdminOne.menupenjualan/inputdata.listprodpenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function summarypenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenjualan'] == 'No' OR $level_user['menupenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return view('admin.AdminOne.menupenjualan/inputdata.summarypenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historypenjualanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenjualanbarang'] == 'No' OR $level_user['historypenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePenjualan')->historypenjualanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menupenjualan/listdata.historypenjualanitem',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServicePenjualan')->historypenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menupenjualan/listdata.historypenjualan',['url_api' => $url_api,'app' => 'menupenjualan','url_active' => 'menupenjualanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function exportpenjualanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePenjualan')->historypenjualanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Penjualan Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPenjualanAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServicePenjualan')->historypenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Penjualan ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPenjualan($request), $nama_file);
            }
        }
    }

    public function printsalesorder(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenjualanbarang'] == 'No' OR $level_user['historypenjualanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'salesorder';
            $request['title_print'] = 'Sales Order';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Penerimaan Barang
    public function penerimaanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;
            

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['menupenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpenerimaanbarang'] == 'No'){
                if($level_user['historypenerimaanbarang'] == 'Yes'){
                    return redirect('/admin/historypenerimaanbarang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_rdo_perdana');
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServicePenerimaanbarang')->getcodepenerimaan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_rdo_perdana', $code_data));
            
            $list_perusahaan = $this->get_cabang($request);
            $list_gudang = $this->get_gudang($request);

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewpenerimaan?d='.$code_data);
            }else{
                return view('admin.AdminOne.menugudang.newdata.penerimaan',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_perusahaan' => $list_perusahaan,'list_gudang' => $list_gudang,'code_data' => $code_data,'status_transaksi' => $results['status_transaksi']]);
            }
        }
    }

    public function listoppembelian(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePenerimaanbarang')->listoppembelian($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function detailoppembelian(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePenerimaanbarang')->detailoppembelian($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function listprodpenerimaan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['menupenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            if($request->status_data == 'Yes'){
                $request['code_data'] = $request->get('code_data_rdo');
                if($request->get('tipe_data') != ''){
                    $request['tipe_data'] = $request->get('tipe_data');
                }else{
                    $request['tipe_data'] = 'group';
                }

                $response = app('App\Services\ApiServicePenerimaanbarang')->viewpenerimaan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                return view('admin.AdminOne.menugudang.inputdata.listinputprodpenerimaan',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }else{
                $request['code_data'] = $request->get('code_data');
                $response = app('App\Services\ApiServicePenerimaanbarang')->listprodpembelian($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                return view('admin.AdminOne.menugudang.inputdata.listprodpembelian',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }
        }
    }

    public function viewpenerimaan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenerimaanbarang'] == 'No' OR $level_user['historypenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_penerimaan'] = $request['d'];
            $response = app('App\Services\ApiServicePenerimaanbarang')->viewpenerimaan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menugudang.editdata.penerimaan',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function historypenerimaan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenerimaanbarang'] == 'No' OR $level_user['historypenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePenerimaanbarang')->historypenerimaanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historypenerimaanitem',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServicePenerimaanbarang')->historypenerimaan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historypenerimaan',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupenerimaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function exportpenerimaanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePenerimaanbarang')->historypenerimaanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Penerimaan Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPenerimaanAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServicePenerimaanbarang')->historypenerimaan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Penerimaan ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPenerimaan($request), $nama_file);
            }
        }
    }

    public function printpenerimaan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['historypenerimaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_penerimaan'] = $request['d'];
            
            $response = app('App\Services\ApiServicePenerimaanbarang')->viewpenerimaan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'tandaterima';
            $request['title_print'] = 'Tanda Terima';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Pengiriman Barang
    public function pengirimanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;
            

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['menupengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpengirimanbarang'] == 'No'){
                if($level_user['historypengirimanbarang'] == 'Yes'){
                    return redirect('/admin/historypengirimanbarang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_rso_perdana');
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServicePengirimanbarang')->getcodepengiriman($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_rso_perdana', $code_data));
            
            $list_perusahaan = $this->get_cabang($request);
            $list_gudang = $this->get_gudang($request);

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewpengiriman?d='.$code_data);
            }else{
                return view('admin.AdminOne.menugudang.newdata.pengiriman',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_perusahaan' => $list_perusahaan,'list_gudang' => $list_gudang,'code_data' => $code_data,'status_transaksi' => $results['status_transaksi']]);
            }
        }
    }

    public function listoppenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePengirimanbarang')->listoppenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function detailoppenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePengirimanbarang')->detailoppenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function listprodpengiriman(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['menupengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            if($request->status_data == 'Yes'){
                $request['code_data'] = $request->get('code_data_rso');
                if($request->get('tipe_data') != ''){
                    $request['tipe_data'] = $request->get('tipe_data');
                }else{
                    $request['tipe_data'] = 'group';
                }
                $response = app('App\Services\ApiServicePengirimanbarang')->viewpengiriman($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                return view('admin.AdminOne.menugudang.inputdata.listinputprodpengiriman',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }else{
                $request['code_data'] = $request->get('code_data');
                $response = app('App\Services\ApiServicePengirimanbarang')->listprodpenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                return view('admin.AdminOne.menugudang.inputdata.listprodpenjualan',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }
        }
    }

    public function viewpengiriman(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupengirimanbarang'] == 'No' OR $level_user['historypengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_pengiriman'] = $request['d'];
            $response = app('App\Services\ApiServicePengirimanbarang')->viewpengiriman($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menugudang.editdata.pengiriman',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function historypengiriman(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupengirimanbarang'] == 'No' OR $level_user['historypengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePengirimanbarang')->historypengirimanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historypengirimanitem',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServicePengirimanbarang')->historypengiriman($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historypengiriman',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'menupengirimanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function exportpengirimanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePengirimanbarang')->historypengirimanitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Pengiriman Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPengirimanAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServicePengirimanbarang')->historypengiriman($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Pengiriman ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataPengiriman($request), $nama_file);
            }
        }
    }

    public function printpengiriman(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['historypengirimanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_pengiriman'] = $request['d'];
            
            $response = app('App\Services\ApiServicePengirimanbarang')->viewpengiriman($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'tandakirim';
            $request['title_print'] = 'Tanda Kirim';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Penyesuaian Stock Barang
    public function penyesuaianstockbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['penyesuaianstockbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            // Dev History Penyesuaian Stock
            if($level_user['inputpenyesuaianstockbarang'] == 'No'){
                if($level_user['historypenyesuaianstockbarang'] == 'Yes'){
                    return redirect('/admin/historypenyesuaianstockbarang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_stock_opname_perdana');
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServicePenyesuaianstock')->getcodepenyesuaian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_stock_opname_perdana', $code_data));
            
            $list_gudang = $this->get_gudang($request);

            return view('admin.AdminOne.menugudang.newdata.penyesuaianstockbarang',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'penyesuaianstockbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'code_data' => $code_data ,'list_gudang' => $list_gudang]);
        }
    }

    public function listbarangstockopname(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePenyesuaianstock')->listbarangstockopname($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function liststockbarangSO(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePenyesuaianstock')->liststockbarangSO($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function historypenyesuaianstockbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['penyesuaianstockbarang'] == 'No' OR $level_user['historypenyesuaianstockbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;

            $response = app('App\Services\ApiServicePenyesuaianstock')->historypenyesuaianstockbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menugudang.listdata.historypenyesuaianstockbarang',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'penyesuaianstockbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
                
        }
    }

    public function exporthistorypenyesuaianstockbarang (Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpenyesuaianstockbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "Penyesuaian Stock Barang ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new DataPenyesuaianStockBarang($request), $nama_file);
        }
    }

    // Data History Stock Barang
    public function historystockbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['historystockbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;

            $nama_gudang = $request->nama_gudang;
            
            $response = app('App\Services\ApiServiceHistorystock')->historystockbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_gudang = $this->get_op_gudang($request);

            return view('admin.AdminOne.menugudang.listdata.historystockbarang',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'historystockbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend,'list_gudang' => $list_gudang['results'],'nama_gudang' => $nama_gudang]);
        }
    }

    public function exporthistorystockbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exporthistorystockbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;

            $response = app('App\Services\ApiServiceHistorystock')->historystockbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "History Stock Barang ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new HistoryStockBarang($request), $nama_file);
        }
    }

    // Data Persediaan Barang
    public function persediaanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['persediaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServicePersediaanstock')->persediaanbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $list_gudang = $this->get_op_gudang($request);

            return view('admin.AdminOne.menugudang.listdata.persediaanbarang',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'persediaanbarang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend,'list_gudang' => $list_gudang['results']]);
        }
    }

    public function exportpersediaanbarang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpersediaanbarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;

            $response = app('App\Services\ApiServicePersediaanstock')->persediaanbarang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $dateyear = Carbon::now()->format('Y-m-d');
            $nama_file = "Persediaan Barang ".$dateyear.".xls" ;
            return Excel::download(new PersediaanBarang($request), $nama_file);
        }
    }

    // Mutasi Kirim
    public function mutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;
            

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['mutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputmutasikirim'] == 'No'){
                if($level_user['historymutasikirim'] == 'Yes'){
                    return redirect('/admin/historymutasikirim');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_mtsk_perdana');
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServiceMutasikirim')->getcodemutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_mtsk_perdana', $code_data));

            $list_gudang = $this->get_op_gudang($request);

            // return $count_request;

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewmutasikirim?d='.$code_data);
            }else{
                return view('admin.AdminOne.menugudang.newdata.mutasikirim',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasikirim','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_gudang' => $list_gudang['results'],'code_data' => $code_data,'status_transaksi' => $results['status_transaksi']]);
            }
        }
    }

    public function viewmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasikirim'] == 'No' OR $level_user['historymutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServiceMutasikirim')->viewmutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request['d'];
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServiceMutasikirim')->getcodemutasikirim($request);  
            $get_code = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            // $count_request = $get_code['count_request'];

            $list_gudang = $this->get_gudang($request);

            return view('admin.AdminOne.menugudang.editdata.mutasikirim',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasikirim','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'list_gudang' => $list_gudang,'code_data' => $get_code]);
        }
    }

    public function listprodmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['mutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServiceMutasikirim')->viewmutasikirim($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return view('admin.AdminOne.menugudang.inputdata.listprodmutasikirim',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasikirim','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }

    public function historymutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasikirim'] == 'No' OR $level_user['historymutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServiceMutasikirim')->historymutasikirimitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historymutasikirimitem',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasikirim','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServiceMutasikirim')->historymutasikirim($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historymutasikirim',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasikirim','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function exportmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportmutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServiceMutasikirim')->historymutasikirimitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Mutasi Kirim Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataMutasiKirimAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServiceMutasikirim')->historymutasikirim($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Mutasi Kirim ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataMutasiKirim($request), $nama_file);
            }
        }
    }

    public function printmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasikirim'] == 'No' OR $level_user['historymutasikirim'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServiceMutasikirim')->viewmutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'mutasikirim';
            $request['title_print'] = 'Mutasi Kirim';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Mutasi Terima
    public function mutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;
            

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['mutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputmutasiterima'] == 'No'){
                if($level_user['historymutasiterima'] == 'Yes'){
                    return redirect('/admin/historymutasiterima');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_mtst_perdana');
            $request['code_data'] = $code_data;
            $response = app('App\Services\ApiServiceMutasiterima')->getcodemutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_mtst_perdana', $code_data));

            $list_gudang = $this->get_op_gudang($request);

            if($results['status_transaksi'] == 'Yes'){
                return redirect('/admin/viewmutasiterima?d='.$code_data);
            }else{
                return view('admin.AdminOne.menugudang.newdata.mutasiterima',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_gudang' => $list_gudang['results'],'code_data' => $code_data,'status_transaksi' => $results['status_transaksi']]);
            }
        }
    }

    public function listopmutasi(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServiceMutasiterima')->listopmutasi($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function detailopmutasi(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServiceMutasiterima')->detailopmutasi($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function listprodmutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menugudang'] == 'No' OR $level_user['mutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            if($request->status_data == 'Yes'){
                $request['code_data'] = $request->get('code_data');
            $response = app('App\Services\ApiServiceMutasiterima')->viewmutasiterima($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                return view('admin.AdminOne.menugudang.inputdata.listinputprodmutasiterima',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }else{            
                $request['code_data'] = $request->get('code_data');
                $response = app('App\Services\ApiServiceMutasiterima')->listprodmutasiterima($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                return view('admin.AdminOne.menugudang.inputdata.listprodmutasiterima',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
            }
        }
    }

    public function viewmutasiterima(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasiterima'] == 'No' OR $level_user['historymutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_mutasi_terima'] = $request['d'];
            $response = app('App\Services\ApiServiceMutasiterima')->viewmutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menugudang.editdata.mutasiterima',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function historymutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasiterima'] == 'No' OR $level_user['historymutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            if($request->tp == 'item'){
            $response = app('App\Services\ApiServiceMutasiterima')->historymutasiterimaitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historymutasiterimaitem',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }else{
                $response = app('App\Services\ApiServiceMutasiterima')->historymutasiterima($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menugudang.listdata.historymutasiterima',['url_api' => $url_api,'app' => 'menugudang','url_active' => 'mutasiterima','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
            }
        }
    }

    public function printmutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['mutasiterima'] == 'No' OR $level_user['historymutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['nomor_mutasi_terima'] = $request['d'];
            
            $response = app('App\Services\ApiServiceMutasiterima')->viewmutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'mutasiterima';
            $request['title_print'] = 'Mutasi Terima';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    public function exportmutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportmutasiterima'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServiceMutasiterima')->historymutasiterimaitem($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Mutasi Terima Perbarang ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataMutasiTerimaAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServiceMutasiterima')->historymutasiterima($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
                $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
                if($request->searchdate != ''){
                    $getsearchdate = explode ("sd",$request->searchdate);
                    $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                    $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
                }
                $nama_file = "Mutasi Terima ".$date_export_start." sd ".$date_export_end.".xls" ;
                return Excel::download(new DataMutasiTerima($request), $nama_file);
            }
        }
    }

    // Data Penerimaan Kas
    public function menupenerimaankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menupenerimaankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpenerimaankas'] == 'No'){
                if($level_user['historypenerimaankas'] == 'Yes'){
                    return redirect('/admin/historypenerimaankas');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_pay_kas_perdana');
            $request['code_data'] = $code_data;

            $request['tipe_data'] = 'Kas';
            
            $response = app('App\Services\ApiServicePenerimaankas')->getcodepay($request);  
            
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_pay_kas_perdana', $code_data));
            
            $list_akun = $this->get_op_akun($request);

            if($results['status_data'] == 'Yes'){
                return redirect('/admin/inputpenerimaankas?d='.$code_data);
            }else{
                return view('admin.AdminOne.menufinance.newdata.penerimaankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupenerimaankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_akun' => $list_akun,'code_data' => $code_data,'status_data' => $results['status_data']]);
            }
        }
    }

    public function historypenerimaankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenerimaankas'] == 'No' OR $level_user['historypenerimaankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServicePenerimaankas')->historypenerimaankas($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.historypenerimaankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupenerimaankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportpenerimaankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpenerimaankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';

            $response = app('App\Services\ApiServicePenerimaankas')->historypenerimaankas($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "Penerimaan Kas ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new DataPenerimaanKas($request), $nama_file);
        }
    }

    public function viewpenerimaankas(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupenerimaankas'] == 'No' OR $level_user['historypenerimaankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServicePenerimaankas')->viewpenerimaankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menufinance.editdata.penerimaankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupenerimaankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function printpenerimaankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['historypenerimaankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServicePenerimaankas')->viewpenerimaankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'penerimaankas';
            $request['title_print'] = 'Penerimaan Kas';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Pengeluaran Kas
    public function menupengeluarankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menupengeluarankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpengeluarankas'] == 'No'){
                if($level_user['historypengeluarankas'] == 'Yes'){
                    return redirect('/admin/historypengeluarankas');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_paykas_perdana');
            $request['code_data'] = $code_data;

            $request['tipe_data'] = 'Kas';
            
            $response = app('App\Services\ApiServicePengeluarankas')->getcodepaykas($request);  
            
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_paykas_perdana', $code_data));
            
            $list_akun = $this->get_op_akun($request);

            // return $code_data;

            if($results['status_data'] == 'Yes'){
                return redirect('/admin/inputpengeluarankas?d='.$code_data);
            }else{
                return view('admin.AdminOne.menufinance.newdata.pengeluarankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupengeluarankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_akun' => $list_akun['results'],'code_data' => $code_data,'status_data' => $results['status_data']]);
            }
        }
    }

    public function historypengeluarankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupengeluarankas'] == 'No' OR $level_user['historypengeluarankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServicePengeluarankas')->historypengeluarankas($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.historypengeluarankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupengeluarankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportpengeluarankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpengeluarankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';

            $response = app('App\Services\ApiServicePengeluarankas')->historypengeluarankas($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "Pengeluaran Kas ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new DataPengeluaranKas($request), $nama_file);
        }
    }

    public function viewpengeluarankas(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupengeluarankas'] == 'No' OR $level_user['historypengeluarankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServicePengeluarankas')->viewpengeluarankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menufinance.editdata.pengeluarankas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupengeluarankas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function printpengeluarankas(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['historypengeluarankas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServicePengeluarankas')->viewpengeluarankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'pengeluarankas';
            $request['title_print'] = 'Pengeluaran Kas';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Data Pembayaran Pembelian - Purchase Payment - Hutang
    public function menupembayaranhutang (Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menupembayaranhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpembayaranhutang'] == 'No'){
                if($level_user['historypembayaranhutang'] == 'Yes'){
                    return redirect('/admin/historypembayaranhutang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_purchasepayment_perdana');
            $request['code_data'] = $code_data;

            $request['tipe_data'] = 'Kas';
            
            $response = app('App\Services\ApiServicePembayaranpembelian')->getcodepurchasepayment($request);  
            
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_purchasepayment_perdana', $code_data));
            
            $list_akun = $this->get_op_akun($request);

            if($results['status_data'] == 'Yes'){
                return redirect('/admin/inputpembayaranhutang?d='.$code_data);
            }else{
                return view('admin.AdminOne.menufinance.newdata.purchasepayment',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_akun' => $list_akun['results'],'code_data' => $code_data,'status_data' => $results['status_data']]);
            }
        }
    }

    public function listpurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePembayaranpembelian')->listpurchasepayment($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function detailpurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePembayaranpembelian')->detailpurchasepayment($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function historypembayaranhutang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembayaranhutang'] == 'No' OR $level_user['historypembayaranhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServicePembayaranpembelian')->historypembayaranhutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.historypembayaranhutang',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportpurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpembayaranhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';

            $response = app('App\Services\ApiServicePembayaranpembelian')->historypembayaranhutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "Purchase Payment ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new DataPurchasePayment($request), $nama_file);
        }
    }

    public function viewpurchasepayment(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembayaranhutang'] == 'No' OR $level_user['historypembayaranhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_transaksi'] = $request['d'];
            $response = app('App\Services\ApiServicePembayaranpembelian')->viewpurchasepayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menufinance.editdata.purchasepayment',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function printpurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['historypembayaranhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_transaksi'] = $request['d'];
            $response = app('App\Services\ApiServicePembayaranpembelian')->viewpurchasepayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'purchasepayment';
            $request['title_print'] = 'Purchase Payment';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }


    // Data Pembayaran Penjualan - Sales Payment - Piutang
    public function menupembayaranpiutang (Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menupembayaranpiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            if($level_user['inputpembayaranpiutang'] == 'No'){
                if($level_user['historypembayaranpiutang'] == 'Yes'){
                    return redirect('/admin/historypembayaranpiutang');
                }else{
                    return redirect('/admin/dash')->with('error','Tidak ada akses');
                }
            }
            
            $code_data = $request->cookie('code_salespayment_perdana');
            $request['code_data'] = $code_data;

            $request['tipe_data'] = 'Kas';
            
            $response = app('App\Services\ApiServicePembayaranpenjualan')->getcodesalespayment($request); 
             
            $results = is_array($response) ? $response : $response->getData(true); 
            $code_data = $results['results'];
            Cookie::queue(Cookie()->forever('code_salespayment_perdana', $code_data));
            
            $list_akun = $this->get_op_akun($request);

            if($results['status_data'] == 'Yes'){
                return redirect('/admin/inputpembayaranpiutang?d='.$code_data);
            }else{
                return view('admin.AdminOne.menufinance.newdata.salespayment',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranpiutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'list_akun' => $list_akun['results'],'code_data' => $code_data,'status_data' => $results['status_data']]);
            }
        }
    }

    public function listsalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePembayaranpenjualan')->listsalespayment($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function detailsalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{             
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            $response = app('App\Services\ApiServicePembayaranpenjualan')->detailsalespayment($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    public function historypembayaranpiutang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembayaranpiutang'] == 'No' OR $level_user['historypembayaranpiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServicePembayaranpenjualan')->historypembayaranpiutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.historypembayaranpiutang',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranpiutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportsalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportpembayaranpiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            $request['type'] = 'export';

            $response = app('App\Services\ApiServicePembayaranpenjualan')->historypembayaranpiutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $date_export_start = Carbon::now()->modify("-1 month")->format('d M Y');
            $date_export_end = Carbon::now()->modify("0 days")->format('d M Y');
            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $date_export_start = Carbon::parse($getsearchdate[0])->format('d M Y');
                $date_export_end = Carbon::parse($getsearchdate[1])->format('d M Y');
            }
            $nama_file = "Sales Payment ".$date_export_start." sd ".$date_export_end.".xls" ;
            return Excel::download(new DataSalesPayment($request), $nama_file);
        }
    }

    public function viewsalespayment(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
        $admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menupembayaranpiutang'] == 'No' OR $level_user['historypembayaranpiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServicePembayaranpenjualan')->viewsalespayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}
            
            $code_data = $request->get('code_data');

            return view('admin.AdminOne.menufinance.editdata.salespayment',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menupembayaranpiutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'results' => $results,'code_data' => $code_data]);
        }
    }

    public function printsalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $get_user = $this->get_user($request);           
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['historypembayaranpiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['code_data'] = $request['d'];
            
            $response = app('App\Services\ApiServicePembayaranpenjualan')->viewsalespayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Data tidak ditemukan'){return redirect('/admin/dash')->with('error','Data tidak ditemukan');}

            $request['tipe_page'] = 'full';
            $request['file_print'] = 'salespayment';
            $request['title_print'] = 'Sales Payment';
            
            return view('admin.AdminOne.print.tempprint',['url_api' => $url_api,'app' => 'tempprint','url_active' => 'tempprint','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results']]);
        }
    }

    // Hostory kas
    public function historykas(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
    	$key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        if($request->has('vd')){
            if($request->vd == ''){
                $vd = '999999999999999';
            }else{
                $vd = $request->vd;
            }
        }else{
            $vd = '999999999999999';
        }

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['historykas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            Carbon::setLocale('en');

            $datefilterstart = Carbon::now()->modify("-1 month")->format('Y-m-d') . ' 00:00:00';
            $datefilterend = Carbon::now()->modify("0 days")->format('Y-m-d') . ' 23:59:59';

            if($request->searchdate != ''){
                $getsearchdate = explode ("sd",$request->searchdate);
                $datefilterstart = Carbon::parse($getsearchdate[0])->format('Y-m-d') . ' 00:00:00';
                $datefilterend = Carbon::parse($getsearchdate[1])->format('Y-m-d') . ' 23:59:59';
            }

            $request['vd'] = $vd;
            $request['searchdate'] = $datefilterstart.'sd'.$datefilterend;
            
            $response = app('App\Services\ApiServiceHistorykas')->historykas($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.historykas',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'historykas','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results'],'searchdate' => 'searchdate='.$request->searchdate,'datefilterstart' => $datefilterstart,'datefilterend' => $datefilterend]);
        }
    }

    public function exportkas(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportkas'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $nama_file = "Data Kas.xls" ;
            return Excel::download(new DataKas($request), $nama_file);
        }
    }

    // Daftar Hutang
    public function menuhutang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menuhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            if($request->tp == 'item'){            
                $response = app('App\Services\ApiServiceHutang')->listhutang($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
    
                return view('admin.AdminOne.menufinance.listdata.listhutang',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menuhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
            }else{
                $response = app('App\Services\ApiServiceHutang')->listhutangpersupplier($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menufinance.listdata.listhutangpersupplier',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menuhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
            }
        }
    }

    public function exportlisthutang(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportlisthutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServiceHutang')->listhutang($request);
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
                $nama_file = "Data Hutang Pertransaksi.xls" ;
                return Excel::download(new DataHutangAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServiceHutang')->listhutangpersupplier($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
                $nama_file = "Data Hutang.xls" ;
                return Excel::download(new DataHutang($request), $nama_file);
            }
        }
    }
    
    public function kartuhutang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menuhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            $request['id_supplier'] = $request['d'];

            $response = app('App\Services\ApiServiceHutang')->kartuhutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.kartuhutang',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menuhutang','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
        }
    }

    public function exportkartuhutang(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportkartuhutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['type'] = 'export';
            $request['id_supplier'] = $request['d'];

            $response = app('App\Services\ApiServiceHutang')->kartuhutang($request);            
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
        
            $nama_file = "Data Kartu Hutang.xls" ;
            return Excel::download(new DataKartuHutang($request), $nama_file);
        }
    }

    // Tagihan
    public function menutagihan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menutagihan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            
            if($request->tp == 'item'){            
                $response = app('App\Services\ApiServicePiutang')->listtagihan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
    
                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
    
                return view('admin.AdminOne.menufinance.listdata.listtagihan',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menutagihan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
            }else{
                $response = app('App\Services\ApiServicePiutang')->listtagihanpercustomer($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

                return view('admin.AdminOne.menufinance.listdata.listtagihanpercustomer',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menutagihan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
            }
        }
    }

    public function exportlisttagihan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportlisttagihan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');} 
            
            $request['type'] = 'export';
            
            if($request->tp == 'item'){
                $response = app('App\Services\ApiServicePiutang')->listtagihan($request);
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
                $nama_file = "Data Tagihan Pertransaksi.xls" ;
                return Excel::download(new DataTagihanAll($request), $nama_file);
            }else{
                $response = app('App\Services\ApiServicePiutang')->listtagihanpercustomer($request);  
                $results = is_array($response) ? $response : $response->getData(true); 

                if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
                $nama_file = "Data Tagihan.xls" ;
                return Excel::download(new DataTagihan($request), $nama_file);
            }
        }
    }
    
    public function kartupiutang(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20; 
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menutagihan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request['vd'] = $vd;
            $request['id_customer'] = $request['d'];

            $response = app('App\Services\ApiServicePiutang')->kartupiutang($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.kartupiutang',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menutagihan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'count_vd' => $vd,'keysearch' => $request->keysearch,'results' => $results['results']['list'],'listdata' => $results['results']]);
        }
    }

    public function exportkartupiutang(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportkartupiutang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['type'] = 'export';
            $request['id_customer'] = $request['d'];

            $response = app('App\Services\ApiServicePiutang')->kartupiutang($request);            
            $results = is_array($response) ? $response : $response->getData(true); 

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
        
            $nama_file = "Data Kartu Piutang.xls" ;
            return Excel::download(new DataKartuPiutang($request), $nama_file);
        }
    }

    // PPN
    public function menuppn(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['menuppn'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $nama_perusahaan = $request->nama_perusahaan;
            $tahun =  $request->tahun;
            if($tahun == Null or $tahun ==''){
                $tahun = Carbon::now()->format('Y');
            }
            
            $response = app('App\Services\ApiServicePpn')->listppn($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            
            $list_cabang = $this->get_op_cabang($request);

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            // return $results;

            return view('admin.AdminOne.menufinance.listdata.listppn',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menuppn','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'listdata' => $results['results'],'list_cabang' => $list_cabang['results'],'nama_perusahaan' => $nama_perusahaan,'tahun' => $tahun]);
        }
    }

    public function exportppn(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportppn'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $nama_file = "Data PPN.xls" ;
            return Excel::download(new DataPPN($request), $nama_file);
        }
    }

    // Rekap Pembelian Penjualan
    public function rekappembelianpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['menufinance'] == 'No' OR $level_user['rekappembelianpenjualan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $nama_perusahaan = $request->nama_perusahaan;
            $tahun =  $request->tahun;
            if($tahun == Null or $tahun ==''){
                $tahun = Carbon::now()->format('Y');
            }
            
            $response = app('App\Services\ApiServiceRekappembelianpenjualan')->rekappembelianpenjualan($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            
            $list_cabang = $this->get_op_cabang($request);

            if($results['note'] == 'Tidak ada akses'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            return view('admin.AdminOne.menufinance.listdata.listrekapitulasi',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'rekappembelianpenjualan','request' => $request,'res_user' => $res_user,'level_user' => $level_user,'list_akses' => $list_akses['results'],'listdata' => $results['results'],'list_cabang' => $list_cabang['results'],'nama_perusahaan' => $nama_perusahaan,'tahun' => $tahun]);
        }
    }

    public function exportrekappembelianpenjualan(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{  
            $get_user = $this->get_user($request);            
            if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
            $request['data_company'] = $get_user['results']['data_company']; 
            
            $res_user = $get_user['results'][0]['detailadmin'][0];
            $res_level_user = $get_user['results'][0]['leveladmin'][0];
            $nama_admin = substr($res_user['full_name'],0,15);
            if(strlen($nama_admin) > 15){$nama_admin = $nama_admin."...";}
            $request['nama_admin'] = $nama_admin;

            $get_setting = $this->get_setting($request);
            $manual_book =  $get_setting['results']['data_setting']['manual_book'];
            $request['manual_book'] = $manual_book;

            $list_akses = $this->get_akses($request);
            $level_user = [];
            foreach ($res_level_user as $menu) {
                $level_user[$menu['data_menu']] = $menu['access_rights'];
            }

            if($level_user['exportppn'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $nama_file = "Data Rekapitulasi.xls" ;
            return Excel::download(new DataRekapitulasi($request), $nama_file);
        }
    }

}