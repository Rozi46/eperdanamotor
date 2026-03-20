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
                    <th style="width:100px; text-align: center;">Bulan</th>
                    <th style="width:70px; text-align: center;">Tahun</th>
                    <th style="width:170px; text-align: center;">No. Transaksi</th>
                    <th style="width:400px; text-align: center;">Uraian</th>
                    <th style="width:150px; text-align: center;">Saldo Awal</th>
                    <th style="width:150px; text-align: center;">Masuk</th>
                    <th style="width:150px; text-align: center;">Keluar</th>
                    <th style="width:150px; text-align: center;">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="strtable" style="text-align:right;" colspan="6">Saldo Akhir <?php echo Date::parse($datefilterend)->format('d M Y'); ?> : </td>
                    <td class="strtable" style="text-align:right;" colspan="4">{{$listdata['saldo_akhir']['total']}}</td>
                </tr>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <?php
                        $masuk = $view_data['debet'];
                        $keluar = $view_data['kredit'];

                        if($no == 1){														
                            $e = $listdata['saldo_akhir']['total'];
                            $saldoawal = $e + $keluar - $masuk;
                            $saldoakhir = $saldoawal + $masuk - $keluar ;
                        }else{
                            $saldoakhir = $saldoawal;
                            $saldoawal = $saldoakhir + $keluar - $masuk;
                        }
                    ?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nomor']}}</td>
                        <td class="strtable">{{$view_data['keterangan']}}</td>
                        <td class="strtable" style="text-align:right;">{{$saldoawal}}</td>
                        <td class="strtable" style="text-align:right;">{{$masuk}}</td>
                        <td class="strtable" style="text-align:right;">{{$keluar}}</td>
                        <td class="strtable" style="text-align:right;">{{$saldoakhir}}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="10">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
                <tr>
                    <td class="strtable" style="text-align:right;" colspan="6">Saldo Awal <?php echo Date::parse($datefilterstart)->format('d M Y'); ?> :</td>
                    <td class="strtable" style="text-align:right;" >{{$listdata['saldo_awal']['total']}}</td>
                    <td class="strtable" style="text-align:right;" colspan="3"></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>