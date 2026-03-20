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
                    <th style="width:170px; text-align: center;">No. Penyesuaian</th>
                    <th style="width:170px; text-align: center;">Gudang</th>
                    <th style="width:170px; text-align: center;">Nama Barang</th>
                    <th style="width:150px; text-align: center;">Stock Awal</th>
                    <th style="width:170px; text-align: center;">Stock Penyesuaian</th>
                    <th style="width:170px; text-align: center;">Stock Akhir</th>
                    <th style="width:150px; text-align: center;">Satuan</th>
                    <th style="width:250px; text-align: center;">Keterangan</th>
                    <th style="width:150px; text-align: center;">Di Input Oleh</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable" style="text-align:center;">{{ !empty($view_data['tanggal_transaksi']) ? Date::parse($view_data['tanggal_transaksi'])->format('d') : 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ !empty($view_data['tanggal_transaksi']) ? Date::parse($view_data['tanggal_transaksi'])->format('F') : 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ !empty($view_data['tanggal_transaksi']) ? Date::parse($view_data['tanggal_transaksi'])->format('Y') : 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['code_transaksi'] ?? 'Belum Ditentukan' }}</td>
                        <td class="strtable" >{{ $view_data['nama_gudang'] ?? 'Belum Ditentukan' }}</td>
                        <td class="strtable" style="text-align:left;" title="{{ $view_data['nama'] ?? 'Belum Ditentukan' }}">{{ $view_data['nama_barang'] ?? 'Belum Ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['stock_awal'] ?? 0 }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['stock_penyesuaian'] ?? 0 }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['stock_akhir'] ?? 0 }} </td>
                        <td class="strtable" style="text-align:left;">{{ $view_data['satuan'] ?? 'Belum Ditentukan' }}</td>
                        <td class="strtable" style="text-align:left;">{{ $view_data['keterangan'] ?? 'Belum Ditentukan' }}</td>
                        <td sclass="strtable" tyle="text-align:left;">{{ $view_data['user_input'] ?? 'Belum Ditentukan' }}</td>
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