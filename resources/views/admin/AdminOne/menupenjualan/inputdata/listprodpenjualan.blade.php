<?php $no = 0;?> @forelse($results['results']['list_produk'] as $view_data) <?php $no++ ;?>
    <?php
        $id = $view_data['id'];
        $id = str_replace('-','',$id);
        $mata_uang = 'Rp';
        $diskon = number_format($view_data['diskon_harga'],2,",",".");
    ?>
    <?php if($results['results']['counttransaksi'] == 0  && $results['results']['detail']['status_transaksi'] == 'Proses'){?>
        <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>  
            <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
                <td style="text-align:center;" id="hg_td">{{$no}}</td>
                <td style="text-align:left;">{{$results['results']['detail_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                <td style="text-align:center;">
                    <select name="new_satuan_{{$view_data['id']}}" style="width:60%; padding-top: 3px;" <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>>
                    
                        <option value="{{$view_data['kode_satuan']}}">{{$results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</option>

                        <?php if($view_data['kode_satuan'] != $results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']){?><option value="{{$results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']}}">{{$results['results']['satuan_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</option><?php }?>

                        @foreach ($results['results']['satuan_barang_pecahan'][$view_data['kode_barang']] as $view_data_satuan)
                            <?php if($view_data['kode_satuan'] != $view_data_satuan['id']){?><option value="{{$view_data_satuan['id']}}">{{$view_data_satuan['nama'] ?? 'Belum ditentukan' }}</option><?php }?>                                
                        @endforeach          
                    </select> 
                </td>

                <td style="text-align:center;">
                    <input type="text" name="new_price_{{$view_data['id']}}" value="<?php echo number_format($view_data['harga'],2,",","") ?>" style="width: 70%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id']){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:center;">
                    <input type="text" name="new_qty_{{$view_data['id']}}" value="<?php echo number_format($view_data['jumlah_jual'],0,"",".") ?>" style="width: 50px; text-align:center;" onKeyPress="return goodchars(event,'0123456789',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:right;">
                    <input type="text" name="new_disc_{{$view_data['id']}}" value="<?php echo number_format($view_data['diskon_persen'],2,",","") ?>" style="width:45%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>

                    <input type="text" name="new_disc2_{{$view_data['id']}}" value="<?php echo number_format($view_data['diskon_persen2'],2,",","") ?>" style="width:45%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['diskon_persen'] == '0'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:right;" line="netto_{{$view_data['code_data']}}"><?php echo number_format($view_data['harga_netto'],2,",",".") ?></td>

                <td style="text-align:right;" line="total_harga_{{$view_data['code_data']}}"><?php echo number_format($view_data['total_harga'],2,",",".") ?></td>

                <td style="text-align:center;"><button type="button" class="btn btn-danger btn_del" btn="del_produk_{{$view_data['id']}}" title="Hapus Data"<?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>  ><i class="fa fa-trash-o"></i></button></td>
            </tr>
        <?php } elseif ($res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa') { ?>
            <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
                <td style="text-align:center;" id="hg_td">{{$no}}</td>
                <td style="text-align:left;">{{$results['results']['detail_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                <td style="text-align:center;">
                    <select name="new_satuan_{{$view_data['id']}}" style="width:60%; padding-top: 3px;" <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>>
                    
                        <option value="{{$view_data['kode_satuan']}}">{{$results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</option>

                        <?php if($view_data['kode_satuan'] != $results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']){?><option value="{{$results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']}}">{{$results['results']['satuan_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</option><?php }?>

                        @foreach ($results['results']['satuan_barang_pecahan'][$view_data['kode_barang']] as $view_data_satuan)
                            <?php if($view_data['kode_satuan'] != $view_data_satuan['id']){?><option value="{{$view_data_satuan['id']}}">{{$view_data_satuan['nama'] ?? 'Belum ditentukan' }}</option><?php }?>                                
                        @endforeach          
                    </select> 
                </td>

                <td style="text-align:center;">
                    <input type="text" name="new_price_{{$view_data['id']}}" value="<?php echo number_format($view_data['harga'],2,",","") ?>" style="width: 70%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id']){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:center;">
                    <input type="text" name="new_qty_{{$view_data['id']}}" value="<?php echo number_format($view_data['jumlah_jual'],0,"",".") ?>" style="width: 50px; text-align:center;" onKeyPress="return goodchars(event,'0123456789',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:right;">
                    <input type="text" name="new_disc_{{$view_data['id']}}" value="<?php echo number_format($view_data['diskon_persen'],2,",","") ?>" style="width:45%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Dibatalkan'){?>disabled="true"<?php } ?>/>

                    <input type="text" name="new_disc2_{{$view_data['id']}}" value="<?php echo number_format($view_data['diskon_persen2'],2,",","") ?>" style="width:45%; text-align:right;" onKeyPress="return goodchars(event,'0123456789,',this)" <?php if($view_data['jumlah_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] ){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['diskon_persen'] == '0'){?>disabled="true"<?php } ?>/>
                </td>

                <td style="text-align:right;" line="netto_{{$view_data['code_data']}}"><?php echo number_format($view_data['harga_netto'],2,",",".") ?></td>

                <td style="text-align:right;" line="total_harga_{{$view_data['code_data']}}"><?php echo number_format($view_data['total_harga'],2,",",".") ?></td>

                <td style="text-align:center;"><button type="button" class="btn btn-danger btn_del" btn="del_produk_{{$view_data['id']}}" title="Hapus Data"<?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>  ><i class="fa fa-trash-o"></i></button></td>
            </tr>
        <?php } else { ?>
            <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
                <td style="text-align:center;" id="hg_td">{{$no}}</td>
                <td style="text-align:left;">{{$results['results']['detail_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
                <td style="text-align:center;" line="new_satuan_{{$view_data['id']}}"><?php echo ($results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama'])?></td>
                <td style="text-align:center;" line="new_price_{{$view_data['id']}}"><?php echo number_format($view_data['harga'],2,",",".") ?></td>
                <td style="text-align:center;" line="new_qty_{{$view_data['id']}}"><?php echo number_format($view_data['jumlah_jual'],0,"",".") ?></td>
                <td style="text-align:center;" line="new_disc_{{$view_data['id']}}"><?php echo number_format($view_data['diskon_persen'],2,",",".")." + ".number_format($view_data['diskon_persen2'],2,",",".") ?></td>


                <td style="text-align:right;" line="netto_{{$view_data['code_data']}}"><?php echo number_format($view_data['harga_netto'],2,",",".") ?></td>

                <td style="text-align:right;" line="total_harga_{{$view_data['code_data']}}"><?php echo number_format($view_data['total_harga'],2,",",".") ?></td>
            </tr>
        <?php } ?> 
    <?php } else { ?>
        <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
            <td style="text-align:center;" id="hg_td">{{$no}}</td>
            <td style="text-align:left;">{{$results['results']['detail_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
            <td style="text-align:center;" line="new_satuan_{{$view_data['id']}}"><?php echo ($results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama'])?></td>
            <td style="text-align:center;" line="new_price_{{$view_data['id']}}"><?php echo number_format($view_data['harga'],2,",",".") ?></td>
            <td style="text-align:center;" line="new_qty_{{$view_data['id']}}"><?php echo number_format($view_data['jumlah_jual'],0,"",".") ?></td>
            <td style="text-align:center;" line="new_disc_{{$view_data['id']}}"><?php echo number_format($view_data['diskon_persen'],2,",",".")." + ".number_format($view_data['diskon_persen2'],2,",",".") ?></td>


            <td style="text-align:right;" line="netto_{{$view_data['code_data']}}"><?php echo number_format($view_data['harga_netto'],2,",",".") ?></td>

            <td style="text-align:right;" line="total_harga_{{$view_data['code_data']}}"><?php echo number_format($view_data['total_harga'],2,",",".") ?></td>
        </tr>
    <?php } ?> 

    <script>
        $(document).ready(function(){      
            $('.bg_act_page_main button').prop({disabled:false});
            $('input[name="data_produk"]').prop({disabled:false}).focus().val('');

            $('[btn="del_produk_{{$view_data['id']}}"]').click(function(){
                if($('[btn="del_produk_{{$view_data['id']}}"]').click){
                    $('.bg_act_page_main button').prop({disabled:true});
                    $('input[name="data_produk"]').prop({disabled:true});
                    $('[line="data_produk_{{$view_data['code_data']}}"]').remove();
                    $.ajax({
                        type: "GET",
                        url: "/admin/deleteprodpenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                        data:"id={{$view_data['id']}}&code_data={{$view_data['code_data']}}",
                        cache: false,
                        success: function(data){
                            if(data.status_message == 'success'){
                                $('.bg_act_page_main button').prop({disabled:false});
                                $('input[name="data_produk"]').prop({disabled:false}).focus().val('');
                                var menu = $('.list_data_prod_transaksi').length;
                                if ($('.list_data_prod_transaksi').length == 0) {
                                    $('.bg_act_page_main button').prop({disabled:true});
                                    $('[name="btn_cancel"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('[btn="history_data"]').prop({disabled:false});
                                    $('[onclick="BackPage()"]').prop({disabled:false});
                                }
                                $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('[btn="cari_produk"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                    // $(".ios").iosCheckbox();
                                });
                                $.get("/admin/summarypenjualan",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
                                    $('[line="summary_transaksi"]').html(listsummary);
                                });
                            }else{
                                $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal dihapus</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('[btn="cari_produk"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                    // $(".ios").iosCheckbox();
                                });
                            }
                        }
                    });
                }
            });	
            
            $('select[name="new_satuan_{{$view_data['id']}}"]').change(function(){
                listsatuanharga_<?php echo $id;?>();  
            });

            $('input[name="new_price_{{$view_data['id']}}"]').change(function(){
                var price_up = $('input[name="new_price_{{$view_data['id']}}"]');
                var price_up_val = price_up.val();
                var price_up_val = price_up_val.replace(",", ".");
                if(price_up_val == ''){
                    price_up.val('<?php echo number_format($view_data['harga'],2,",","") ?>').focus();
                }else{
                    saveprice_<?php echo $id;?>();
                }
            });

            $('input[name="new_qty_{{$view_data['id']}}"]').change(function(){
                var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]');
                if(qty_up.val() != ''){
                    if(qty_up.val() <= 0){
                        qty_up.val('<?php echo number_format($view_data['jumlah_jual'],0,"",".") ?>').focus();
                    }else{
                        saveqty_<?php echo $id;?>();
                    }
                }else{
                    qty_up.val('<?php echo number_format($view_data['jumlah_jual'],0,"",".") ?>').focus();
                }
            });
           
            $('input[name="new_disc_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['id']}}"]').val();
                var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]');
                var disc_up_val = disc_up.val();
                if(disc_up_val != ''){
                    savedisc_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    disc_up.val('<?php echo number_format($view_data['diskon_persen'],2,",","") ?>').focus();
                } 
            });
           
           $('input[name="new_disc2_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]');
                var disc_up2_val = disc_up2.val();
                if(disc_up2_val != ''){
                    savedisc2_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    disc_up2.val('<?php echo number_format($view_data['diskon_persen2'],2,",","") ?>').focus();
                } 
           });
        });
        
        function listsatuanharga_<?php echo $id;?>(){       
            var satuan_harga = $('select[name="new_satuan_{{$view_data['id']}}"]').val();      
            $.ajax({
                type: "GET",
                url: "/admin/listsatuanhargapenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&harga_satuan="+satuan_harga,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('select[name="new_satuan_{{$view_data['id']}}"] option[value="123"]').prop("selected", true); 
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function saveprice_<?php echo $id;?>(){
            var price_up = $('input[name="new_price_{{$view_data['id']}}"]').val();
            var price_up = price_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/uphargapenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&harga="+price_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }
                }
            }); 
        }
        
        function saveqty_<?php echo $id;?>(){
            var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]').val();
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/upqtypenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&qty="+qty_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            $(".ios").iosCheckbox();
                        });
                    }
                }
            }); 
        }
        
        function savedisc_<?php echo $id;?>(){
            var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]').val();
            var disc_up = disc_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&nilai_diskon="+disc_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function savedisc2_<?php echo $id;?>(){
            var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]').val();
            var disc_up2 = disc_up2.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpenjualan2?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&nilai_diskon2="+disc_up2,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpenjualan",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            });  
        }
        
        function hitung_total_<?php echo $id;?>(){
            var price_up = $('input[name="new_price_{{$view_data['id']}}"]').val();
            var price_up = price_up.replace(",", ".");

            var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]').val(); 
            var qty_up = parseInt(qty_up);

            // var total_up = (price_up*qty_up);

            // var tipe_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
            // var disc_up = $('input[name="new_disc_{{$view_data['code_data']}}"]').val();
            // if(tipe_disc_up == 'Persen'){
                // var disc_up = disc_up.replace(",", ".");
                // var nilai_diskon = disc_up/100;
                // var total_up_disc = (total_up*nilai_diskon);
                // var total_up = (total_up-total_up_disc);
            // }else if(tipe_disc_up == 'Jumlah'){
            //     var disc_up = disc_up.replace(",", ".");
            //     var total_up = total_up - disc_up;
            // }else{
            //     var disc_up = disc_up.replace(",", ".");
            //     var total_up = total_up;
            // }
            
            var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]').val();
            var disc_up = disc_up.replace(",", ".");
            var nilai_diskon = disc_up/100;
            var nilai_diskon = (price_up*nilai_diskon);

            var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]').val();
            var disc_up2 = disc_up2.replace(",", ".");
            var nilai_diskon2 = disc_up2/100;
            var nilai_diskon2 = ((price_up-nilai_diskon)*nilai_diskon2);

            var netto_up = (price_up-nilai_diskon-nilai_diskon2);

            // var total_up_disc2 = (total_up*nilai_diskon);
            // var total_up = (total_up-total_up_disc);

            var total_up = (qty_up*netto_up);
            
            var netto_up = netto_up.toFixed(2);
            var netto_up = netto_up.replace(".", ",");
            $('[line="netto_{{$view_data['id']}}"]').html(format_angka(netto_up));

            var total_up = total_up.toFixed(2);
            var total_up = total_up.replace(".", ",");
            $('[line="total_harga_{{$view_data['id']}}"]').html(format_angka(total_up));

            $.get("/admin/summarypenjualan",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
                $('[line="summary_transaksi"]').html(listsummary);
            });
        }

    </script>
