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
                    <th style="width:170px; text-align: center;">No. Pembelian</th>
                    <th style="width:150px; text-align: center;">Pembelian Oleh</th>
                    <th style="width:150px; text-align: center;">Supplier</th>
                    <th style="width:100px; text-align: center;">Kategori</th>
                    <th style="width:200px; text-align: center;">Nama Barang</th>
                    <th style="width:150px; text-align: center;">Qty Pembelian</th>
                    <th style="width:150px; text-align: center;">Qty Diterima</th>
                    <th style="width:150px; text-align: center;">Qty Belum Diterima</th>
                    <th style="width:100px; text-align: center;">Satuan</th>
                    <th style="width:150px; text-align: center;">Harga</th>
                    <th style="width:150px; text-align: center;">Diskon</th>
                    <th style="width:150px; text-align: center;">PPN</th>
                    <th style="width:150px; text-align: center;">Total</th>
                    <th style="width:200px; text-align: center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                    <?php
                        // if($view_data['tipe_diskon'] == 'Persen'){
                        //     $diskon = number_format($view_data['nilai_diskon'],2,",",".").'%';
                        //     $getdiskon = $view_data['harga_beli'] * ($view_data['nilai_diskon']/100);
                        // }else{
                            $diskon = number_format($view_data['diskon_harga'],2,",",".");
                            $getdiskon = $view_data['diskon_harga'];
                        // }
                        $qtyblterima = $view_data['jumlah_beli'] - $view_data['jumlah_terima'];
                    ?>
                    <tr>
                        <td class="strtable" style="text-align:center;">{{$no}}</td>
                        <?php if($res_user['tipe_user'] == 'Super User'){?>
                            <td>{{$listdata['detail_perusahaan'][$view_data['id']]['kantor']}}</td>
                        <?php } ?>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('d')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('F')}} </td>
                        <td class="strtable" style="text-align:center;">{{Date::parse($view_data['tanggal'])->format('Y')}} </td>
                        <td class="strtable" style="text-align:center;">{{$view_data['nomor']}}</td>
                        <td style="text-align:left;">{{$listdata['user_input'][$view_data['id']]['full_name']}}</td>
                        
                        <?php if($listdata['detail_supplier'][$view_data['id']]['code_data'] != null){?>
                            <td>{{$listdata['detail_supplier'][$view_data['id']]['nama']}}</td>
                        <?php }else{ ?>
                            <td>Belum Ditentukan</td>
                        <?php } ?>
                        
                        <?php if($listdata['kategori_prod'][$view_data['id']] != null){?>
                            <td style="text-align:center;">{{$listdata['kategori_prod'][$view_data['id']]['nama']}}</td>
                        <?php }else{ ?>
                            <td>-</td>
                        <?php } ?>

                        <td style="text-align:left;">{{$listdata['produk'][$view_data['id']]['nama']}}</td>

                        <td class="strtable" style="text-align:center;">{{$view_data['jumlah_beli']}}</td>
                        <td class="strtable" style="text-align:center;">{{$view_data['jumlah_terima']}}</td>
                        <td class="strtable" style="text-align:center;">{{$qtyblterima}}</td>
                        <td style="text-align:center;">{{$listdata['satuan_prod'][$view_data['id']]['nama']}}</td>
                        <td class="strtable" style="text-align:right; font-weight: 500;">{{$view_data['harga']}}</td>
                        <td class="strtable" style="text-align:right; font-weight: 500;">{{$getdiskon}}</td>
                        <td class="strtable" style="text-align:right; font-weight: 500;">{{$view_data['ppn']}}</td>
                        <td class="strtable" style="text-align:right; font-weight: 500;">{{$view_data['total_harga']}}</td>
                        <td style="text-align:left;">{{$listdata['detail_pembelian'][$view_data['id']]['ket']}}</td>

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