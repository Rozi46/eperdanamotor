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
                    <th style="width:70px; text-align: center;">Tanggal</th>
                    <th style="width:70px; text-align: center;">Bulan</th>
                    <th style="width:70px; text-align: center;">Tahun</th>
                    <th style="width:170px; text-align: center;">No. Pembayaran</th>
                    <th style="width:200px; text-align: center;">Pembayaran Penjualan Oleh</th>
                    <th style="width:250px; text-align: center;">Nomor Penjualan</th>
                    <th style="width:150px; text-align: center;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nomor']}}</td>
                        <td class="strtable">{{$listdata['user_input'][$view_data['code_data']]['full_name']}}</td>
                        <td class="strtable">{{$view_data['nomor_hutang']}} - [ {{$listdata['detail_supplier'][$view_data['code_data']]['nama']}} ]</td>
                        <td class="strtable" style="text-align:right;">{{$view_data['jumlah']}}</td>
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