@empty
    <tr>
        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" >
            <i class="fa fa-shopping-bag"></i>
        </td>
    </tr>
    <script>
        $(document).ready(function(){
            $('.bg_act_page_main button').prop({disabled:true});
            $('[name="btn_cancel"]').prop({disabled:false});
            $('[btn="history_data"]').prop({disabled:false});
            $('[onclick="BackPage()"]').prop({disabled:false});
        });
    </script>
@endforelse


<?php if($results['results']['detail']['status_transaksi'] != 'Finish' && $results['results']['counttransaksi'] == 0){?>
    <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>
        <?php if($no > 0){ for ($i=0; $i <= 0; $i++) { ?>
            <tr>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td> 
            </tr>  
        <?php } } ?> 
    <?php } elseif ($res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'){ ?>
        <?php if($no > 0){ for ($i=0; $i <= 0; $i++) { ?>
            <tr>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td> 
            </tr>  
        <?php } } ?> 
    <?php } else { ?>
        <?php if($no > 0){ for ($i=0; $i <= 0; $i++) { ?>
            <tr>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
                <td class="blank_list" style="text-align:center;"></td>
            </tr>  
        <?php } } ?> 
    <?php } ?> 
<?php } else { ?>
    <?php if($no > 0){ for ($i=0; $i <= 0; $i++) { ?>
        <tr>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
            <td class="blank_list" style="text-align:center;"></td>
        </tr>          
    <?php } } ?>  
<?php } ?> 

