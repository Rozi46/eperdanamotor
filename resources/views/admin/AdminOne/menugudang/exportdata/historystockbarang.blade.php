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
                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="7">History Stock Barang</td>
                </tr>
                <tr>
                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="7">{{ $listdata['nama_gudang']['nama'] }}</td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <th style="width:40px; text-align: center;">No</th>
                    <th style="width:170px; text-align: center;">Nama Barang</th>
                    <th style="width:170px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Stock Awal</th>
                    <th style="width:170px; text-align: center;">Masuk</th>
                    <th style="width:170px; text-align: center;">Keluar</th>
                    <th style="width:150px; text-align: center;">Stock Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable">{{$view_data['nama_barang']}}</td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nama']}}</td>
                        <td class="strtable" style="text-align:center;"><?php echo number_format($listdata['stock_awal'][$view_data['kode_barang']],0,"",".") ?></td>
                        <td class="strtable" style="text-align:center;"><?php echo number_format($listdata['stock_masuk'][$view_data['kode_barang']],0,"",".") ?></td>
                        <td class="strtable" style="text-align:center;"><?php echo number_format($listdata['stock_keluar'][$view_data['kode_barang']],0,"",".") ?></td>
                        <td class="strtable" style="text-align:center;"><?php echo number_format($listdata['stock_akhir'][$view_data['kode_barang']],0,"",".") ?></td>
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