<?php

namespace App\Http\Controllers;

use App\Http\Controllers\{Controller, ApiController};
use Illuminate\Http\{Request, Response, UploadedFile};
use Illuminate\Support\Facades\{Http, Route,Session, Hash, Artisan, Cookie};
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class ActionController extends Controller
{ 
    //Admin
    public function editaccount(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];
    
            $request->validate([
                'full_name' => 'required|string|max:200',
                'phone_number' => 'required|string|max:30',
                'email' => 'required|string|email|max:200',
            ]);
            
            $request['id'] = $res_user['id'];

            $response = app('App\Services\ApiServiceMasterpengguna')->editadmin($request); 
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/editaccount')->with($status,$note);
            }else{
                if(isset($results['note']['email'])){
                    return redirect('/admin/editaccount')->with('error',$results['note']['email'][0]);
                }else{
                    return redirect('/admin/editaccount')->with($status,$note);
                }
            }
        }
    }

    public function editpassaccount(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];
    
            $request->validate([
                'old_password' => 'required|string|max:30',
                'new_password' => 'required|string|max:30',
            ]);
    
            if($request->old_password == $request->new_password){
                return redirect('/admin/editaccount')->with('error','Kata sandi baru harus berbeda dengan kata sandi lama.');
            }

            // if(!Hash::check($request->old_password,$res_user['password'])){
            //     return redirect('/admin/editaccount')->with('error','Kata sandi lama salah.');
            // }
            
            $request['id'] = $res_user['id'];

            $response = app('App\Services\ApiServiceMasterpengguna')->editpassadmin($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/logout')->with('success','Data berhasil disimpan, silakan masuk kembali');
            }else{
                return redirect('/admin/editaccount')->with($status,$note);
            }
        }
    }

    //Setting Menu
    public function delmenu(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            if($res_user['level'] == 'LV5677001'){
            
                $request['id'] = $request['d'];

                $response = app('App\Services\ApiServicePengaturan')->delmenu($request);
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results ['status_message'];
                $note = $results ['note'];

                return redirect('/admin/settingmenu')->with($status,$note);
            }else{
                return redirect('/admin/dash')->with('error','Tidak ada akses');
            }
        }
    }

    public function actionsettingmenu(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            if($res_user['level'] == 'LV5677001'){
                $request->validate([
                    'no_urut' => 'required|string',
                    'nama_menu' => 'required|string|max:200',
                    'nama_akses' => 'required|min:1|max:200',
                ]);

                $response = app('App\Services\ApiServicePengaturan')->actionsettingmenu($request);
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results ['status_message'];
                $note = $results ['note'];
    
                if($status == 'success'){
                    return redirect('/admin/settingmenu')->with($status,$note);
                }else{
                    if(isset($results['note']['nama_akses'])){
                        return redirect('/admin/settingmenu')->with($status,$results['note']['nama_akses'][0]);
                    }else{
                        return redirect('/admin/settingmenu')->with($status,$note);
                    }
                }
            }else{
                return redirect('/admin/dash')->with('error','Tidak ada akses');
            }
        }
    }

    public function uploadmanualbook(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
    	$key_token = session('key_token_perdana');
        $load_app = $request->load;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            $get_user = $this->get_user($request);
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengaturan')->uploadmanualbook($request);
            $results = is_array($response) ? $response : $response->getData(true);
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/manualbook?d=')->with($status,$note);
        }
    }

    public function newcompany(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'kode' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'jenis' => 'required|string|max:200',
                'alamat' => 'required|string|max:200',
                'email' => 'required|email|max:200',
            ]);

            $response = app('App\Services\ApiServicePengaturan')->newcompany($request);
            $results = is_array($response) ? $response : $response->getData(true);
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listcompany')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newcompany')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newcompany')->with($status,$note);
                }
            }
        }
    }

    public function editcompany(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengaturan')->editcompany($request);
            $results = is_array($response) ? $response : $response->getData(true);
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/editcompany?d='.$request->id_data)->with($status,$note);
        }
    }

    public function deletecompany(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServicePengaturan')->deletecompany($request);
            $results = is_array($response) ? $response : $response->getData(true);
            $status = $results['status_message'];
            $note = $results['note'];
            
            return redirect('/admin/listcompany')->with($status,$note);
        }
    }  

    // Barang  
    public function newbarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'satuan' => 'required|string|max:200',
                'kategori' => 'required|string|max:200',
                'merk' => 'required|string|max:200',
                'supplier' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
                'harga_khusus' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceBarang')->newbarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listbarang')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newbarang')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newbarang')->with($status,$note);
                }
            }
        }
    }

    public function editbarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'satuan' => 'required|string|max:200',
                'kategori' => 'required|string|max:200',
                'merk' => 'required|string|max:200',
                'supplier' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
                'harga_khusus' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceBarang')->editbarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listbarang')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editbarang?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editbarang?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletebarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listbarang'] == 'No' OR $level_user['deletebarang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceBarang')->deletebarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listbarang')->with($status,$note);
        }
    }   

    // Jasa  
    public function newjasa(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'satuan' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceJasa')->newjasa($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listjasa')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newjasa')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newjasa')->with($status,$note);
                }
            }
        }
    }

    public function editjasa(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'kode_barang' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'satuan' => 'required|string|max:200',
                'harga_beli' => 'required|string|max:200',
                'harga_jual' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceJasa')->editjasa($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listjasa')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editjasa?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editjasa?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletejasa(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listjasa'] == 'No' OR $level_user['deletejasa'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceJasa')->deletejasa($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listjasa')->with($status,$note);
        }
    } 
    
    // Satuan  
    public function newsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'isi_satuan' => 'required|string|max:200',
                'status_pecahan' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceSatuan')->newsatuan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listsatuan')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newsatuan')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newsatuan')->with($status,$note);
                }
            }
        }
    }

    public function editsatuan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceSatuan')->editsatuan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listsatuan')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editsatuan?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editsatuan?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletesatuan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listsatuan'] == 'No' OR $level_user['deletesatuan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceSatuan')->deletesatuan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listsatuan')->with($status,$note);
        }
    }
    
    // Kategori 
    public function newkategori(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceKategori')->newkategori($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listkategori')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newkategori')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newkategori')->with($status,$note);
                }
            }
        }
    }

    public function editkategori(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceKategori')->editkategori($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listkategori')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editkategori?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editkategori?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletekategori(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listkategori'] == 'No' OR $level_user['deletekategori'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceKategori')->deletekategori($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            
            return redirect('/admin/listkategori')->with($status,$note);
        }
    }  
    
    // Merk  
    public function newmerk(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceMerk')->newmerk($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listmerk')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newmerk')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newmerk')->with($status,$note);
                }
            }
        }
    }

    public function editmerk(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceMerk')->editmerk($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listmerk')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editmerk?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editmerk?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletemerk(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listmerk'] == 'No' OR $level_user['deletemerk'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceMerk')->deletemerk($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listmerk')->with($status,$note);
        }
    } 

    // Supplier
    public function newsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            $response = app('App\Services\ApiServiceSupplier')->newsupplier($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listsupplier')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newsupplier')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newsupplier')->with($status,$note);
                }
            }
        }
    }

    public function editsupplier(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceSupplier')->editsupplier($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listsupplier')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editsupplier?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editsupplier?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function upstatussupplier(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listsupplier'] == 'No' OR $level_user['editsupplier'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['id'];

            $response = app('App\Services\ApiServiceSupplier')->upstatussupplier($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    } 

    public function deletesupplier(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listsupplier'] == 'No' OR $level_user['deletesupplier'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceSupplier')->deletesupplier($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
                
            return redirect('/admin/listsupplier')->with($status,$note);
        }
    }  

    // Customer
    public function newcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            $response = app('App\Services\ApiServiceCustomer')->newcustomer($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listcustomer')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newcustomer')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newcustomer')->with($status,$note);
                }
            }
        }
    }

    public function editcustomer(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceCustomer')->editcustomer($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listcustomer')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editcustomer?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editcustomer?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function upstatuscustomer(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listcustomer'] == 'No' OR $level_user['editcustomer'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['id'];

            $response = app('App\Services\ApiServiceCustomer')->upstatuscustomer($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    } 

    public function deletecustomer(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listcustomer'] == 'No' OR $level_user['deletecustomer'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceCustomer')->deletecustomer($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listcustomer')->with($status,$note);
        }
    } 

    // Karyawan  
    public function newkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama_karyawan' => 'required|string|max:200',
                'tempat_lahir' => 'required|string|max:200',
                'tanggal_lahir' => 'required|string|max:200',
                'jenis_kelamin' => 'required|string|max:200',
                'jabatan' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            $response = app('App\Services\ApiServiceKaryawan')->newkaryawan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listkaryawan')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newkaryawan')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newkaryawan')->with($status,$note);
                }
            }
        }
    }

    public function editkaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama_karyawan' => 'required|string|max:200',
                'tempat_lahir' => 'required|string|max:200',
                'tanggal_lahir' => 'required|string|max:200',
                'jenis_kelamin' => 'required|string|max:200',
                'jabatan' => 'required|string|max:200',
                'no_hp' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceKaryawan')->editkaryawan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listkaryawan')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editkaryawan?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editkaryawan?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function upstatuskaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listkaryawan'] == 'No' OR $level_user['editkaryawan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['id'];

            $response = app('App\Services\ApiServiceKaryawan')->upstatuskaryawan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    } 

    public function deletekaryawan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listkaryawan'] == 'No' OR $level_user['deletekaryawan'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceKaryawan')->deletekaryawan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listkaryawan')->with($status,$note);
        }
    } 

    // Gudang   
    public function newgudang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
                'jenis_gudang' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServiceGudang')->newgudang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listgudang')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/newgudang')->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/newgudang')->with($status,$note);
                }
            }
        }
    }

    public function editgudang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama' => 'required|string|max:200',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
                'jenis_gudang' => 'required|string|max:200',
            ]);
            
            $request['id'] = $request['id_data'];
            $response = app('App\Services\ApiServiceGudang')->editgudang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listgudang')->with($status,$note);
            }else{
                if(isset($results['note']['nama'])){
                    return redirect('/admin/editgudang?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editgudang?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function upstatusgudang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listgudang'] == 'No' OR $level_user['editgudang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['id'];

            $response = app('App\Services\ApiServiceGudang')->upstatusgudang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    } 

    public function deletegudang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listgudang'] == 'No' OR $level_user['deletegudang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceGudang')->deletegudang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listgudang')->with($status,$note);
        }
    } 

    // Cabang   
    public function newcabang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama_cabang' => 'required|string|max:200',
                'nama_pic' => 'required|string|max:200',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);

            $response = app('App\Services\ApiServiceCabang')->newcabang($request);
            $results = is_array($response) ? $response : $response->getData(true);
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listcabang')->with($status,$note);
            }else{
                if(isset($results['note']['nama_cabang'])){
                    return redirect('/admin/newcabang')->with($status,$results['note']['nama_cabang'][0]);
                }else{
                    return redirect('/admin/newcabang')->with($status,$note);
                }
            }
        }
    } 

    public function editcabang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'nama_cabang' => 'required|string|max:200',
                'nama_pic' => 'required|string|max:200',
                'no_hp_pic' => 'required|string|max:200',
                'alamat' => 'required|string',
            ]);
            
            $request['id'] = $request['id_data'];

            $response = app('App\Services\ApiServiceCabang')->editcabang($request);
            $results = is_array($response) ? $response : $response->getData(true);            
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listcabang')->with($status,$note);
            }else{
                if(isset($results['note']['nama_cabang'])){
                    return redirect('/admin/editcabang?d='.$request->id_data)->with($status,$results['note']['nama'][0]);
                }else{
                    return redirect('/admin/editcabang?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    public function deletecabang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listcabang'] == 'No' OR $level_user['deletecabang'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceCabang')->deletecabang($request);
            $results = is_array($response) ? $response : $response->getData(true);
            
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/listcabang')->with($status,$note);
        }
    } 

    // Pengguna    
    public function newusers(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'full_name' => 'required|string|max:200',
                'phone_number' => 'required|string|max:200',
                'email' => 'required|string|email|max:200',
                'password' => 'required|min:1|max:200',
                'level' => 'required|string|max:30',
            ]);

            $response = app('App\Services\ApiServiceMasterpengguna')->newusers($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listusers')->with($status,$note);
            }else{
                if(isset($results['note']['email'])){
                    return redirect('/admin/newusers')->with($status,$results['note']['email'][0]);
                }else{
                    return redirect('/admin/newusers')->with($status,$note);
                }
            }
        }
    }

    public function deleteusers(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listusers'] == 'No' OR $level_user['deleteusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['id'] = $request['d'];

            $response = app('App\Services\ApiServiceMasterpengguna')->deleteusers($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listusers')->with($status,$note);
            }else{
                return redirect('/admin/editusers?d='.$request->d)->with($status,$note);
            }
        }
    }

    public function editusers(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['listusers'] == 'No' OR $level_user['editusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}

            $request->validate([
                'full_name' => 'required|string|max:200',
                'phone_number' => 'required|string|max:200',
                'email' => 'required|string|email|max:200',
                'level' => 'required|string|max:30',
                'status_data' => 'required|string|max:30',
            ]);
            
            $request['id'] = $request['id_data'];

            $response = app('App\Services\ApiServiceMasterpengguna')->editusers($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/listusers')->with($status,$note);
            }else{
                if(isset($results['note']['email'])){
                    return redirect('/admin/editusers?d='.$request->id_data)->with($status,$results['note']['email'][0]);
                }else{
                    return redirect('/admin/editusers?d='.$request->id_data)->with($status,$note);
                }
            }
        }
    }

    // Level Pengguna 
    public function actionlevel(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'level_name' => 'required|string',
            ]);

            $response = app('App\Services\ApiServiceMasterpengguna')->actionlevel($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/levelusers')->with($status,$note);
            }else{
                if($request->get('code_data') != ''){
                    if(isset($results['note']['level_name'])){
                        return redirect('/admin/editlevel?d='.$request->code_data)->with($status,$results['note']['level_name'][0]);
                    }else{
                        return redirect('/admin/editlevel?d='.$request->code_data)->with($status,$note);
                    }
                }else{
                    if(isset($results['note']['level_name'])){
                        return redirect('/admin/newlevelusers')->with($status,$results['note']['level_name'][0]);
                    }else{
                        return redirect('/admin/newlevelusers')->with($status,$note);
                    }
                }
            }
        }
    }

    public function deletelevel(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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

            if($level_user['levelusers'] == 'No' OR $level_user['deletelevelusers'] == 'No'){return redirect('/admin/dash')->with('error','Tidak ada akses');}
            
            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServiceMasterpengguna')->deletelevel($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/levelusers')->with($status,$note);
            }else{
                return redirect('/admin/editlevel?d='.$request->d)->with($status,$note);
            }
        }
    }

    // Data Pembeliaan Barang   
    public function getcodepembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePembelian')->getcodepembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 

    public function saveprodpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePembelian')->saveprodpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);

            if($status == 'success'){
                return redirect('/admin/pembelianbarang')->with($status,$note);
            }else{
                return redirect('/admin/pembelianbarang')->with($status,$note);
            }
        }
    }

    public function deleteprodpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->deleteprodpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function deletepembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePembelian')->deletepembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_po'));
                return redirect('/admin/menupembelianbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpembelian?d='.$request['d'])->with($status,$note);
            }
        }
    }

    public function upppnpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->upppnpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function listsatuanharga(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->listsatuanharga($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function uphargapembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->uphargapembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upqtypembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->upqtypembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelian2(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelian2($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelian3(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelian3($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelianharga(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelianharga($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelianharga2(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelianharga2($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpembelianharga3(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->updiscpembelianharga3($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upsummarypembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->upsummarypembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upsummarypembeliancash(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePembelian')->upsummarypembeliancash($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }
    
    public function updatepembelian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            // $request->validate([
            //     'supplier'=>'required|string|max:200'
            // ]);

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServicePembelian')->viewpembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $get_data = $results['results']['detail'];
            
            $request['code_data'] = $request['d'];
            $request['supplier'] = $request['supplier'];
            $request['keterangan'] = $request['ket'];

            $response = app('App\Services\ApiServicePembelian')->updatepembelian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_po'));
                if($get_data['status_transaksi'] == 'Proses'){
                    return redirect('/admin/menupembelianbarang')->with($status,$note);
                }else{
                    return redirect('/admin/viewpembelian?d='.$request['d'])->with($status,$note);
                }
            }else{
                return redirect('/admin/viewpembelian?d='.$request['d'])->with($status,$note);
            }     
        }
    }

    // Data Penerimaan Barang
    public function getcodepenerimaan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenerimaanbarang')->getcodepenerimaan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 
    
    public function savepenerimaan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenerimaanbarang')->savepenerimaan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);
        }
    }

    public function deleterdobarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePenerimaanbarang')->deleterdobarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_rdo'));
                return redirect('/admin/menupenerimaanbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpenerimaan?d='.$request['d'])->with($status,$note);
            }
        }
    }

    public function updaterdobarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenerimaanbarang')->updaterdobarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            // return response()->json(['status_message' => $status,'note' => $note]);

            if($status == 'success'){
                return redirect('/admin/menupenerimaanbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpenerimaan?d='.$request['d'])->with($status,$note);
            }
        }
    }

    // Data Penjualan Barang   
    public function getcodepenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenjualan')->getcodepenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    }

    public function saveprodpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenjualan')->saveprodpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);
            // return response()->json(['status_message' => $status,'note' => $note]);

            if($status == 'success'){
                return redirect('/admin/penjualanbarang')->with($status,$note);
            }else{
                return redirect('/admin/penjualanbarang')->with($status,$note);
            }
        }
    }

    public function listsatuanhargapenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->listsatuanhargapenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function uphargapenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->uphargapenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upqtypenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->upqtypenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->updiscpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function updiscpenjualan2(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->updiscpenjualan2($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upsummarypenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->upsummarypenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }
    
    public function updatepenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $get_data = $results['results']['detail'];
            
            $request['code_data'] = $request['d'];
            $request['supplier'] = $request['supplier'];
            $request['keterangan'] = $request['ket'];

            $response = app('App\Services\ApiServicePenjualan')->updatepenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_so'));
                if($get_data['status_transaksi'] == 'Proses'){
                    return redirect('/admin/menupenjualanbarang')->with($status,$note);
                }else{
                    return redirect('/admin/viewpenjualan?d='.$request['d'])->with($status,$note);
                }
            }else{
                return redirect('/admin/viewpenjualan?d='.$request['d'])->with($status,$note);
            }     
        }
    }

    public function deleteprodpenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServicePenjualan')->deleteprodpenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function deletepenjualan(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePenjualan')->deletepenjualan($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_so'));
                return redirect('/admin/menupenjualanbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpenjualan?d='.$request['d'])->with($status,$note);
            }
        }
    }

    // Cashier
        public function cashsaveprodpenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);          
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];  

                $response = app('App\Services\ApiServiceCashier')->saveprodpenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];
                $code = $results['code'];

                return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);

                return redirect('/cash/dash')->with($status,$note);
            }
        }

        public function cashlistsatuanhargapenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);       
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->listsatuanhargapenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashuphargapenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->uphargapenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashupqtypenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->upqtypenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashupdiscpenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->updiscpenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashupdiscpenjualan2(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->updiscpenjualan2($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashupsummarypenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->upsummarypenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashdeleteprodpenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);         
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['id'] = $request['id'];
                $request['code_data'] = $request['code_data'];

                $response = app('App\Services\ApiServiceCashier')->deleteprodpenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                return response()->json(['status_message' => $status,'note' => $note]);
            }
        }

        public function cashdeletepenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);        
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['code_data'] = $request['d'];

                $response = app('App\Services\ApiServiceCashier')->deletepenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                if($status == 'success'){
                    Cookie::queue(Cookie::forget('code_so_cash'));
                    return redirect('/cash/dash')->with($status,$note);
                }else{
                    return redirect('/cash/viewpenjualan?d='.$request['d'])->with($status,$note);
                }
            }
        }
        
        public function cashupdatepenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);         
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['code_data'] = $request['d'];
                $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request);   
                $results = is_array($response) ? $response : $response->getData(true); 
                $get_data = $results['results']['detail'];
                
                $request['code_data'] = $request['d'];
                $request['supplier'] = $request['supplier'];
                $request['keterangan'] = $request['ket'];

                $response = app('App\Services\ApiServiceCashier')->updatepenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                if($status == 'success'){
                    Cookie::queue(Cookie::forget('code_so_cash'));
                    return redirect('/cash/dash')->with($status,$note);
                }else{
                    return redirect('/cash/viewpenjualan?d='.$request['d'])->with($status,$note);
                }     
            }
        }
        
        public function cashpendingpenjualan(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);         
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];

                $request['code_data'] = $request['d'];
                $response = app('App\Services\ApiServicePenjualan')->viewpenjualan($request); 
                $results = is_array($response) ? $response : $response->getData(true); 
                $get_data = $results['results']['detail'];
                
                $request['code_data'] = $request['d'];
                $request['supplier'] = $request['supplier'];
                $request['keterangan'] = $request['ket'];

                $response = app('App\Services\ApiServiceCashier')->pendingpenjualan($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                if($status == 'success'){
                    Cookie::queue(Cookie::forget('code_so_cash'));
                    return redirect('/cash/dash')->with($status,$note);
                }else{
                    return redirect('/cash/viewpenjualan?d='.$request['d'])->with($status,$note);
                }     
            }
        }
        
        public function casheditaccount(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);          
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];
        
                $request->validate([
                    'full_name' => 'required|string|max:200',
                    'phone_number' => 'required|string|max:30',
                    'email' => 'required|string|email|max:200',
                ]);
                
                $request['id'] = $res_user['id'];

                $response = app('App\Services\ApiServiceCashier')->editadminCashier($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                if($status == 'success'){
                    return redirect('/cash/editaccount')->with($status,$note);
                }else{
                    if(isset($results['note']['email'])){
                        return redirect('/cash/editaccount')->with($status,$results['note']['email'][0]);
                    }else{
                        return redirect('/cash/editaccount')->with($status,$note);
                    }
                }
            }
        }

        public function casheditpassaccount(Request $request)
        {
            if(empty(session('key_token_perdana_cash')) && empty(session('admin_login_perdana_cash'))){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $get_user = $this->get_user_cashier($request);          
                if(!$get_user OR $get_user['status_message'] == 'failed'){return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');}
                $res_user = $get_user['results'][0]['detailadmin'][0];
        
                $request->validate([
                    'old_password' => 'required|string|max:30',
                    'new_password' => 'required|string|max:30',
                ]);
        
                if($request->old_password == $request->new_password){
                    return redirect('/cash/editaccount')->with('error','Kata sandi baru harus berbeda dengan kata sandi lama.');
                }

                // if(!Hash::check($request->old_password,$res_user['password'])){
                //     return redirect('/admin/editaccount')->with('error','Kata sandi lama salah.');
                // }
                
                $request['id'] = $res_user['id'];

                $response = app('App\Services\ApiServiceCashier')->editpassadminCashier($request);  
                $results = is_array($response) ? $response : $response->getData(true); 
                $status = $results['status_message'];
                $note = $results['note'];

                if($status == 'success'){
                    return redirect('/cash/logout')->with($status,'Data berhasil disimpan, silakan masuk kembali');
                }else{
                    return redirect('/cash/editaccount')->with($status,$note);
                }
            }
        }
    // end Cashier

    // Data Pengiriman Barang
    public function getcodepengiriman(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengirimanbarang')->getcodepengiriman($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 
    
    public function savepengiriman(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengirimanbarang')->savepengiriman($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);
        }
    }

    public function deletersobarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePengirimanbarang')->deletersobarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_rso'));
                return redirect('/admin/menupengirimanbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpengiriman?d='.$request['d'])->with($status,$note);
            }
        }
    }

    public function updatersobarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengirimanbarang')->updatersobarang($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            // return response()->json(['status_message' => $status,'note' => $note]);

            if($status == 'success'){
                return redirect('/admin/menupengirimanbarang')->with($status,$note);
            }else{
                return redirect('/admin/viewpengiriman?d='.$request['d'])->with($status,$note);
            }
        }
    }

    // Penyesuaian stock barang     
    public function getcodepenyesuaianstock(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenyesuaianstock')->getcodepenyesuaian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    }
    
    public function penyesuaianstockbarang(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'code_transaksi' => 'required|string|max:200',
                'tgl_transaksi' => 'required|string|max:200',
                'data_gudang' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServicePenyesuaianstock')->newstockopname($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/inputstockopname')->with($status,'Data berhasil disimpan, silakan input stock produk.');
            }else{
                return redirect('/admin/penyesuaianstockbarang')->with($status,$note);
            }
        }
    }

    public function savepenyesuaianstock(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];
            
            $response = app('App\Services\ApiServicePenyesuaianstock')->savepenyesuaianstock($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }
    
    public function updatenomorpenyesuaian(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenyesuaianstock')->updatenomorpenyesuaian($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return redirect('/admin/historypenyesuaianstockbarang')->with($status,$note);    
        }
    }

    // Mutasi Kirim 
    public function getcodemutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServiceMutasikirim')->getcodemutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 
    
    public function savemutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServiceMutasikirim')->savemutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);
        }
    }
    
    public function updatemutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];
            
            $request['code_data'] = $request['d'];
            $request['keterangan'] = $request['ket'];
            $response = app('App\Services\ApiServiceMutasikirim')->updatemutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            $request['code_data'] = $request['d'];
            $response = app('App\Services\ApiServiceMutasikirim')->viewmutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $get_data = $results['results']['detail'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_mtsk_perdana'));
                if($get_data['status_transaksi'] == 'Proses'){
                    return redirect('/admin/mutasikirim')->with($status,$note);
                }else{
                    return redirect('/admin/viewmutasikirim?d='.$request['d'])->with($status,$note);
                }
            }else{
                return redirect('/admin/viewmutasikirim?d='.$request['d'])->with($status,$note);
            }     
        }
    }

    public function deleteprodmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServiceMutasikirim')->deleteprodmutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function upqtymutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServiceMutasikirim')->upqtymutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function listsatuanmutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['id'] = $request['id'];
            $request['code_data'] = $request['code_data'];

            $response = app('App\Services\ApiServiceMutasikirim')->listsatuanmutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            return response()->json(['status_message' => $status,'note' => $note]);
        }
    }

    public function deletemutasikirim(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServiceMutasikirim')->deletemutasikirim($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_mtsk_perdana'));
                return redirect('/admin/mutasikirim')->with($status,$note);
            }else{
                return redirect('/admin/viewmutasikirim?d='.$request['d'])->with($status,$note);
            }
        }
    }

    // Mutasi Terima
    public function getcodemutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServiceMutasiterima')->getcodemutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 
    
    public function savemutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServiceMutasiterima')->savemutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return response()->json(['status_message' => $status,'note' => $note,'code' => $code]);
        }
    }

    public function deletemutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServiceMutasiterima')->deletemutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_mtst_perdana'));
                return redirect('/admin/mutasiterima')->with($status,$note);
            }else{
                return redirect('/admin/viewmutasiterima?d='.$request['d'])->with($status,$note);
            }
        }
    }
    
    public function updatemutasiterima(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];
            
            $request['code_data'] = $request['code_data'];
            $request['keterangan'] = $request['keterangan'];
            $response = app('App\Services\ApiServiceMutasiterima')->updatemutasiterima($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                Cookie::queue(Cookie::forget('code_mtst_perdana'));
                return redirect('/admin/mutasiterima')->with($status,$note);
            }else{
                return redirect('/admin/viewmutasiterima?d='.$request['d'])->with($status,$note);
            }     
        }
    }

    //Penerimaan Kas  
    public function getcodepay(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenerimaankas')->getcodepay($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 

    public function saveppenerimaankas(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePenerimaankas')->savepenerimaankas($request); 
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return redirect('/admin/menupenerimaankas')->with($status,$note);         
        }
    }

    public function deletepenerimaankas(Request $request)
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePenerimaankas')->deletepenerimaankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/menupenerimaankas')->with($status,$note);
            }else{
                return redirect('/admin/viewpenerimaankas?d='.$request['d'])->with($status,$note);
            }
        }
    }

    //Pengeluaran Kas  
    public function getcodepaykas(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengeluarankas')->getcodepaykas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    }
       
    public function saveppengeluarankas(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePengeluarankas')->savepengeluarankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return redirect('/admin/menupengeluarankas')->with($status,$note);           
        }
    }

    public function deletepengeluarankas(Request $request)
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['code_data'] = $request['d'];

            $response = app('App\Services\ApiServicePengeluarankas')->deletepengeluarankas($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];

            if($status == 'success'){
                return redirect('/admin/menupengeluarankas')->with($status,$note);
            }else{
                return redirect('/admin/viewpengeluarankas?d='.$request['d'])->with($status,$note);
            }
        }
    }

    //Pembayaran Pembelian - Purchase Payment - Hutang
    public function getcodepurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePembayaranpembelian')->getcodepurchasepayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 

    public function savepurchasepayment(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'tgl_transaksi' => 'required|string|max:200',
                'nomor_transaksi' => 'required|string|max:200',
                'nama_supplier' => 'required|string|max:200',
                'nomor_pembelian' => 'required|string|max:200',
                'pembayaran' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServicePembayaranpembelian')->savepurchasepayment($request); 
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];
            
            return redirect('/admin/menupembayaranhutang')->with($status,$note);         
        }
    }

    //Pembayaran Penjualan - Sales Payment - Piutang
    public function getcodesalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $response = app('App\Services\ApiServicePembayaranpenjualan')->getcodesalespayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code_data = $results['results'];

            return response()->json(['status_message' => $status,'note' => $note,'code_data' => $code_data]);
        }
    } 

    public function savesalespayment(Request $request)
    {
    	if(empty(session('key_token_perdana')) && empty(session('admin_login_perdana'))){
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
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request->validate([
                'tgl_transaksi' => 'required|string|max:200',
                'nomor_transaksi' => 'required|string|max:200',
                'nama_customer' => 'required|string|max:200',
                'nomor_penjualan' => 'required|string|max:200',
                'pembayaran' => 'required|string|max:200',
            ]);

            $response = app('App\Services\ApiServicePembayaranpenjualan')->savesalespayment($request);  
            $results = is_array($response) ? $response : $response->getData(true); 
            $status = $results['status_message'];
            $note = $results['note'];
            $code = $results['code'];

            return redirect('/admin/menupembayaranpiutang')->with($status,$note);           
        }
    }
}
