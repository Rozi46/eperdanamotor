<?php

namespace App\Services;

use App\Models\{Setting, Kantor, User, LevelAdmin, ListAkses, Activity, Barang, HistoryStock};
use Illuminate\Http\{Request, UploadedFile, Response};
use Illuminate\Support\Facades\{Hash, Validator, File, Http, Route, Session, Auth, DB, Lang};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Database\Query\Builder;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiServicePengaturan
{    
    // Pengaturan Level 
    public function getlevelakses($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $results['menu'] = ListAkses::where('menu', 'Yes')->orderBy('no_urut', 'DESC')->get();
            foreach($results['menu'] as $key => $menu){
                $results['count_used'][$menu->id] = ListAkses::where('menu_index',$menu->id)->count();
                $results['submenu'][$menu->id] = ListAkses::where('menu_index', $menu->id)
                    ->where('submenu', 'Yes')
                    ->orderBy('no_urut', 'ASC')
                    ->get();

                foreach($results['submenu'][$menu->id] as $key => $submenu){
                    $results['action'][$submenu->id] = ListAkses::where('menu_index', $submenu->id)->where('action', 'Yes')->orderBy('no_urut', 'ASC')->get();

                    foreach($results['action'][$submenu->id] as $key => $action){
                        $results['subaction'][$action->id] = ListAkses::where('menu_index', $action->id)->where('subaction', 'Yes')->orderBy('no_urut', 'ASC')->get();
                    }
                }
            }
                
            return response()->json(['status_message' => 'success','results' => $results]);
        }
    }

    public function actionsettingmenu($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            if($viewadmin['code_data'] == '8603264093R'){

                $validator = Validator::make($request->all(), [
                    'no_urut' => 'required|string',
                    'nama_menu' => 'required|string|max:200',
                    'nama_akses' => 'required|min:1|max:200|unique:db_list_akses_web',
                ]);

                if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                if($request->get('type_menu') == 'Menu'){
                    $menu = 'Yes';
                    $submenu = 'No';
                    $action = 'No';
                    $subaction = 'No';
                }elseif($request->get('type_menu') == 'SubMenu'){
                    $menu = 'No';
                    $submenu = 'Yes';
                    $action = 'No';
                    $subaction = 'No';
                }elseif($request->get('type_menu') == 'Action'){
                    $menu = 'No';
                    $submenu = 'No';
                    $action = 'Yes';
                    $subaction = 'No';
                }elseif($request->get('type_menu') == 'SubAction'){
                    $menu = 'No';
                    $submenu = 'No';
                    $action = 'No';
                    $subaction = 'Yes';
                }else{
                    $menu = 'Yes';
                    $submenu = 'No';
                    $action = 'No';
                    $subaction = 'No';
                }

                if($request->get('type_menu') != 'Menu'){

                    $validator = Validator::make($request->all(), [
                        'menu_index' => 'required|string',
                    ]);
    
                    if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}
                }

                if($request->get('icon_menu') == ''){
                    $icon_menu = 'fa fa-align-right';
                }else{
                    $icon_menu = $request->get('icon_menu');
                }

                $savedata = ListAkses::create([
                    'id' => Str::uuid(),
                    'no_urut' => $request->get('no_urut'),
                    'nama_menu' => $request->get('nama_menu'),
                    'nama_akses' => str_replace(' ', '',$request->get('nama_akses')),
                    'menu_index' => $request->get('menu_index'),
                    'menu' => $menu,
                    'submenu' => $submenu,
                    'action' => $action,
                    'subaction' => $subaction,
                    'icon_menu' => $icon_menu,
                ]);

                if($savedata){
                    $listlevel = DB::table('db_level_admin_web')
                        ->select('level_name',DB::raw('code_data'))
                        ->groupBy('level_name','code_data')
                        ->orderBy('level_name', 'ASC')
                        ->get();
                    foreach($listlevel as $key => $level){
                        $uuidlevel = Str::uuid();
                        if($level->code_data == 'LV5677001'){
                            $access_rights = 'Yes';
                        }else{
                            $access_rights = 'No';
                        }
                        LevelAdmin::create([
                            'id' => $uuidlevel,
                            'code_data' => $level->code_data,
                            'level_name' => $level->level_name,
                            'data_menu' => str_replace(' ', '',$request->get('nama_akses')),
                            'access_rights' => $access_rights
                        ]);
                    }

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','id_data' => Str::uuid(),'results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
        }
    }

    public function delmenu($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            if($viewadmin['code_data'] == '8603264093R'){
                $getdata = ListAkses::where('id', $request->id)->first();
                if($getdata){
                    $listmenu = ListAkses::where('menu_index', $getdata->id)->orderBy('no_urut', 'ASC')->get();
                    foreach($listmenu as $key => $list){
                        $listsubmenu[$list->id] = ListAkses::where('menu_index', $list->id)->orderBy('no_urut', 'ASC')->get();
                        foreach($listsubmenu[$list->id] as $key => $listsub){
                            $listaction[$listsub->id] = ListAkses::where('menu_index', $listsub->id)->orderBy('no_urut', 'ASC')->get();
                            foreach($listaction[$listsub->id] as $key => $listact){
                                $listsubaction[$listact->id] = ListAkses::where('menu_index', $listact->id)->orderBy('no_urut', 'ASC')->get();
                                foreach($listsubaction[$listact->id] as $key => $listsubact){
                                    LevelAdmin::where('data_menu', $listsubact->nama_akses)->delete();
                                    ListAkses::where('menu_index', $listsubact->id)->delete();
                                }
                                LevelAdmin::where('data_menu', $listact->nama_akses)->delete();
                                ListAkses::where('menu_index', $listact->id)->delete();
                            }
                            LevelAdmin::where('data_menu', $listsub->nama_akses)->delete();
                            ListAkses::where('menu_index', $listsub->id)->delete();
                        }
                        LevelAdmin::where('data_menu', $list->nama_akses)->delete();
                        ListAkses::where('menu_index', $list->id)->delete();
                    }
                    LevelAdmin::where('data_menu', $getdata->nama_akses)->delete();
                    ListAkses::where('menu_index', $getdata->id)->delete();
                    ListAkses::where('id', $request->id)->delete();

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            }
        }
    }

    public function listoplevel($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $level_akses = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','users')->first();
            $level_sub_akses = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','listusers')->first();
            $level_sub_sub_akses = LevelAdmin::where('code_data', $viewadmin->level)->where('data_menu','=','newusers')->first();
            
            if($level_akses->access_rights == 'Yes' && $level_sub_akses->access_rights == 'Yes' && $level_sub_sub_akses->access_rights == 'Yes'){

                $results = DB::table('db_level_admin_web')
                    ->select('level_name',DB::raw('code_data'))
                    ->groupBy('level_name','code_data')
                    ->orderBy('level_name', 'ASC')
                    ->get();
                    
                return response()->json(['status_message' => 'success','results' => $results]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Tidak ada akses','results' => $object]);
            } 
        }
    }

    // Data Perusahaan
    public function listcompany($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $vd = $request->filled('vd') ? $request->vd : 20;
            
            $results['listdata'] = Kantor::where(function($query) use ($request) {
                    $query->Where('kode','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('kantor','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('jenis','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('alamat','LIKE', '%'.$request->keysearch.'%')
                    ->orWhere('email','LIKE', '%'.$request->keysearch.'%');
                })
                ->orderBy('id', 'ASC')
                ->paginate($vd ? $vd : 20);

            foreach($results['listdata'] as $key => $data){
                $results['count_used'][$data->id] = User::where('kode_kantor', $data->kode)->count();
            }
                
            return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','count_all_data' => $results['listdata']->total(),'count_view_data' => $vd,'keysearch' => $request->keysearch,'results' => $results]);
        }
    }
                
    public function newcompany($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if($viewadmin){
            $validator = Validator::make($request->all(), [
                'kode' => 'required|string|max:200',
                'nama' => 'required|string|max:200',
                'jenis' => 'required|string|max:200',
                'alamat' => 'required|string|max:200',
                'email' => 'required|email|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $savedata = Kantor::create([
                'kode' => $request->get('kode'),
                'kantor' => $request->get('nama'),
                'jenis' => $request->get('jenis'),
                'alamat' => $request->get('alamat'),
                'email' => $request->get('email'),
                'ket' => 'SERVER',
                'foto' => Null,
            ]);

            if($savedata){
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                $time = Carbon::now()->format('Ymdhis');
                $newCodeData_activity = $time."".$otp;
                $newCodeData_activity = ltrim($newCodeData_activity, '0');
                Activity::create([
                    'id' => Str::uuid(),
                    'code_data' => $newCodeData_activity,
                    'kode_user' => $viewadmin->id,
                    'activity' => 'Tambah data perusahan ['.$request->nama.' - '.$request->get('kode').']',
                    'kode_kantor' => $viewadmin->kode_kantor,
                ]);
                return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 

    }

    public function viewcompany($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
        if($viewadmin){
            $getdata['kantor'] = Kantor::where('id', $request->id)->first();
            if($getdata['kantor']){ 
                $count_used = User::where('kode_kantor', $getdata['kantor']->kode)->count();            
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata,'count_used' => $count_used]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }else{
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        } 
    }

    public function editcompany($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{            
            $get_data['company'] = Kantor::where('id', $request->id_data)->first();
            $validator = Validator::make($request->all(), [
                'kode_company' => 'required|string|max:100',
                'nama_company' => 'required|string|max:100',
                'jenis_company' => 'required|string|max:100',
                'alamat_company' => 'required|string|max:100',
                'email_company' => 'required|string|email|max:200',
            ]);

            if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

            $update = Kantor::where('id', $request->id_data)
                ->update([
                    'kode' => $request->get('kode_company'),
                    'kantor' => $request->get('nama_company'),
                    'jenis' => $request->get('jenis_company'),
                    'alamat' => $request->get('alamat_company'),
                    'email' => $request->get('email_company'),
            ]);
             
            if($update){
                if($request->logo_company != ''){
                    // Penggunaan pada controller
                    // $request->validate(['logo_company' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2548']); 

                    // Penggunaan pada service
                    $request->validate([
                        'logo_company' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2548'
                    ]);

                    $imageName = 'PK-'.$request->kode_company.'-'.time().'.'.$request->logo_company->extension();

                    $request->logo_company->move(public_path('/themes/admin/AdminOne/image/public/'), $imageName); 

                    Kantor::where('id', $request->id_data)
                    ->update([
                        'foto' => $imageName,
                    ]);

                    File::delete(public_path('/themes/admin/AdminOne/image/public/'.$get_data['company']->foto.''));
                    $file = $request->file('logo_company');
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $file->getClientOriginalName()]);
                }else{
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan','results' => $object]);
            }
        }
    }

    public function deletecompany($request)
    {
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();

        if(!$viewadmin){
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }else{
            $getdata['company'] = Kantor::where('id', $request->id)->first();
            if($getdata['company']){
                $DelData = $getdata['company']->delete();
                File::delete(public_path('/themes/admin/AdminOne/image/public/'.$getdata['company']->foto.''));
                
                if($DelData){
                    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $otp = substr(str_shuffle(str_repeat($pool, 1)), 0, 1);                    
                    $time = Carbon::now()->format('Ymdhis');
                    $newCodeData_activity = $time."".$otp;
                    $newCodeData_activity = ltrim($newCodeData_activity, '0');

                    Activity::create([
                        'id' => Str::uuid(),
                        'code_data' => $newCodeData_activity,
                        'kode_user' => $viewadmin->id,
                        'activity' => 'Hapus data perusahan ['.$getdata['company']->nama.' - '.$getdata['company']->kode.']',
                        'kode_kantor' => $viewadmin->kode_kantor,
                    ]);
                    return response()->json(['status_message' => 'success','note' => 'Data berhasil dihapus','results' => $object]);
                }else{
                    return response()->json(['status_message' => 'error','note' => 'Data gagal dihapus','results' => $object]);
                }
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    // Manual Book 
    public function viewManualBook($request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
        if(!$viewadmin){ 
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }else{
            $getdata['setting'] = Setting::where('id', '1')->first();
            if($getdata['setting']){           
                return response()->json(['status_message' => 'success','note' => 'Proses data berhasil','results' => $getdata]);
            }else{
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }
        }
    }

    public function uploadmanualbook($request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $object = [];
        $viewadmin = User::where('id', $request->u)->where('key_token', $request->token)->first();
        if(!$viewadmin){ 
            return response()->json(['status_message' => 'error','note' => 'Terjadi kesalahan saat proses data']);
        }else{
            $getdata['setting'] = Setting::find(1);

            if ($getdata['setting']) {
                try {
                    if (!$request->hasFile('manual_book')) {
                        return response()->json(['status_message' => 'error','note' => 'Manual book tidak ditemukan dalam permintaan.','results' => $object]);
                    }

                    $validator = Validator::make($request->all(), [
                        'manual_book' => 'required|mimes:pdf,doc,docx|max:20480', // max 20MB
                    ]);

                    if($validator->fails()){return response()->json(['status_message' => 'error','note' => $validator->errors()]);}

                    DB::beginTransaction();

                    $oldFile = $getdata['setting']->manual_book;
                    $manualbookName = 'MB-' . time() . '.' . $request->manual_book->getClientOriginalExtension();
                    $request->manual_book->move(public_path('themes/admin/AdminOne/ManualBook/'), $manualbookName);

                    if ($oldFile) {
                        $path = public_path('themes/admin/AdminOne/ManualBook/' . $oldFile);
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }

                    $getdata['setting']->update([
                        'manual_book' => $manualbookName,
                    ]);

                    $otp = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
                    $newCodeData_activity = ltrim(Carbon::now()->format('Ymdhis') . $otp, '0');

                    Activity::create([
                        'id'            => Str::uuid(),
                        'code_data'     => $newCodeData_activity,
                        'kode_user'     => $viewadmin->id ?? null,
                        'activity'      => 'Ubah manual book aplikasi',
                        'kode_kantor'   => $viewadmin->kode_kantor ?? null,
                    ]);

                    DB::commit();

                    return response()->json(['status_message' => 'success','note' => 'Data berhasil disimpan','results' => $manualbookName
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status_message' => 'error','note' => 'Data gagal disimpan: ' . $e->getMessage(),'results' => $object]);
                }
            } else {
                return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
            }

        } 

    }

    public function downloadmanualbook($request)
    {
        $object = [];            
        $filePath = public_path('/themes/admin/AdminOne/ManualBook/' . $request['d']);
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return response()->json(['status_message' => 'error','note' => 'Data tidak ditemukan','results' => $object]);
        }
    }
}