<?php

namespace App\Exports;


require '../vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Jenssegers\Date\Date;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiControllerFinance;
use Artisan;
use Cookie;
use JWTAuth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DataPPN implements FromView, WithColumnFormatting
{
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        date_default_timezone_set('Asia/Jakarta');
        $url_api =  env('APP_API');
    	$admin_login = session('admin_login_perdana');
        $key_token = session('key_token_perdana');
        
        $request['url_api'] = $url_api;
        $request['u'] = $admin_login;
        $request['token'] = $key_token;

        $vd = $request->filled('vd') ? $request->vd : 20;

    	if(empty(session('key_token_perdana'))){
    		return redirect('logout')->with('error','Terjadi kesalahan!!! silahkan hubungi kami');
    	}else{ 
            $get_user = app('App\Http\Controllers\ApiController')->getadmin($request);           
            $get_user = is_array($get_user) ? $get_user : $get_user->getData(true);  
            $res_user = $get_user['results'][0]['detailadmin'][0];
            
            $response = app('App\Services\ApiServicePpn')->listppn($request);
            $results = is_array($response) ? $response : $response->getData(true); 

            // return $results;

            return view('admin.AdminOne.menufinance.exportdata.datappn',['url_api' => $url_api,'app' => 'menufinance','url_active' => 'menuppn','request' => $request,'res_user' => $res_user,'listdata' => $results['results']]);
        }
    }
    
    public function columnFormats(): array
    {
        // urutan kolom berdasarkan header tabel export excel, A dimulai No
        return [
            'C' => '#,##0.00', // pembelian
            'D' => '#,##0.00', // penjualan
        ];
    }
}