<html>
    <head>
        <title>Export to Excel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style> .strtable{ mso-number-format:\@; } table tr th,table tr td{border: 1px solid #000;} </style>
    </head>
    <body>
        <table class="table_view table-striped table-hover">
            <thead>
                <!-- <tr>
                    <th style="width:40px; text-align: center;">No</th>
                    <th style="width:250px; text-align: center;">Nama Barang</th>
                    <th style="width:170px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Stock</th>
                </tr> -->
                <tr>
                    <th rowspan="2" style="width:40px; text-align: center;">No</th>
                    <th rowspan="2" style="width:250px; text-align: center;">Nama Barang</th>
                    <th rowspan="2" style="width:170px; text-align: center;">Satuan</th>
                    <!-- HEADER UTAMA STOCK -->
                    <th colspan="{{ count($listdata['list_gudang']) }}" style="width:150px; text-align: center;">Stock</th>
                </tr>

                <tr>
                    <!-- SUB HEADER GUDANG -->
                    @foreach($listdata['list_gudang'] as $view_gudang)
                        <th style="min-width:150px; text-align:center;">{{ $view_gudang['nama'] ?? 'Belum Ditentukan' }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <td class="strtable">{{$view_data['nama_barang']}}</td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nama_satuan']}}</td>
                        <!-- <td class="strtable" style="text-align:center;"><?php echo number_format($listdata['stock_akhir'][$view_data['kode_barang']],0,"",".") ?></td> -->
                        @foreach($listdata['list_gudang'] as $view_gudang)                                                        
                            <td class="strtable" style="text-align:right;">{{$listdata['stok_pergudang'][$view_data['kode_barang']][$view_gudang['code_data']] ?? 0}}</td>                      
                        @endforeach 
                    </tr>
                @empty
                    <tr>
                        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="4">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>