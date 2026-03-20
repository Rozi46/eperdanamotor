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
                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="4">Data PPN Tahun {{$listdata['thn_now']}}</td>
                </tr>
                <tr>
                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="4">{{ $listdata['nama_perusahaan']['nama_cabang'] }}</td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <th style="width:40px; text-align: center;">No</th>
                    <th style="width:150px; text-align: center;">Bulan</th>
                    <th style="width:170px; text-align: center;">Pembelian</th>
                    <th style="width:200px; text-align: center;">Penjualan</th>
                </tr>
            </thead>
            <tbody>
                @for ($x = 1; $x <= 12; $x++)                                                           
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$x}}</td>
                        <td class="strtable">{{$listdata['months'.$x]}}</td>
                        <td class="strtable" style="width:200px; text-align: right;">{{$listdata['pembelian'.$x]}}</td>
                        <td class="strtable" style="width:200px; text-align: right;">{{$listdata['penjualan'.$x]}}</td>
                    </tr>
                @endfor 
            </tbody>
        </table>
    </body>
</html>