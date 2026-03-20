<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Cabang};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePpn
{
    public function listppn($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data','results' => $object]);
        }else{
            $level_menu = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menufinance')->first();
            $level_action = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','menuppn')->first();
            
            if($level_menu->access_rights == 'No' OR $level_action->access_rights == 'No'){
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
            
            $kode_cabang = $request->get('nama_perusahaan');
            if($kode_cabang == Null or $kode_cabang ==''){
                $results['nama_perusahaan']['nama_cabang'] = 'Semua Perusahaan';
            }else{   
                $results['nama_perusahaan'] = Cabang::where('id',  $kode_cabang)->first();
            }
            
            $thn_now = $request->get('tahun');
            if($thn_now == Null or $thn_now ==''){
                $thn_now = Carbon::now()->format('Y');
            }  
            $bln_now = Carbon::now()->format('m');
            $hari_now = Carbon::now()->format('d');

            $results['thn_now'] = $thn_now;
            $results['bln_now'] = $bln_now;
                
            $months = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];

            for ($x = 1; $x <= 12; $x++) {                    
                if($kode_cabang == Null or $kode_cabang ==''){
                    $results['pembelian'.$x] = DB::table('db_pembelian')
                        ->whereMonth('tanggal', $x) 
                        ->whereYear('tanggal', $thn_now) 
                        ->where('kode_kantor', $viewadmin->kode_kantor) 
                        ->sum('ppn'); 
                }else{ 
                    $results['pembelian'.$x] = DB::table('db_pembelian')
                        ->whereMonth('tanggal', $x) 
                        ->whereYear('tanggal', $thn_now) 
                        ->where('kode_kantor', $viewadmin->kode_kantor) 
                        ->where('kode_cabang', $kode_cabang)
                        ->sum('ppn'); 
                }

                $results['penjualan'.$x] = DB::table('db_penjualan')
                    ->whereMonth('tanggal', $x) 
                    ->whereYear('tanggal', $thn_now) 
                    ->where('kode_kantor', $viewadmin->kode_kantor) 
                    ->sum('ppn'); 

                $results['months'.$x] = $months[$x];
            }  

            if($kode_cabang == Null or $kode_cabang ==''){
                $results['sum_pembelian'] = DB::table('db_pembelian')
                    ->whereYear('tanggal', $thn_now) 
                    ->where('kode_kantor', $viewadmin->kode_kantor) 
                    ->sum('ppn'); 
            }else{ 
                $results['sum_pembelian'] = DB::table('db_pembelian')
                    ->whereYear('tanggal', $thn_now) 
                    ->where('kode_kantor', $viewadmin->kode_kantor) 
                    ->where('kode_cabang', $kode_cabang)
                    ->sum('ppn'); 
            }

            $results['sum_penjualan'] = DB::table('db_penjualan')
                ->whereYear('tanggal', $thn_now) 
                ->where('kode_kantor', $viewadmin->kode_kantor) 
                ->sum('ppn'); 

            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $results ]);
        }
    }
}