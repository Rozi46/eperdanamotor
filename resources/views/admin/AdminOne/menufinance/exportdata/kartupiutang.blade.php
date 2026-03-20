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
                    <th style="width:200px; text-align: center;">No. Transaksi</th>
                    <th style="width:200px; text-align: center;">Penjualan Oleh</th>
                    <!-- <th style="min-width:500px; text-align: center;">Daftar Transaksi</th> -->

                    <th style="width:400px; text-align: center;">Nama Produk</th>
                    <th style="width:100px; text-align: center;">Jumlah</th>
                    <th style="width:100px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Harga Netto</th>
                    <th style="width:150px; text-align: center;">Total Harga</th>

                    <th style="width:200px; text-align: center;">Total</th>
                    <th style="width:200px; text-align: center;">Jumlah Bayar</th>
                    <th style="width:200px; text-align: center;">Saldo Piutang</th>
                    <th style="width:200px; text-align: center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0; ?>
                @forelse($results['data'] as $view_data)
                    <?php 
                        $no++; 
                        $produk_list = $listdata['list_produk'][$view_data['nomor']] ?? [];
                        $rowspan = count($produk_list) > 0 ? count($produk_list) : 1; 
                    ?>
                    @if(count($produk_list) > 0)
                        @foreach($produk_list as $index => $view_produk)
                        <tr>
                            @if($index === 0)
                                <td style="text-align:center;" rowspan="{{ $rowspan }}">{{ $no }}</td>
                                <!-- <td rowspan="{{ $rowspan }}">{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td> -->
                                <td rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                                <td rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                                <td rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>

                                <td rowspan="{{ $rowspan }}">{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $listdata['user_input'][$view_data['nomor']]['full_name'] ?? 'Belum ditentukan' }}</td>
                            @endif
                            <td>{{ $listdata['detail_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                            <td style="text-align:center;">{{ $view_produk['jumlah_jual'] ?? 'Belum ditentukan' }}</td>
                            <td style="text-align:center;">{{ $listdata['satuan_barang_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                            <td style="text-align:right;">{{ $view_produk['harga_netto'] ?? 0 }}</td>
                            <td style="text-align:right;">{{ $view_produk['total_harga'] ?? 0 }}</td>
                            @if($index === 0)
                                <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['jumlah'] ?? 0 }}</td>
                                <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['bayar'] ?? 0 }}</td>
                                <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['sisa'] ?? 0 }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $view_data['ket'] ?? 'Belum ditentukan' }}</td>
                            @endif
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td style="text-align:center;">{{ $no }}</td>
                            <td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
                            <td>{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
                            <td>{{ $listdata['user_input'][$view_data['code_data']]['full_name'] ?? 'Belum ditentukan' }}</td>
                            <td colspan="5" style="text-align: center;">Tidak ada produk</td>
                            <td style="text-align:right;">{{ $view_data['jumlah'] ?? 0 }}</td>
                            <td style="text-align:right;">{{ $view_data['bayar'] ?? 0 }}</td>
                            <td style="text-align:right;">{{ $view_data['sisa'] ?? 0 }}</td>
                            <td>{{ $view_data['ket'] ?? 'Belum ditentukan' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; font-weight: 600; font-size: 14px;" colspan="13">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>