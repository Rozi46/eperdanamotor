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
                    <th style="width:35px; text-align: center;">No</th>
                    <th style="width:70px; text-align: center;">Tanggal</th>
                    <th style="width:70px; text-align: center;">Bulan</th>
                    <th style="width:70px; text-align: center;">Tahun</th>
                    <th style="width:170px; text-align: center;">No. Mutasi</th>
                    <th style="width:150px; text-align: center;">Mutasi Oleh</th>
                    <th style="width:200px; text-align: center;">Nama Barang</th>
                    <th style="width:150px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Qty Kirim</th>
                    <th style="width:150px; text-align: center;">Gudang Asal</th>
                    <th style="width:150px; text-align: center;">Gudang Tujuan</th>
                    <th style="width:350px; text-align: center;">Keterangan</th>
                    <th style="width:150px; text-align: center;">Status Mutasi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{ $no }}</td>
                        <td class="strtable" style="text-align:center;">{{ Date::parse($view_data['tanggal'])->format('d') }} </td>
                        <td class="strtable" style="text-align:center;">{{ Date::parse($view_data['tanggal'])->format('F') }} </td>
                        <td class="strtable" style="text-align:center;">{{ Date::parse($view_data['tanggal'])->format('Y') }} </td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:left;">
                            {{ $listdata['user_input'][$view_data['id']]['full_name'] != 'null' ? $listdata['user_input'][$view_data['id']]['full_name'] : 'Belum ditentukan' }}
                        </td>                         
                        <td class="strtable" style="text-align:center;">{{ $listdata['detail_barang'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>                       
                        <td class="strtable" style="text-align:center;">{{ $listdata['detail_satuan'][$view_data['id']]['nama'] ?? 'Belum ditentukan'}}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['qty'] ?? 0 }}</td>
                        <td class="strtable" style="text-align:center;">{{ $listdata['detail_gudang_asal'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $listdata['detail_gudang_tujuan'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['ket'] ?? 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['status_transaksi'] ?? 'Belum ditentukan'}}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="strtable"  style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>