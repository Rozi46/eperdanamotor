<?php $no = 0;?> @forelse($results['results']['list_produk_group'] as $view_data) <?php $no++ ;?>
    <?php  
        $id = $view_data['id'];
        $id = str_replace('-','',$id);
        $qty_pembelian = $view_data['jumlah_beli'];
        $qty_terima = $view_data['jumlah_terima'];
    ?>
    <tr>
        <td style="text-align:center;" id="hg_td">{{$no}}</td>
        <td style="text-align:left;">{{$results['results']['detail_produk'][$id]['nama']}}</td>
        <td style="text-align:center;">
            <?php echo number_format($qty_pembelian,0,"",".") ?> 
            <?php echo $results['results']['satuan_produk'][$id]['nama'] ?>
            <?php $kode_satuan['$id'] = $results['results']['satuan_produk'][$id]['nama'] ?>
        </td>
        <?php if($results['results']['detail_pembelian']['status_transaksi'] == 'Finish'){?>           
            <td style="text-align:center;"><?php echo number_format($qty_terima,0,"",".") ?></td>    
        <?php } else { ?>       
            <td style="text-align:center;"><input type="text" name="new_qty_{{$id}}" value="<?php echo number_format($qty_terima,0,"",".") ?>" style="width: 95px; text-align:center;" onKeyPress="return goodchars(event,'0123456789',this)"/></td> 
        <?php }?>

    </tr>

    <script type="text/javascript">
        $(document).ready(function(){
            $('.bg_act_page_main button').prop({disabled:false});

            $('input[name="new_qty_{{$id}}"]').change(function(){
                var code_data = $('input[name="code_data"]').val();
                var code_transaksi = $('input[name="in_code_transaksi"]').val();
                var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                var no_do = $('input[name="no_do"]').val();
                var no_pembelian = $('input[name="in_no_pembelian"]').val();
                var keterangan = $('input[name="keterangan"]').val();
                var kode_satuan = $('input[name="new_qty_{{$id}}"]').val();
                var qty_up = $('input[name="new_qty_{{$id}}"]').val();
                
                if(qty_up > <?php echo $qty_pembelian;?>){
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Qty yang diterima melebih qty pembelian. Jika barang melebihi qty pembelian silakan update data pembelian terlebih dahulu.</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    $('input[name="new_qty_{{$id}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                }else if(tgl_transaksi == ''){
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Bidang tanggal penerimaan harus diisi.</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    $('input[name="new_qty_{{$id}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                }else if(code_transaksi == ''){
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Bidang nomor penerimaan harus diisi.</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    $('input[name="new_qty_{{$id}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                }else if(no_do == ''){
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Bidang nomor delivery order (DO) harus diisi.</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    $('input[name="new_qty_{{$id}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                }else if(no_pembelian == ''){
                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Bidang nomor pembelian harus diisi.</div>');
                    $('button[btn-action="aciton-confirmasi"]').remove();
                    $('input[name="new_qty_{{$id}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                }else{                  
                    loadingpage(2000);
                    $.ajax({
                        type: "POST",
                        url: "/admin/savepenerimaan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                        data:"id={{$view_data['id']}}&code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&code_pembelian="+code_data+"&keterangan="+encodeURIComponent(keterangan)+"&code_produk={{$view_data['kode_barang']}}&qty_pembelian=<?php echo $qty_pembelian;?>&kode_satuan="+kode_satuan+"&qty="+qty_up,
                        cache: false,
                        success: function(data){
                            loadingpage(0);
                            if(data.status_message == 'failed'){
                                if(data.note.code_transaksi != '' && data.note.code_transaksi != undefined){
                                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Penerimaan sudah terdaftar.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('input[name="new_qty_{{$view_data['kode_barang']}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                                }else{
                                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('input[name="new_qty_{{$view_data['kode_barang']}}"]').val('<?php echo number_format($qty_terima,0,"","") ?>');
                                }
                            }else{
                                $('div[data-model="listproduk"]').modal('hide');
                                $('select[name="data_perusahaan"]').prop({disabled:true});
                                $('select[name="data_cabang"]').prop({disabled:true});
                                $('input[name="code_transaksi"]').prop({disabled:true});
                                $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                                $('select[name="supplier"]').prop({disabled:true});
                                $('select[name="gudang"]').prop({disabled:true});
                                $('input[name="no_pembelian"]').prop({disabled:true});
                                $('input[name="no_do"]').prop({disabled:true});
                                $('.bg_act_page_main button').prop({disabled:true});
                                $('[line="list_produk_transakasi"] button').prop({disabled:true});
                                $('[line="list_produk_transakasi"] input').prop({disabled:true});loadingpage(2000);
                                window.location.reload();
                            }
                        }
                    });
                }
                
            });
        });
    </script>
@empty
    <tr>
        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 250px; font-size: 14px;" colspan="20" >
            <i class="fa fa-shopping-bag"></i>
        </td>
    </tr>
    <script>
        $(document).ready(function(){
            $('.bg_act_page_main button').prop({disabled:true});
            $('[name="btn_cancel"]').prop({disabled:false});
            $('[onclick="BackPage()"]').prop({disabled:false});
        });
    </script>
@endforelse

<?php if($no > 0){ for ($i=0; $i <= 0; $i++) { ?>
    <tr>
        <td class="blank" style="text-align:center;"></td>
        <td class="blank" style="text-align:center;"></td>
        <td class="blank" style="text-align:center;"></td>
        <td class="blank" style="text-align:center;"></td>
    </tr>
<?php } } ?>
<script type="text/javascript">
    $(document).ready(function(){
        var hg_td = $('#hg_td').height();
        $('.blank').css({"height":"40px","padding":"18px"});
    });
</script>