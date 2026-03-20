@extends('admin.AdminOne.layout.assets')
@section('title', 'Manual Book ')

@section('content')
    
    <iframe 
        src="{{ isset($request['file_manualbook']) ? asset('themes/admin/AdminOne/ManualBook/' . $request['file_manualbook'] . '?token=' . $request['token']) : '#' }}" 
        style="position:fixed; top:0; left:0; bottom:0; right:0; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;" > 
    </iframe> 
    
@endsection