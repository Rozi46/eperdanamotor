@extends('admin.AdminOne.layout.assets')
@section('title', 'Print '.$request['title_print'])

@section('content')
    
    <iframe src="{{asset('/print/'.$request['file_print'].'.php?api='.$url_api.'&token='.$request['token'].'&u='.$request['u'].'&d='.$request['d'].'&tp='.$request['tp'].'&ak='.$request['ak'].'&page='.$request['page'].'&vd='.$request['vd'].'&keysearch='.$request['keysearch'].'&searchdate='.$request['searchdate'])}}" style="position:fixed; top:0; left:0; bottom:0; right:0; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;" > 
    
@endsection