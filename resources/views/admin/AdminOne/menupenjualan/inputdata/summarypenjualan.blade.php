<?php if(count($results['results']['list_produk']) > 0){?>
    <?php if($results['results']['qty_penjualan'] == $results['results']['qty_kirim']){?>
        <?php
            $mata_uang = 'Rp';
            $diskon = number_format($results['results']['detail']['diskon_persen'],2,",",".");
            
        ?>
        <tr>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Total :</td>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['total'],2,",",".") ?></td>
        </tr>
        <tr>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon :</td>
            <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo $diskon; ?></td>
        </tr>
        <tr>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
            <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"><?php echo $mata_uang;?><?php echo number_format($results['results']['detail']['grand_total'],2,",",".") ?></td>
        </tr>
        <!-- <tr>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
            <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['sub_total'],2,",",".") ?></td>
        </tr>
        <tr>
            <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
            <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['ppn'],2,",",".") ?></td>
        </tr> -->
    <?php }else{ ?> 
        <?php if($results['results']['detail']['status_transaksi'] != 'Finish' && $results['results']['qty_kirim'] == 0){?>
            <?php
                $mata_uang = 'Rp';
            ?>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Total :</td>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['total'],2,",",".") ?></td>
            </tr>
            <?php if($res_user['id'] != 'bd050931-d837-11eb-8038-204747ab6caa'){?>
                <tr>
                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon  :</td>
                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3" line="type_disc_po">
                        <select name="type_disc_po" style="width:50px;" <?php if($results['results']['qty_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] && $res_user['id'] != 'bd050931-d837-11eb-8038-204747ab6caa'){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?> <?php if($results['results']['counttransaksi'] != 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>>
                            <option value="Persen">(%)</option>
                            <option value="Jumlah">(<?php echo $mata_uang;?>)</option>
                            <!-- <option value="Tidak Aktif">Tidak Aktif</option> -->
                        </select>

                        <input type="text" name="up_disc_po" value="<?php echo number_format($results['results']['detail']['diskon_persen'],2,",",".") ?>" onKeyPress="return goodchars(event,'0123456789,',this)" style="width: 50px; text-align:right; font-weight: 600;" <?php if($results['results']['qty_kirim'] > 0){?>disabled="true"<?php } ?> <?php if($results['results']['detail']['kode_user'] != null && $results['results']['detail']['kode_user'] != $res_user['id'] && $res_user['id'] != 'bd050931-d837-11eb-8038-204747ab6caa'){?>disabled="true"<?php } ?> <?php if($results['results']['counttransaksi'] != 0){?>readonly="false"<?php } ?> <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>disabled="true"<?php } ?>/>
                    </td>
                </tr>
            <?php }else{ ?>
                <tr>
                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon  :</td>
                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3" line="type_disc_po">
                        <select name="type_disc_po" style="width:50px;" <?php if($results['results']['counttransaksi'] != 0){?>disabled="true"<?php } ?>>
                            <option value="Persen">(%)</option>
                            <option value="Jumlah">(<?php echo $mata_uang;?>)</option>
                            <!-- <option value="Tidak Aktif">Tidak Aktif</option> -->
                        </select>

                        <input type="text" name="up_disc_po" value="<?php echo number_format($results['results']['detail']['diskon_persen'],2,",",".") ?>" onKeyPress="return goodchars(event,'0123456789,',this)" style="width: 50px; text-align:right; font-weight: 600;" <?php if($results['results']['counttransaksi'] != 0){?>readonly="true"<?php } ?>/>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"><?php echo $mata_uang;?><?php echo number_format($results['results']['detail']['grand_total'],2,",",".") ?></td>
            </tr>
            <!-- <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600; min-width:200px;" colspan="3"><?php echo number_format($results['results']['detail']['sub_total'],2,",",".") ?></td>
            </tr>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['ppn'],2,",",".") ?></td>
            </tr> -->
        <?php }else{ ?>
            <?php
                // if($results['results']['detail']['kurs_harga'] == 'Dolar'){
                //     $mata_uang = 'USD';
                // }else{
                    $mata_uang = 'Rp';
                // }
                // if($results['results']['detail']['tipe_diskon'] == 'Persen'){
                //     $diskon = number_format($results['results']['detail']['diskon_faktur'],2,",",".").'%';
                // }else{
                    $diskon = number_format($results['results']['detail']['diskon_persen'],2,",",".");
                // }
            ?>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Total :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['total'],2,",",".") ?></td>
            </tr>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo $diskon; ?></td>
            </tr>
            <!-- <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Biaya Kirim :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['biaya_kirim'],2,",",".") ?></td>
            </tr> -->
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"><?php echo $mata_uang;?><?php echo number_format($results['results']['detail']['grand_total'],2,",",".") ?></td>
            </tr>
            <!-- <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['sub_total'],2,",",".") ?></td>
            </tr>
            <tr>
                <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
                <td class="strtable" style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3"><?php echo number_format($results['results']['detail']['ppn'],2,",",".") ?></td>
            </tr> -->
        <?php } ?>
    <?php } ?>
