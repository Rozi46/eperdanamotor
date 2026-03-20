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
                    <th style="width:270px; text-align: center;">Tanggal Aktivitas</th>
                    <th style="width:150px; text-align: center;">Kode Pengguna</th>
                    <th style="width:170px; text-align: center;">Nama Lengkap</th>
                    <th style="width:600px; text-align: center;">Keterangan Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php Date::setLocale('id'); $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable">{{ \Carbon\Carbon::parse($view_data['created_at'])->translatedFormat('l, j F Y - H:i:s') }}</td>
                        <td class="strtable" style="text-align:center;">{{$view_data['code_data']}}</td>
                        <td>{{$view_data['full_name']}}</td>
                        <td>{{$view_data['activity']}}</td>
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