<script type="text/javascript">
    $(document).ready(function(){
        
            $('[line="list_produk_transakasi"] input').prop({disabled:true});
            $('[line="list_produk_transakasi"] select').prop({disabled:true});
            $('[line="list_produk_transakasi"] button').prop({disabled:true});
            $('input[type="checkbox"]').prop({disabled:true});
        
        var hg_td = $('#hg_td').height();
        $('.blank_list').css({"height":""+hg_td+"","padding":"18px"});

        $.get("/admin/summarypenjualan",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
            $('[line="summary_transaksi"]').html(listsummary);
            <?php if($request['focus_line'] == 'summary'){?>
                $("html, body").animate({ scrollTop: $('.page_main').height()}, 600);
            <?php } ?>            
            <?php if($results['results']['detail']['kode_user'] != $res_user['id'] ){?>
                $('[line="list_produk_transakasi"] input').prop({disabled:true});
                $('[line="list_produk_transakasi"] select').prop({disabled:true});
                $('[line="list_produk_transakasi"] button').prop({disabled:true});
                $('[line="summary_transaksi"] input').prop({disabled:true});
                $('[line="summary_transaksi"] select').prop({disabled:true});
                $('[line="summary_transaksi"] button').prop({disabled:true});
                $('input[type="checkbox"]').prop({disabled:true});
            <?php } ?>   
        });
    });
</script>