<?php }else{ ?>
    <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Total :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
    </tr>
    <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
    </tr>
    <!-- <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Biaya Kirim :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
    </tr> -->
    <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3">Rp0,00</td>
    </tr>
    <!-- <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
    </tr>
    <tr>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
        <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
    </tr> -->
<?php } ?>

<script type="text/javascript">
    $(document).ready(function(){

            $('[line="list_produk_transakasi"] input').prop({disabled:false});
            $('[line="list_produk_transakasi"] select').prop({disabled:false});
            $('[line="list_produk_transakasi"] button').prop({disabled:false});
            $('input[type="checkbox"]').prop({disabled:false});
       
        var hg_td = $('#hg_td').height();
        $('.blank_list').css({"height":""+hg_td+"","padding":"18px"});

     

        var type_disc_po = $('select[name="type_disc_po"]').val();
        if(type_disc_po == 'Jumlah'){
            $('[line="type_disc_po"]').css({'width':'165px'});
            $('input[name="up_disc_po"]').css({'width':'105px'});
        }else{
            $('[line="type_disc_po"]').css({'width':'auto'});
            $('input[name="up_disc_po"]').css({'width':'50px'});
        }

        $('select[name="type_disc_po"]').change(function(){
            var type_disc_po = $('select[name="type_disc_po"]').val();
            if(type_disc_po == 'Tidak Aktif'){
                // savetotalpo();
                $('[line="type_disc_po"]').css({'width':'auto'});
                $('input[name="up_disc_po"]').css({'width':'50px'});
                $('input[name="up_disc_po"]').val('0,00').prop("readonly", true); 
                savetotalpo();  
            }else{
                if(type_disc_po == 'Jumlah'){
                    $('[line="type_disc_po"]').css({'width':'165px'});
                    $('input[name="up_disc_po"]').css({'width':'105px'});
                    // $('input[name="up_disc_po"]').val('0,00').prop("readonly", false); 
                    $('input[name="up_disc_po"]').focus().select().val('<?php echo number_format($results['results']['detail']['diskon_harga'],2,",",".") ?>');
                }else{
                    $('[line="type_disc_po"]').css({'width':'auto'});
                    $('input[name="up_disc_po"]').css({'width':'50px'});
                    // $('input[name="up_disc_po"]').val('0,00').prop("readonly", false); 
                    $('input[name="up_disc_po"]').focus().select().val('<?php echo number_format($results['results']['detail']['diskon_persen'],2,",",".") ?>');
                }
            }
        });

        $('input[name="up_disc_po"]').change(function(){
            savetotalpo();
        });
    });

    function savetotalpo(){
        var type_disc_po = $('select[name="type_disc_po"]').val();
        var up_disc_po = $('input[name="up_disc_po"]').val();

        if(type_disc_po == 'Tidak Aktif'){
            var up_disc_po = '0,00';
            $('input[name="up_disc_po"]').val(up_disc_po);
        }

        var up_disc_po = up_disc_po.replace(".", "");

        if(up_disc_po == ''){
            var up_disc_po = '0';
        }
            
        $('.bg_act_page_main button').prop({disabled:true});
        $('input[name="data_produk"]').prop({disabled:true});
        $('[line="list_produk_transakasi"] button').prop({disabled:true});
        $('[line="list_produk_transakasi"] input').prop({disabled:true});
        $('[line="list_produk_transakasi"] select').prop({disabled:true});
        $('input[type="checkbox"]').prop({disabled:true});
                    
        $.ajax({
            type: "POST",
            url: "/admin/upsummarypenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
            data:"code_data=<?php echo $results['results']['detail']['code_data'];?>&nilai_diskon="+up_disc_po+"&tipe_diskon="+type_disc_po,
            cache: false,
            success: function(data){
                if(data.status_message == 'success'){
                    window.location.href = "/admin/viewpenjualan?d={{$results['results']['detail']['nomor']}}&fc=summary";
                }else{
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    window.location.href = "/admin/viewpenjualan?d={{$results['results']['detail']['nomor']}}&fc=summary";
                }
            }
        }); 
    }
</script>