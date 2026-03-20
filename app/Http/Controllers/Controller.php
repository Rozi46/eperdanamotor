<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $process;
    
    public function get_user($request)
    {
    	if(!session()->has('key_token_perdana') || !session()->has('admin_login_perdana')){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;   
            $request['u'] = $admin_login;
            $request['token'] = $key_token;                    

            $response = app('App\Http\Controllers\ApiController')->getadmin($request);
            $get_user = $response->getData(true);

            return $get_user;
        }
    }
    
    public function get_setting($request)
    {
    	if(!session()->has('key_token_perdana') || !session()->has('admin_login_perdana')){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $response = app('App\Http\Controllers\ApiController')->getSetting($request);
            $get_setting = $response->getData(true);

            return $get_setting;
        }
    }

    public function get_akses($request)
    {
    	if(!session()->has('key_token_perdana') || !session()->has('admin_login_perdana')){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
            
            $response = app('App\Services\ApiServicePengaturan')->getlevelakses($request);
            $list_akses = $response->getData(true);            

            return $list_akses;
        }
    }

    public function get_op_level($request)
    {
    	if(!session()->has('key_token_perdana') || !session()->has('admin_login_perdana')){
    		return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;

            $response = app('App\Services\ApiServicePengaturan')->listoplevel($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    // backup_database MYSQL
    public function backup_database()
    {
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $backupPath = storage_path('app/backup');

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = $database.'_'.date('Ym').'.sql';
        $file = $backupPath.DIRECTORY_SEPARATOR.$filename;

        // DETECT OS
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mysqldump = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe';
        } else {
            $mysqldump = '/usr/bin/mysqldump';
        }

        if (!file_exists($mysqldump)) {
            return response()->json([
                'success' => false,
                'error'   => 'mysqldump tidak ditemukan di path: '.$mysqldump
            ]);
        }

        // gunakan environment password agar lebih aman
        putenv("MYSQL_PWD=".$password);

        $command = "\"$mysqldump\" --host=$host --port=$port --user=$username "
            ."--single-transaction --quick --lock-tables=false "
            ."$database > \"$file\"";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json([
                'success' => false,
                'command' => $command,
                'output'  => $output,
                'return'  => $returnVar
            ]);
        }

        if (!file_exists($file) || filesize($file) < 2000) {
            return response()->json([
                'success' => false,
                'error'   => 'Backup gagal / file terlalu kecil',
                'size'    => filesize($file)
            ]);
        }

        return response()->json([
            'success' => true,
            'file'    => $filename,
            'size'    => filesize($file),
            'path'    => $file
        ]);
    }

    // backup_database POSTGRESQL
    // public function backup_database()
    // {
    //     $host = env('DB_HOST');
    //     $port = env('DB_PORT', 5432);
    //     $database = env('DB_DATABASE');
    //     $username = env('DB_USERNAME');
    //     $password = env('DB_PASSWORD');

    //     $backupPath = storage_path('app/backup');
    //     if (!is_dir($backupPath)) {
    //         mkdir($backupPath, 0755, true);
    //     }

  
    //     $filename = $database.'_'.date('Ym').'.backup';
    //     $file = $backupPath.DIRECTORY_SEPARATOR.$filename;

    //     // DETECT OS
    //     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //         $pgDump = 'C:\Program Files\PostgreSQL\16\bin\pg_dump.exe';
    //     } else {
    //         $pgDump = '/usr/bin/pg_dump';
    //     }

    //     $command = "\"$pgDump\" -h $host -p $port -U $username -F c -b -v -f \"$file\" $database";

    //     putenv("PGPASSWORD=$password");

    //     exec($command." 2>&1", $output, $returnVar);

    //     if ($returnVar === 0 && file_exists($file) && filesize($file) > 0) {
    //         return response()->json(['success' => true,'file' => $filename,'size' => filesize($file),'path' => $file]);
    //     }

    //     return response()->json(['success' => false,'output' => $output,'return' => $returnVar]);
    // }
    
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
    // Contoh pemakaian
    // $kodeHuruf  = generateCode(4, 'letters'); // misal: "XZQP"
    // $kodeAngka  = generateCode(4, 'numbers'); // misal: "0385"
    // $kodeCampur = generateCode(6, 'mixed');   // misal: "A9C7XZ"    

    public function get_op_satuan($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopsatuan($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_kategori($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopkategori($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_merk($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopmerk($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_supplier($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopsupplier($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_gudang($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopgudang($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_cabang($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopcabang($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_akun($request)
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
            
            $response = app('App\Http\Controllers\ApiController')->listopakun($request);
            $results = is_array($response) ? $response : $response->getData(true);

            return $results;
        }
    }

    public function get_op_mekanik($request)
    {
        if(!session()->has('key_token_perdana') && !session()->has('admin_login_perdana')){
            return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
        }else{  
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');
            $load_app = $request->load;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;
                        
            $response = app('App\Services\ApiServicePenjualan')->listopmekanik($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            return $results;
        }
    }

    // Cashier    
        public function get_user_cashier($request)
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

                $response = app('App\Services\ApiServiceCashier')->getadminCashier($request);                
                $get_user = is_array($response) ? $response : $response->getData(true); 

                return $get_user;
            }
        }
    
        public function get_setting_cashier($request)
        {
            if(!session()->has('key_token_perdana_cash') || !session()->has('admin_login_perdana_cash')){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{ 
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;

                $response = app('App\Services\ApiServiceCashier')->getSettingCashier($request);                
                $get_setting = is_array($response) ? $response : $response->getData(true); 

                return $get_setting;
            }
        }

        public function get_akses_cashier($request)
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
                            
                $response = app('App\Services\ApiServiceCashier')->getlevelaksesCashier($request);
                $list_akses = $response->getData(true);

                return $list_akses;
            }
        }

        public function get_op_gudang_cashier($request)
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
                            
                $response = app('App\Services\ApiServiceCashier')->listopgudangCashier($request);
                $results = is_array($response) ? $response : $response->getData(true); 

                return $results;
            }
        }

        public function get_op_mekanik_cashier($request)
        {
            if(!session()->has('key_token_perdana_cash') && !session()->has('admin_login_perdana_cash')){
                return redirect('/admin/logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
            }else{  
                date_default_timezone_set('Asia/Jakarta');
                $url_api =  env('APP_API');
                $admin_login = session('admin_login_perdana_cash');
                $key_token = session('key_token_perdana_cash');
                $load_app = $request->load;
                $request['u'] = $admin_login;
                $request['token'] = $key_token;
                            
                $response = app('App\Services\ApiServiceCashier')->listopmekanikCashier($request);
                $results = is_array($response) ? $response : $response->getData(true); 

                return $results;
            }
        }
    // end Cashier
}
