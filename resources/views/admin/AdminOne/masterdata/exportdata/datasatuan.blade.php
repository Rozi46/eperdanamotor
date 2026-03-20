<html>
    <head>
        <title>Export to Excel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style> .strtable{ mso-number-format:\@; } table tr th,table tr td{border: 1px solid #000;} </style>
    </head>
    <body>
        <table class="table_view table-striped table-hover">
            <thead>
                <tr>
                    <th style="width:40px; text-align: center;">No</th>
                    <th style="width:150px; text-align: center;">Kode Data</th>
                    <th style="width:400px; text-align: center;">Nama Satuan</th>
                    <th style="width:100px; text-align: center;">Isi Satuan</th>
                    <th style="width:150px; text-align: center;">Pecahan Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                <?php
                    if($view_data['kode_pecahan'] == NULL){
                        $pecahansatuan = '-';
                    }elseif($view_data['kode_pecahan'] == '-'){
                        $pecahansatuan = '-';
                    }else{
                        $pecahansatuan = $listdata['satuan_pecahan'][$view_data['id']]['nama'];
                    }
                ?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
						<td class="strtable">{{$view_data['code_data']}}</td>
                        <td class="strtable" >{{$view_data['nama']}}</td>
						<td class="strtable" style="text-align:center;">{{$view_data['isi']}} </td>
						<td class="strtable" style="text-align:center;">{{$pecahansatuan}}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>