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
                    <th style="width:250px; text-align: center;">Nama Supplier</th>
                    <th style="width:150px; text-align: center;">Jumlah Transaksi</th>
                    <th style="width:150px; text-align: center;">Total Hutang</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{ $no }}</td>
                        <td class="strtable">{{ $view_data['supplier_nama'] ?? 'Belum ditentukan' }}</td>
                        <td class="strtable" style="text-align:center;">{{ $view_data['jumlah_pembelian'] ?? 0 }}</td>
                        <td class="strtable" style="text-align:right;">{{ $view_data['total_sisa'] ?? 0 }}</td>
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