<html>
    <head>
        <title>Export to Excel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style> table tr th,table tr td{border: 1px solid #000;} </style>
    </head>
    <body>
        <table class="table_view table-striped table-hover">
            <thead>
                <tr>
                    <th style="width:40px; text-align: center;">No</th>
                    <th style="width:200px; text-align: center;">Kode Data</th>
                    <th style="width:400px; text-align: center;">Nama Barang</th>
                    <th style="width:100px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Kategori</th>
                    <th style="width:150px; text-align: center;">Merk</th>
                    <th style="width:250px; text-align: center;">Supplier</th>
                    <th style="width:150px; text-align: center;">Harga Beli</th>
                    <th style="width:150px; text-align: center;">Harga Jual</th>                    
                    <th style="width:150px; text-align: center;">Harga Khusus</th>         
                    <th style="width:150px; text-align: center;">Harga Beli Tertinggi</th>                   
                    <th style="width:150px; text-align: center;">Tanggal Beli Tertinggi</th>                
                    <th style="width:150px; text-align: center;">Harga Beli Terakhir</th>
                    <th style="width:150px; text-align: center;">Tanggal Beli Terakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td style="text-align:center;">{{ $no }}</td>
                        <td style="text-align:center;">{{ $view_data['kode'] }} </td>
                        <td >{{ $view_data['nama'] ?? 'Belum ditentukan' }} </td>
                        <td >{{ $listdata['satuan'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }} </td>
                        <td >{{ $listdata['kategori'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>
                        <td >{{ $listdata['merk'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>
                        <td >{{ $listdata['supplier'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}</td>
                        <td >{{ $view_data['harga_beli'] ?? 0 }} </td>
                        <td >{{ $view_data['harga_jual1'] ?? 0 }} </td>
                        <td >{{ $view_data['harga_jual2'] ?? 0 }} </td>
                        <td  style="text-align:right;">{{ $listdata['harga_beli'][$view_data['id']] ?? 0}}</td>
                        <td  style="text-align:right;">{{ $listdata['tanggal_beli'][$view_data['id']] ?? 'Belum ditentukan' }}</td>
                        <td  style="text-align:right;">{{ $listdata['harga_beli_terakhir'][$view_data['id']] ?? 0}}</td>
                        <td  style="text-align:right;">{{ $listdata['tanggal_beli_terakhir'][$view_data['id']] ?? 'Belum ditentukan' }}</td>
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