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
                    <?php if($res_user['tipe_user'] == 'Super User'){?>
                        <th style="width:150px; text-align: center;">Nama Perusahaan</th>
                    <?php } ?>
                    <th style="width:70px; text-align: center;">Tanggal</th>
                    <th style="width:70px; text-align: center;">Bulan</th>
                    <th style="width:70px; text-align: center;">Tahun</th>
                    <th style="width:170px; text-align: center;">No. Penerimaan</th>
                    <th style="width:170px; text-align: center;">Nama Supplier</th>
                    <th style="width:150px; text-align: center;">Pembelian Oleh</th>
                    <th style="width:170px; text-align: center;">No. Pembelian</th>
                    <th style="width:170px; text-align: center;">keterangan</th>
                    <th style="width:150px; text-align: center;">Qty</th>
                    <th style="width:150px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <?php if($res_user['tipe_user'] == 'Super User'){?>
                            <td>{{$listdata['detail_perusahaan'][$view_data['nomor_penerimaan']]['kantor']}}</td>
                        <?php } ?>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nomor_penerimaan']}}</td>
                        <td>{{$listdata['detail_supplier'][$view_data['nomor_penerimaan']]['nama']}}</td>
                        <td>{{$listdata['user_pembelian'][$view_data['nomor_penerimaan']]['full_name']}}</td>
                        <td>{{$listdata['detail_pembelian'][$view_data['nomor_penerimaan']]['nomor']}}</td>
                        <td style="text-align:left;">{{$view_data['ket']}}</td>
                        <td style="text-align:center;">{{$listdata['qty_penerimaan'][$view_data['nomor_penerimaan']]}}</td>
                        <td style="text-align:center;"><?php echo $view_data['status_transaksi'];?></td>
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