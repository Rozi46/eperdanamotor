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
                    <?php if($res_user['tipe_user'] == 'Super User'){?>
                        <th style="width:150px; text-align: center;">Nama Perusahaan</th>
                    <?php } ?>
                    <th style="width:70px; text-align: center;">Tanggal</th>
                    <th style="width:70px; text-align: center;">Bulan</th>
                    <th style="width:70px; text-align: center;">Tahun</th>
                    <th style="width:170px; text-align: center;">No. Transaksi</th>
                    <th style="width:150px; text-align: center;">Pembelian Oleh</th>
                    <th style="width:250px; text-align: center;">Supplier</th>

                    <th style="width:300px; text-align: center;">Nama Produk</th>
                    <th style="width:100px; text-align: center;">Jumlah</th>
                    <th style="width:100px; text-align: center;">Satuan</th>
                    <th style="width:200px; text-align: center;">Harga</th>
                    <th style="width:200px; text-align: center;">Diskon Barang (%)</th>
                    <th style="width:200px; text-align: center;">Diskon Barang (Rp)</th>
                    <th style="width:200px; text-align: center;">Harga Netto</th>
                    <th style="width:200px; text-align: center;">Total Harga</th>

                    <!-- <th style="min-width:125px; text-align: center;">Subtotal</th>
                    <th style="min-width:125px; text-align: center;">PPN</th> -->
                    <th style="width:200px; text-align: center;">Total Faktur</th>
                    <th style="width:200px; text-align: center;">Diskon Faktur</th>
                    <th style="width:200px; text-align: center;">Cash Diskon</th>
                    <!-- <th style="min-width:125px; text-align: center;">Biaya Kirim (+)</th> -->
                    <th style="width:200px; text-align: center;">Grand Total</th>

                    <th style="min-width:200px; text-align: center;">Jenis PPN</th>
                    <th style="min-width:200px; text-align: center;">PPN</th>

                    <th style="width:250px; text-align: center;">Ket</th>
                    <!-- <th style="min-width:125px; text-align: center;">Jenis Pembelian</th> -->
                    <th style="width:200px; text-align: center;">Gudang</th>
                    <th style="width:120px; text-align: center;">Status</th>
                    <th style="width:200px; text-align: center;">Perusahaan Pembeli</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> 
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
                                    <td class="strtable" style="text-align:center;" rowspan="{{ $rowspan }}">{{$no}}</td>
                                    @if($res_user['tipe_user'] == 'Super User')
                                        <td class="strtable" rowspan="{{ $rowspan }}">{{$listdata['detail_perusahaan'][$view_data['code_data']]['kantor']}}</td>
                                    @endif
                                    <!-- <td rowspan="{{ $rowspan }}">{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td> -->
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $view_data['nomor'] ?? 'Belum Ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['user_input'][$view_data['code_data']]['full_name'] ?? 'Belum ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['detail_supplier'][$view_data['code_data']]['nama'] ?? 'Belum ditentukan'}}</td>
                                @endif

                                <td class="strtable" >{{ $listdata['detail_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                                <td style="text-align:center;">{{ (float) ($view_produk['jumlah_beli'] ?? 0) }}</td>
                                <td class="strtable" style="text-align:center;">{{ $listdata['satuan_barang_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                                <td style="text-align:right;">{{ (float) ($view_produk['harga'] ?? 0) }}</td>

                                <!-- <td style="text-align:center;">{{ number_format($view_produk['diskon_harga'] ?? 0,2,",",".") }} + {{ number_format($view_produk['diskon_harga2'] ?? 0,2,",",".") }}</td> -->
                                
                                @php
                                    $diskonList = [
                                        [
                                            'persen' => $view_produk['diskon_persen'] ?? 0,
                                            'harga'  => $view_produk['diskon_harga'] ?? 0,
                                        ],
                                        [
                                            'persen' => $view_produk['diskon_persen2'] ?? 0,
                                            'harga'  => $view_produk['diskon_harga2'] ?? 0,
                                        ],
                                        [
                                            'persen' => $view_produk['diskon_persen3'] ?? 0,
                                            'harga'  => $view_produk['diskon_harga3'] ?? 0,
                                        ],
                                    ];

                                    $filtered = array_filter($diskonList, fn($d) => $d['persen'] > 0);

                                    if (empty($filtered)) {
                                        $filtered = [
                                            ['persen'=>0,'harga'=>0]
                                        ];
                                    }
                                @endphp
                                
                                <td style="text-align:center;">
                                    @foreach($filtered as $d)
                                        {{ (float) ($d['persen']) }}
                                        @if(!$loop->last)<br>@endif                                         
                                    @endforeach
                                </td>
                                <td style="text-align:center;">
                                    @foreach($filtered as $d)
                                        {{ (float) ($d['harga']) }}
                                        @if(!$loop->last)<br>@endif                                         
                                    @endforeach
                                </td>

                                <td style="text-align:right;">{{ (float) ($view_produk['harga_netto'] ?? 0) }}</td>
                                <td style="text-align:right;">{{ (float) ($view_produk['total_harga'] ?? 0) }}</td>

                                @if($index === 0)
                                    <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ (float) ($view_data['total'] ?? 0) }}</td>
                                    <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ (float) ($view_data['diskon_harga'] ?? 0) }}</td>
                                    <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ (float) ($view_data['diskonCash_harga'] ?? 0) }}</td>
                                    <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ (float) ($view_data['grand_total'] ?? 0) }}</td>

                                    <td class="strtable" rowspan="{{ $rowspan }}">{{$view_data['jenis_ppn']}}</td>
                                    <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ (float) ($view_data['ppn'] ?? 0) }}</td>

                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $view_data['ket'] }}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}" style="text-align:center;">{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</td>
                                    <td class="strtable" rowspan="{{ $rowspan }}">{{ $listdata['detail_cabang'][$view_data['code_data']]['nama_cabang'] ?? 'Belum Ditentukan'}}</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="strtable" style="text-align:center;">{{$no}}</td>
                            @if($res_user['tipe_user'] == 'Super User')
                                <td rowspan="{{ $rowspan }}" class="strtable" >{{$listdata['detail_perusahaan'][$view_data['code_data']]['kantor']}}</td>
                            @endif
                            <!-- <td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td> -->
                            <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                            <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                            <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                            <td class="strtable" style="text-align:center;">{{ $view_data['nomor'] ?? 'Belum Ditentukan'}}</td>
                            <td class="strtable" style="text-align:left;">{{ $listdata['user_input'][$view_data['code_data']]['full_name'] ?? 'Belum ditentukan'}}</td>
                            <td class="strtable" style="text-align:left;">{{ $listdata['detail_supplier'][$view_data['code_data']]['nama'] ?? 'Belum ditentukan'}}</td>
                            <td class="strtable" colspan="7" style="text-align:center;">Tidak ada data yang tersedia</td>
                            <!-- <td style="text-align:right;">{{ number_format($view_data['sub_total'] ?? 0,2,",",".") }}</td>
                            <td style="text-align:right;">{{ number_format($view_data['ppn'] ?? 0,2,",",".") }}</td> -->
                            <td style="text-align:right;">{{ (float) ($view_data['total'] ?? 0) }}</td>
                            <td style="text-align:right;">{{ (float) ($view_data['diskon_harga'] ?? 0) }}</td>
                            <td style="text-align:right;">{{ (float) ($view_data['diskonCash_harga'] ?? 0) }}</td>
                            <!-- <td style="text-align:right;">{{ number_format($view_data['biaya_kirim'] ?? 0,2,",",".") }}</td> -->
                            <td style="text-align:right;">{{ (float) ($view_data['grand_total'] ?? 0) }}</td>

                            <td class="strtable" >{{ $view_data['jenis_ppn']}}</td>
                            <td style="text-align:right;">{{ (float)($view_data['ppn'] ?? 0) }}</td>


                            <td class="strtable" >{{$view_data['ket']}}</td>
                            <!-- <td>{{$view_data['jenis_pembelian']}}</td> -->
                            <td class="strtable" >{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan'}}</td>
                            <td class="strtable" style="text-align:center;">{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</td>
                            <td class="strtable" >{{ $listdata['detail_cabang'][$view_data['code_data']]['nama_cabang'] ?? 'Belum Ditentukan'}}</td>
                        </tr>
                    @endif
                    
                @empty
                    <tr>
                        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>