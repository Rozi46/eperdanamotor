@extends('admin.AdminOne.layout.assets')
@section('title', 'Pembelian Barang')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pembelian Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypembelianbarang'] == 'Yes')                                        
                                            <a href="/admin/historypembelianbarang"><button type="button" class="btn btn-success" btn="history_data">History Pembelian</button></a>
                                        @endif

                                        @if($level_user['inputpembelianbarang'] == 'Yes')
                                            <?php if($results['results']['counttransaksi'] == 0  OR $results['results']['detail']['status_transaksi'] == 'Proses'){?>
                                                <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>  
                                                    <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button> <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Pembelian</button>
                                                <?php } elseif ($res_user['level'] == 'LV5677001') { ?>
                                                    <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button> <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Pembelian</button>
                                                <?php } else { ?>
                                                    <!-- <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>  -->
                                                <?php } ?> 
                                            <?php } else { ?>
                                                    <!-- <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>  -->
                                            <?php } ?> 
                                        @endif  

                                        @if($level_user['historypembelianbarang'] == 'Yes')                                            
                                            <?php if($results['results']['detail']['status_transaksi'] == 'Finish'){?>
                                                <a href="printpurchaseorder?d={{$results['results']['detail']['nomor']}}" target="_blank"><button type="button" class="btn btn-secondary" name="btn_print" btn="print_data"><i class="fa fa-print"></i> Print Purchase Order</button> </a> 
                                                <?php if($res_user['level'] == 'LV5677001'){?> <button type="button" class="btn btn-danger" name="del_data" btn="del_data"><i class="fa fa-trash-o"></i> Hapus Pembelian</button> <?php } ?> 
                                            <?php }?>
                                            
                                        @endif     
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/saveppembelian">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$results['results']['detail']['nomor']}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_data_perusahaan" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_data_cabang" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_supplier" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang" value="" readonly="true" style="display:none;" />

                                        <div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Pembelian Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="full_name" placeholder="Full Name" value="" readonly="true">
                                                </div>
                                            </div>
										</div>										
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Pembelian</label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Pembelian" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Pembelian</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Pembelian" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="supplier" class="col-sm-4 col-form-label">Supplier</label>
                                                <div class="col-sm-8 input">
                                                    <!-- @if($level_user['newsupplier'] == 'Yes')
                                                        
                                                            <a href="/admin/newsupplier?ac=menupembelianbarang" title="Tambah Supplier">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fa fa-plus"></i></div>
                                                                </div>
                                                            </a>
                                                        
                                                    @endif -->
                                                    <input type="text" name="supplier" placeholder="Supplier" value="" readonly="true">
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
                                                <label for="nama_perusahaan" class="col-sm-4 col-form-label">Nama Perusahan</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="nama_perusahaan" placeholder="Pilih Perusahaan" value="" readonly="true" />
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="jenis_ppn" class="col-sm-4 col-form-label">Jenis PPN</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="jenis_ppn" placeholder="Jenis PPN" value="" readonly="true" />
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
                                                    <th style="width:180px; text-align: center;">Diskon <br> % - Rp</th>
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
                        @if($level_user['inputpembelianbarang'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save_data"]').remove();
                            $('button[name="btn_cancel"]').remove();
                            $('button[name="del_data"]').remove();
                        @endif   
                        
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
                        $('select[name="data_cabang"]').prop({disabled:true});
                        $('input[name="code_transaksi"]').prop({disabled:true});
                        $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                        $('input[name="gudang"]').prop({disabled:true});
                        $('input[name="data_produk"]').prop({readonly:false}).focus();

                        $('input[name="full_name"]').val('{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum ditentukan' }}');   
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? 'Belum ditentukan' }}');
                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="code_transaksi"]').val('{{$results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{$results['results']['detail']['nomor'] ?? '' }}');
                        $('input[name="in_data_perusahaan"]').val('{{$results['results']['detail']['kode_kantor'] ?? '' }}');
                        $('input[name="in_data_cabang"]').val('{{$results['results']['detail']['kode_cabang'] ?? ''}}'); 
                        $('input[name="in_supplier"]').val('{{ $results['results']['detail']['kode_supplier'] ?? '' }}');
                        $('input[name="supplier"]').val('{{ $results['results']['detail_supplier']['nama'] ?? 'Belum ditentukan' }}');

						$('input[name="in_gudang"]').val('{{$results['results']['detail']['kode_gudang'] ?? ''}}');
                        $('input[name="gudang"]').val('{{ $results['results']['detail_gudang']['nama'] ?? 'Belum ditentukan' }}');
                        $('input[name="nama_perusahaan"]').val('{{ $results['results']['detail_cabang']['nama_cabang'] ?? 'Belum ditentukan' }}');
						$('input[name="jenis_ppn"]').val('{{$results['results']['detail']['jenis_ppn'] ?? 'Belum ditentukan'}}');
						$('input[name="keterangan"]').val('{{$results['results']['detail']['ket'] ?? 'Belum ditentukan'}}');

                        
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
                        
                        $('input[name="supplier"]').keyup(function(){
                            var supplier = $('input[name="supplier"]').val();
                            if(supplier == ''){
                                $('input[name="in_supplier"]').val('null');
                            }
                        });

                        var perusahaan = $('input[name="in_data_perusahaan"]').val();

                        $('[name="supplier"]').autocomplete({
                            minLength:1,
                            source:"/admin/getopsupplier?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_perusahaan="+perusahaan,
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    $('input[name="in_supplier"]').val(val.item.code_data);
                                }
                            }
                        });
                        
                        $('.bg_act_page_main button').prop({disabled:true});

						$('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
					
						$.get("/admin/listprodpembelian",{code_data:'{{$results['results']['detail']['nomor']}}',focus_line:'{{$request['fc']}}'},function(listproduk){
							$('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
						});

                        $('[name="btn_cancel"]').click(function(){
                            if($('[name="btn_cancel"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk membatalkan pembelian barang {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        window.location.href = "/admin/deletepembelian?d={{$results['results']['detail']['code_data']}}&tipe_data=pembelian";
                                    }  
                                });
                            }
                        });

                        $('[name="del_data"]').click(function(){
                            if($('[name="del_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus pembelian barang {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        window.location.href = "/admin/deletepembelian?d={{$results['results']['detail']['code_data']}}&tipe_data=pembelian";
                                    }  
                                });
                            }
                        });
                            
                        $('[btn="save_data"]').click(function(){
                            if($('[btn="save_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan dan selesaikan pembelian barang {{$results['results']['detail']['nomor']}}. Setelah simpan dan selesai maka data tidak bisa diubah kembali.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        var supplier = $('input[name="in_supplier"]').val();
                                        var ket = $('input[name="keterangan"]').val();
                                        window.location.href = "/admin/updatepembelian?d={{$results['results']['detail']['nomor']}}&supplier="+supplier+"&tipe_data=pembelian&ket="+encodeURIComponent(ket);   
                                    }
                                });
                            }
                        });

                    });

                    // function orderproduk(produk){
                    //     // $('.bg_act_page_main button').prop({disabled:true});
                    //     $('input[name="data_produk"]').prop({disabled:true});

                    //     $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                      
                    //     var code_data = $('input[name="code_data"]').val();
                    //     var code_transaksi = $('input[name="in_code_transaksi"]').val();
                    //     var jenis_ppn = $('input[name="jenis_ppn"]').val();
                    //     var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                    //     var code_supplier = $('input[name="in_supplier"]').val();
                    //     var code_gudang = $('input[name="in_gudang"]').val();
                    //     var code_cabang = $('input[name="in_data_cabang"]').val();
                    //     var keterangan = $('input[name="keterangan"]').val();
                    //     $.ajax({
                    //         type: "POST",
                    //         url: "/admin/saveprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                    //         data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&jenis_ppn="+jenis_ppn+"&code_supplier="+code_supplier+"&code_gudang="+code_gudang+"&code_produk="+produk+"&keterangan="+keterangan+"&code_cabang="+code_cabang+"&qty=1",
                    //         cache: false,
                    //         success: function(data){
                    //             $.get("/admin/listprodpembelian",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listproduk){
                    //                 $('[line="list_produk_transakasi"]').html(listproduk);
                    //                 $('input[name="data_produk"]').prop({disabled:false}).focus();
                    //             });
                    //             if(data.status_message == 'failed'){
                    //                 if(data.note.code_transaksi != ''){
                    //                     $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    //                     $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Pembelian sudah terdaftar.</div>');
                    //                     $('button[btn-action="aciton-confirmasi"]').remove();
                    //                     window.location.reload();
                    //                 }else{
                    //                     $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    //                     $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                    //                     $('button[btn-action="aciton-confirmasi"]').remove();
                    //                     window.location.reload();
                    //                 }
                    //             }
                    //         }
                    //     });
                    // }

                    // function orderproduk(produk) {
                    //     // Nonaktifkan elemen terkait sebelum memulai proses
                    //     $('.bg_act_page_main button, input[name="data_produk"]').prop('disabled', true);

                    //     // Ambil semua nilai input dan simpan dalam objek data
                    //     var data = {
                    //         code_data: $('input[name="code_data"]').val(),
                    //         code_transaksi: $('input[name="in_code_transaksi"]').val(),
                    //         jenis_ppn: $('select[name="jenis_ppn"]').val(),
                    //         tgl_transaksi: $('input[name="in_tgl_transaksi"]').val(),
                    //         code_supplier: $('input[name="in_supplier"]').val(),
                    //         code_gudang: $('input[name="in_gudang"]').val(),
                    //         code_produk: produk,
                    //         keterangan: $('input[name="keterangan"]').val(),
                    //         code_cabang: $('input[name="in_cabang"]').val(),
                    //         qty: 1
                    //     };

                    //     // Kirim data menggunakan Fetch API
                    //     fetch("/admin/saveprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}", {
                    //         method: "POST", // Metode HTTP POST
                    //         headers: {
                    //             "Content-Type": "application/json" // Set header untuk JSON
                    //         },
                    //         body: JSON.stringify(data) // Kirim data sebagai string JSON
                    //     })
                    //     .then(response => response.json()) // Mengonversi respons menjadi JSON
                    //     .then(data => {
                    //         if (data.status_message === 'failed') {
                    //             var message = data.note.code_transaksi ? 'No. Pembelian sudah terdaftar.' : 'Data gagal disimpan.';
                    //             $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
                    //             $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">' + message + '</div>');
                    //             $('button[btn-action="aciton-confirmasi"]').remove();
                    //             window.location.reload();
                    //         } else {
                    //             // Atur elemen setelah sukses
                    //             $('input[name="data_produk"]').prop('readonly', true);
                    //             $('[btn="save_data"], [btn="cancel_data"]').show();
                    //             $('input[name="code_transaksi"], input[name="tgl_transaksi"]').prop('disabled', true).removeClass('pointer');
                    //             $('input[name="supplier"]').prop('disabled', true);
                    //             $('[title="Tambah Supplier"]').hide();
                    //             $('select[name="gudang"]').prop('disabled', true);
                    //             window.location.href = "/admin/viewpembelian?d=" + data.code;
                    //         }
                    //     })
                    //     .catch(error => {
                    //         console.error('Error:', error);
                    //         // Tampilkan pesan kesalahan jika ada
                    //         $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
                    //         $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi.</div>');
                    //         $('button[btn-action="aciton-confirmasi"]').remove();
                    //     });
                    // }

                    function orderproduk(produk) {
                        const $dataProdukInput = $('input[name="data_produk"]');
                        const $listProdukTransaksi = $('[line="list_produk_transakasi"]');
                        const $modal = $('div[data-model="confirmasi_data"]');
                        const $modalBody = $modal.find('.modal-body');

                        // Disable input fields to prevent multiple submissions
                        $dataProdukInput.prop({ disabled: true });

                        // Show loading indicator
                        $listProdukTransaksi.html(`
                            <tr>
                                <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20">
                                    <div class="col-md-12 load_data_i text-center"> 
                                        <div class="spinner-grow spinner-grow-sm text-muted"></div> 
                                        <div class="spinner-grow spinner-grow-sm text-secondary"></div> 
                                        <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                    </div>
                                </td>
                            </tr>
                        `);

                        // Gather data
                        const data = {
                            code_data: $('input[name="code_data"]').val(),
                            code_transaksi: $('input[name="in_code_transaksi"]').val(),
                            tgl_transaksi: $('input[name="in_tgl_transaksi"]').val(),
                            jenis_ppn: $('input[name="jenis_ppn"]').val(),
                            code_supplier: $('input[name="in_supplier"]').val(),
                            code_gudang: $('input[name="in_gudang"]').val(),
                            code_produk: produk,
                            keterangan: $('input[name="keterangan"]').val(),
                            code_cabang: $('input[name="in_data_cabang"]').val(),
                            qty: 1
                        };

                        // Make AJAX call
                        $.ajax({
                            type: "POST",
                            url: "/admin/saveprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                            data: $.param(data),
                            cache: false,
                            success: function(response) {
                                // Load product list after saving
                                $.get("/admin/listprodpembelian", { code_data: '{{$results['results']['detail']['nomor']}}' }, function(listproduk) {
                                    $listProdukTransaksi.html(listproduk);
                                    $dataProdukInput.prop({ disabled: false }).focus();
                                });

                                // Handle error messages
                                if (response.status_message === 'failed') {
                                    let message = response.note.code_transaksi ? 
                                        'No. Pembelian sudah terdaftar.' : 
                                        'Data gagal disimpan.';
                                    
                                    $modal.modal({ backdrop: false });
                                    $modalBody.html(`<div class="alert alert-danger">${message}</div>`);
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    window.location.reload();
                                }
                            },
                            error: function() {
                                $modal.modal({ backdrop: false });
                                $modalBody.html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                window.location.reload();
                            }
                        });
                    }

                </script>
            @endsection
@endsection