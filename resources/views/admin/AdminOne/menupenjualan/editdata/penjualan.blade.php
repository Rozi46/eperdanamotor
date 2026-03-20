@extends('admin.AdminOne.layout.assets')
@section('title', 'Penjualan Barang')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Penjualan Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypenjualanbarang'] == 'Yes')                                        
                                            <a href="/admin/historypenjualanbarang"><button type="button" class="btn btn-success" btn="history_data">History Penjualan</button></a>
                                        @endif

                                        @if($level_user['inputpenjualanbarang'] == 'Yes')
                                            <?php if($results['results']['counttransaksi'] == 0  OR $results['results']['detail']['status_transaksi'] == 'Proses'){?>
                                                <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>  
                                                    <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button> 
                                                    <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Penjulaan</button>
                                                <?php } elseif ($res_user['level'] == 'LV5677001') { ?>
                                                    <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button> 
                                                    <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Penjualan</button>
                                                <?php } else { ?>
                                                    <!-- <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>  -->
                                                <?php } ?> 
                                            <?php } else { ?>
                                                    <!-- <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>  -->
                                            <?php } ?> 
                                        @endif  

                                        @if($level_user['historypenjualanbarang'] == 'Yes')                                            
                                            <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>
                                                <a href="printsalesorder?d={{$results['results']['detail']['nomor']}}" target="_blank"><button type="button" class="btn btn-secondary" name="btn_print" btn="print_data"><i class="fa fa-print"></i> Print Sales Order</button> </a> 
                                                <?php if($res_user['level'] == 'LV5677001'){?> 
                                                    <button type="button" class="btn btn-danger" name="del_data" btn="del_data"><i class="fa fa-trash-o"></i> Hapus Penjualan</button>  
                                                <?php } ?>                                                 
                                            <?php }?>                                            
                                        @endif     
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/saveppenjualan">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{ $results['results']['detail']['nomor'] ?? '' }}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_data_perusahaan" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_customer" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang" value="" readonly="true" style="display:none;" />

                                        <div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Penjualan Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="full_name" placeholder="Full Name" value="" readonly="true">
                                                </div>
                                            </div>
										</div>										
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Penjualan</label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Penjualan" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Penjualan</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Penjualan" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="customer" class="col-sm-4 col-form-label">Customer</label>
                                                <div class="col-sm-8 input">
                                                    <!-- @if($level_user['newsupplier'] == 'Yes')
                                                        
                                                            <a href="/admin/newsupplier?ac=menupenjualanbarang" title="Tambah Customer">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fa fa-plus"></i></div>
                                                                </div>
                                                            </a>
                                                        
                                                    @endif -->
                                                    <input type="text" name="customer" placeholder="Customer" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang" class="col-sm-6 col-form-label">Gudang</label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="gudang" placeholder="Pilih Gudang" value="" readonly="true" />
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="type_harga" class="col-sm-4 col-form-label">Type Harga</label>
                                                <div class="col-sm-8 input">                                                    
                                                    <input type="text" name="type_harga" placeholder="Type harga" value="" readonly="true" />
                                                    <!-- <select name="type_harga" placeholder="Type Harga"  value="" disabled="true" >
                                                            <option value="Harga Normal" selected="true">Harga Normal</option>	
                                                            <option value="Harga Khusus">Harga Khusus</option>
                                                    </select> -->
                                                </div>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="mekanik" class="col-sm-2 col-form-label">Mekanik</label>
                                                <div class="col-sm-10 input">
                                                    <select id="nama_mekanik" name="nama_mekanik" placeholder="Mekanik" multiple>
                                                        @foreach ($list_mekanik as $view_data)
                                                            <option value="{{$view_data['code_data']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>                                               
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="" />
                                                </div>                                                
											</div>
										</div>
									</div> 
								</form>
                            </div>
                        </div>
						<div class="col-md-12 bg_page_main"> 
                                    <div class="col-md-12 data_page" line="input_cari_data">
                                        <div class="row bg_data_page form_page content">
                                            <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px;">
                                                <div class="row bg_data_page form_page content bg_form_group">
                                                    <div class="col-md-12 col_act_page_main text-right">
                                                        <input type="text" class="form_group search" name="data_produk" id="data_produk" placeholder="Scan atau cari data barang" value="" style="padding:10px 5px;"/>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>

                            <div class="col-md-12 data_page view">
                                <div class="row bg_data_page" style="padding-left: 5px;padding-right: 5px;padding-bottom: 5px;">
                                    <div class="table_data transaksi">
                                        <table class="table_view table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px; text-align: center;">No</th>
                                                    <th style="min-width:200px; text-align: center;">Nama Barang</th>
                                                    <th style="min-width:75px; text-align: center;">Satuan Barang</th>
                                                    <th style="width:150px; text-align: center;">Harga</th>
                                                    <th style="width:100px; text-align: center;">Qty</th>
                                                    <th style="width:150px; text-align: center;">Diskon</th>
                                                    <th style="min-width:100px; text-align: center;">Netto</th>
                                                    <th style="min-width:100px; text-align: center;">Total Harga</th>
                                                    <?php if($results['results']['detail']['status_transaksi'] != 'Finish' && $results['results']['counttransaksi'] == 0){?>
                                                        <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>
                                                            <th style="width:25px; text-align: center;"></th>
                                                        <?php } elseif ($res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa') {?> 
                                                            <th style="width:25px; text-align: center;"></th>
                                                        <?php } ?> 
                                                    <?php } ?> 
                                                </tr>
                                            </thead>
											<tbody line="list_produk_transakasi">
                                                <tr>
                                                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" >
                                                        <i class="fa fa-shopping-bag"></i>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot line="summary_transaksi"></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
				</div>
            </div>


			@section('script')
				<script type="text/javascript">
                    $(document).ready(function(){ 
                        @if($level_user['inputpenjualanbarang'] == 'No')
                            let form = $('form[name="form_data"]');
                            form.find('input, select').prop('disabled', true).trigger('change');
                            $('button[name="btn_save_data"], button[name="btn_cancel"], button[name="del_data"]').remove();
                        @endif

                        $('#nama_mekanik').select2({
                            placeholder: 'Pilih Mekanik',
                            allowClear: true,
                            width: '100%'
                        });
                        
                        <?php if($results['results']['counttransaksi'] != 0  && $results['results']['detail']['status_transaksi'] == 'Proses'){?>                      
                            $('input[name="keterangan"]').prop({disabled:true}); 
                        <?php } ?>

                        <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>
                            $('input[name="data_produk"]').remove();                     
                            $('input[name="keterangan"]').prop({disabled:true});
                        <?php } ?>                                      
						
						<?php if($results['results']['detail']['status_transaksi'] == 'Proses' && $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'){?>
                            $('input[name="data_produk"]').prop({readonly:false}).focus().val('');
                        <?php }elseif($results['results']['detail']['kode_user'] == $res_user['id']){ ?>
                        <?php }else{ ?>
                            $('input[name="data_produk"]').remove();
                            $('input[name="keterangan"]').prop({disabled:true});

						<?php } ?>
                        
                        $('select[name="data_perusahaan"]').prop({disabled:true});
                        $('input[name="code_transaksi"]').prop({disabled:true});
                        $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                        $('input[name="gudang"]').prop({disabled:true});   
                        $('input[name="data_produk"]').prop({readonly:false}).focus();

                        
						$('input[name="full_name"]').val('{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum Ditentukan' }}');    
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? 'Belum Ditentukan' }}');
                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? 'Belum Ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? '' }}');
                        $('input[name="in_data_perusahaan"]').val('{{ $results['results']['detail']['kode_kantor'] ?? '' }}');    
                        $('input[name="in_customer"]').val('{{ $results['results']['detail']['kode_customer'] ?? '' }}');
                        $('input[name="customer"]').val('{{ $results['results']['detail_customer']['nama'] ?? 'Belum Ditentukan' }}');    
						$('input[name="in_gudang"]').val('{{ $results['results']['detail']['kode_gudang'] ?? '' }}');
						$('input[name="gudang"]').val('{{ $results['results']['detail_gudang']['nama'] ?? 'Belum Ditentukan' }}');
						$('input[name="type_harga"]').val('{{ $results['results']['type_harga'] ?? 'Belum Ditentukan' }}');
                        $('select[name="nama_mekanik"]').val(@json( array_keys($results['results']['detail_mekanik'] ?? [] ))).trigger('change');
						$('input[name="keterangan"]').val('{{ $results['results']['detail']['ket'] ?? 'Belum Ditentukan' }}');

                        
                        var perusahaan = $('input[name="in_data_perusahaan"]').val();
                        $('[name="data_produk"]').autocomplete({
                            minLength:1,
                            source:"/admin/listbarangtransaksi?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_perusahaan="+perusahaan,
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    orderproduk(val.item.code_data);
                                }
                            }
                        });
                        
                        $('input[name="customer"]').keyup(function(){
                            var supplier = $('input[name="customer"]').val();
                            if(supplier == ''){
                                $('input[name="in_customer"]').val('null');
                            }
                        });

                        var perusahaan = $('input[name="in_data_perusahaan"]').val();
                        $('[name="scustomer"]').autocomplete({
                            minLength:1,
                            source:"/admin/getopcusotmer?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_perusahaan="+perusahaan,
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    $('input[name="in_customer"]').val(val.item.code_data);
                                }
                            }
                        });
                        
                        $('.bg_act_page_main button').prop({disabled:true});

						$('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
					
						$.get("/admin/listprodpenjualan",{code_data:'{{$results['results']['detail']['nomor']}}',focus_line:'{{$request['fc']}}'},function(listproduk){
							$('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
						});

                        $('[name="btn_cancel"]').click(function(){
                            if($('[name="btn_cancel"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk membatalkan penjualan barang {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        window.location.href = "/admin/deletepenjualan?d={{$results['results']['detail']['code_data']}}&tipe_data=penjualan";
                                    }  
                                });
                            }
                        });

                        $('[name="del_data"]').click(function(){
                            if($('[name="del_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus penjualan barang {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        window.location.href = "/admin/deletepenjualan?d={{$results['results']['detail']['code_data']}}&tipe_data=penjualan";
                                    }  
                                });
                            }
                        });
                            
                        $('[btn="save_data"]').click(function(){
                            if($('[btn="save_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan dan selesaikan penjualan barang {{$results['results']['detail']['nomor']}}. Setelah simpan dan selesai maka data tidak bisa diubah kembali.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        var customer = $('input[name="in_customer"]').val();
                                        var ket = $('input[name="keterangan"]').val();
                                        var codeMekanik = $('select[name="nama_mekanik"]').val();
                                        window.location.href = "/admin/updatepenjualan?d={{$results['results']['detail']['nomor']}}&customer="+customer+"&tipe_data=penjualan&code_mekanik="+codeMekanik+"&ket="+encodeURIComponent(ket);   
                                    }
                                });
                            }
                        });

                    });

                    function orderproduk(produk){
                        // $('.bg_act_page_main button').prop({disabled:true});
                        $('input[name="data_produk"]').prop({disabled:true});

                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                      
                        var code_data = $('input[name="code_data"]').val();
                        var code_transaksi = $('input[name="in_code_transaksi"]').val();
                        var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                        var code_customer = $('input[name="in_code_customer"]').val();
                        var code_gudang = $('input[name="in_gudang"]').val();
                        var type_harga = $('input[name="type_harga"]').val();
                        var keterangan = $('input[name="keterangan"]').val();
                        $.ajax({
                            type: "POST",
                            url: "/admin/saveprodpenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                            data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&code_customer="+code_customer+"&code_gudang="+code_gudang+"&type_harga="+type_harga+"&code_produk="+produk+"&keterangan="+keterangan+"&qty=1",
                            cache: false,
                            success: function(data){
                                $.get("/admin/listprodpenjualan",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                });
                                if(data.status_message == 'failed'){
                                    if(data.note.code_transaksi != ''){
                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Penjualan sudah terdaftar.</div>');
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        window.location.reload();
                                    }else{
                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        window.location.reload();
                                    }
                                }
                            }
                        });
                    }

                </script>
            @endsection
@endsection