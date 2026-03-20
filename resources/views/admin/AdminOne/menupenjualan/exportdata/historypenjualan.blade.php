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
                    <th style="width:170px; text-align: center;">No. Transaksi</th>
                    <th style="width:150px; text-align: center;">Penjualan Oleh</th>
                    <th style="width:250px; text-align: center;">Customer</th>
                    <!-- <th style="width:150px; text-align: center;">Qty Penjualan</th>
                    <th style="width:150px; text-align: center;">Qty Dikirim</th>
                    <th style="width:150px; text-align: center;">Qty Belum Dikirim</th>
                    <th style="width:150px; text-align: center;">Nilai Transaksi</th>
                    <th style="width:150px; text-align: center;">Status Penjualan</th> -->

                    <th style="width:300px; text-align: center;">Nama Produk</th>
                    <th style="width:100px; text-align: center;">Jumlah</th>
                    <th style="width:100px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Harga</th>
                    <th style="width:150px; text-align: center;">Diskon Barang (-)</th>
                    <th style="width:150px; text-align: center;">Harga Netto</th>
                    <th style="width:150px; text-align: center;">Total Harga</th>

                    <!-- <th style="width:200px; text-align: center;">DPP</th>
                    <th style="width:200px; text-align: center;">PPN</th> -->
                    <th style="width:200px; text-align: center;">Sub Total</th>
                    <th style="width:200px; text-align: center;">Diskon (-)</th>
                    <th style="width:200px; text-align: center;">Biaya Kirim (+)</th>
                    <th style="width:200px; text-align: center;">Grand Total</th>
                    <th style="width:250px; text-align: center;">Ket</th>
                    <th style="width:250px; text-align: center;">Gudang</th>
                    <th style="width:250px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) 
                    <?php 
                        $no++;
                        $produk_list = $listdata['list_produk'][$view_data['nomor']] ?? [];
                        $rowspan = count($produk_list) > 0 ? count($produk_list) : 1;
                    ?>					
                    <script>
                        function viewdata_{{$no}}() {
                            loadingpage(2000);
                            window.location.href = "/admin/viewpenjualan?d={{$view_data['nomor']}}";
                        }
                        <?php if($view_data['kode_user'] != null && $view_data['status_transaksi'] != 'Proses'){?>
                            function printdata_{{$no}}() {
                                window.open("/admin/printsalesorder?d={{$view_data['nomor']}}");
                            }
                        <?php } ?>
                    </script>
                    @if(count($produk_list) > 0)
                        @foreach($produk_list as $index => $view_produk)
                            <tr>
                                @if($index === 0)
                                    <td style="text-align:center;" rowspan="{{ $rowspan }}">{{$no}}
                                    </td>
                                    <!-- <td rowspan="{{ $rowspan }}">{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td> -->
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $view_data['nomor'] ?? 'Belum Ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['user_input'][$view_data['code_data']]['full_name'] ?? 'Belum ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['detail_customer'][$view_data['code_data']]['nama'] ?? 'Belum ditentukan'}}</td>
                                @endif

                                <td class="strtable" >{{ $listdata['detail_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                                <td class="strtable" style="text-align:center;">{{ $view_produk['jumlah_jual'] ?? 0 }}</td>
                                <td class="strtable" style="text-align:center;">{{ $listdata['satuan_barang_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                                <td class="strtable" style="text-align:right;">{{ $view_produk['harga'] ?? 0 }}</td>
                                <td class="strtable" style="text-align:center;">{{ $view_produk['diskon_harga'] ?? 0 }} + {{ $view_produk['diskon_harga2'] ?? 0 }}</td>
                                <td class="strtable" style="text-align:right;">{{ $view_produk['harga_netto'] ?? 0 }}</td>
                                <td class="strtable" style="text-align:right;">{{ $view_produk['total_harga'] ?? 0 }}</td>

                                @if($index === 0)
                                    <!-- <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['sub_total'] ?? 0 }}</td>
                                    <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['ppn'] ?? 0 }}</td> -->
                                    <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['total'] ?? 0 }}</td>
                                    <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['diskon_harga'] ?? 0 }}</td>
                                    <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['biaya_kirim'] ?? 0 }}</td>
                                    <td class="strtable" style="text-align:right;" rowspan="{{ $rowspan }}">{{ $view_data['grand_total'] ?? 0 }}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $view_data['ket'] ?? 'Belum Ditentukan' }}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
                                    <td class="strtable" style="text-align:center;" rowspan="{{ $rowspan }}">{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="strtable" style="text-align:center;">{{$no}}</td>
                            <td class="strtable" >{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
                            <td class="strtable">{{ $view_data['nomor'] ?? 'Belum Ditentukan' }}</td>
                            <td class="strtable" style="text-align:left;">{{ $listdata['user_input'][$view_data['code_data']]['full_name'] ?? 'Belum Ditentukan' }}</td>
                            <td class="strtable" style="text-align:left;">{{ $listdata['detail_customer'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
                            <td class="strtable" colspan="7" style="text-align:center;">Tidak ada data yang tersedia</td>
                            <!-- <td class="strtable" style="text-align:right;">{{ $view_data['sub_total'] ?? 0 }}</td>
                            <td class="strtable" style="text-align:right;">{{ $view_data['ppn'] ?? 0 }}</td> -->
                            <td class="strtable" style="text-align:right;">{{ $view_data['total'] ?? 0}}</td>
                            <td class="strtable" style="text-align:right;">{{ $view_data['diskon_harga'] ?? 0 }}</td>
                            <td class="strtable" style="text-align:right;">{{ $view_data['biaya_kirim'] ?? 0 }}</td>
                            <td class="strtable" style="text-align:right;">{{ $view_data['grand_total'] ?? 0 }}</td>
                            <td class="strtable" >{{ $view_data['ket'] ?? 'Belum Ditentukan' }}</td>
                            <td class="strtable" >{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
                            <td class="strtable" style="text-align:center;">{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td class="strtable" style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>