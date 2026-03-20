<?php

namespace App\Exports;


require '../vendor/autoload.php';

use Illuminate\Http\{Request,Response,UploadedFile};
use Illuminate\Support\Facades\{Http,Route,Session,Hash};
use Illuminate\Support\Carbon;
use Jenssegers\Date\Date;
use App\Http\Controllers\{Controller,ApiController};
use Artisan;
use Cookie;
use JWTAuth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class Cabang implements FromView
{
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
    	if(empty(session('key_token_perdana'))){
    		return redirect('logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            date_default_timezone_set('Asia/Jakarta');
            $url_api =  env('APP_API');
            $admin_login = session('admin_login_perdana');
            $key_token = session('key_token_perdana');        
            $request['url_api'] = $url_api;
            $request['u'] = $admin_login;
            $request['token'] = $key_token;            
            $request = $this->request;

            $get_user = app('App\Http\Controllers\ApiController')->getadmin($request);           
            $get_user = is_array($get_user) ? $get_user : $get_user->getData(true);  
            $res_user = $get_user['results'][0]['detailadmin'][0];

            $request['vd'] = '999999';
            $request['type'] = 'export';
            
            // panggil API versi lama 
            // $results[] = app('App\Services\ApiServiceBarang')->listcabang($request);  
            // $results = collect($results)->toJson();
            // $results = json_decode($results,true);
            // $results = $results[0]['original'];  

            // panggil API versi baru
            // $results = app('App\Services\ApiServiceBarang')->listcabang($request);
            // $results = is_array($results) ? $results : $results->getData(true);
            
            $results = app('App\Services\ApiServiceCabang')->listcabang($request);
            $results = is_array($results) ? $results : $results->getData(true);

            return view('admin.AdminOne.masterdata.exportdata.datacabang',['url_api' => $url_api,'app' => 'users','url_active' => 'listusers','request' => $request,'res_user' => $res_user,'results' => $results['results']['list'],'listdata' => $results['results']]);


            
        }
    }
}