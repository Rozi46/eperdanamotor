@extends('admin.AdminOne.layout.assets')
@section('title', 'Pengiriman Barang')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pengiriman Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypengirimanbarang'] == 'Yes')
                                            <a href="/admin/historypengiriman"><button type="button" class="btn btn-success" btn="history_data">History Pengiriman</button></a>
                                        @endif
                                        @if($level_user['inputpengirimanbarang'] == 'Yes')
                                            <?php if($results['results']['detail_penjualan']['status_transaksi'] == 'Proses'){?>
                                                <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data" style="display:none;">Simpan & Selesai</button>
                                                <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data" style="display:none;">Batalkan Pengiriman</button>
                                            <?php }?>
                                        @endif
                                        @if($level_user['historypengirimanbarang'] == 'Yes')
                                            <?php if($results['results']['detail_penjualan']['status_transaksi'] == 'Finish'){?>
                                                <a href="printpengiriman?d={{$results['results']['detail']['nomor_pengiriman']}}" target="_blank"><button type="button" class="btn btn-secondary" name="btn_print" btn="print_data"><i class="fa fa-print"></i> Print Tanda Kirim</button></a>
                                                <?php if($res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'){?> <button type="button" class="btn btn-danger btn_del" name="del_data" btn="del_data"><i class="fa fa-trash-o"></i> Hapus Pengiriman</button> <?php } ?>
                                            <?php }?>
                                        @endif    
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savepengiriman">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_no_penjualan" value="" readonly="true" style="display:none;" />
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Dikirim Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input class="pointer" type="text" name="full_name" value="{{$results['results']['user_transaksi']['full_name']}}" readonly="true">
                                                </div>
                                            </div>
										</div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Pengiriman</label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Pengiriman" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Pengiriman</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Pengiriman" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang" class="col-sm-5 col-form-label">Gudang</label>
                                                <div class="col-sm-7 input">
                                                    <input type="text" name="gudang" placeholder="Gudang" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="no_penjualan" class="col-sm-6 col-form-label">No. Penjualan (SO)</label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="no_penjualan" placeholder="No. Penjualan (SO)" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-8 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="customer" class="col-sm-2 col-form-label">Customer</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="customer" placeholder="Customer" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-2 col-form-label">Keterangan</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan') }}" autofocus/>
                                                </div>                                                
											</div>
										</div>
									</div> 
								</form>
                            </div>
                        </div>
						<div class="col-md-12 bg_page_main">
                            <div class="col-md-12 data_page view">
                                <div class="row bg_data_page" style="padding-left: 5px;padding-right: 5px;padding-bottom: 5px;">
                                    <div class="table_data transaksi">
                                        <table class="table_view table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px; text-align: center;">No</th>
                                                    <th style="min-width:300px; text-align: center;">Nama Barang</th>
                                                    <th style="min-width:150px; text-align: center;">Qty Penjualan</th>
                                                    <th style="width:150px; text-align: center;">Qty Dikirim</th>
                                                </tr>
                                            </thead>
											<tbody line="list_produk_transakasi">
                                                <tr>
                                                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" >
                                                        <i class="fa fa-shopping-bag"></i>
                                                    </td>
                                                </tr>
											</tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
				</div>
            </div>

            @include('admin.AdminOne.layout.listpopup')            
            
			@section('script')
				<script type="text/javascript">
                    $(document).ready(function(){

                        $('.bg_act_page_main button').prop({disabled:true});
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});
                        
                        $('input[name="no_so"]').val('');
                        $('input[name="in_no_penjualan"]').val('');
                        $('input[name="no_penjualan"]').val('');
                        $('input[name="customer"]').val('');
                        $('input[name="gudang"]').val('');

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        
                        $('input[name="code_transaksi"]').keyup(function(){
                            var value = $('input[name="code_transaksi"]').val();
                            $('input[name="in_code_transaksi"]').val(value);
                        });
                        
                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $('input[name="tgl_transaksi"]').val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                        });

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });
                        
                        $('div[data-model="listproduk"]').modal('hide');
                        $('[btn="save_data"]').show();
                        $('[btn="cancel_data"]').show();
                        $('input[name="code_transaksi"]').prop({disabled:true});
                        $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                        $('input[name="no_penjualan"]').prop({disabled:true});
                        $('input[name="no_so"]').prop({disabled:true});
                            
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? '' }}');
                        $('input[name="code_transaksi"]').val('{{ $results['results']['detail']['nomor_pengiriman'] ?? 'Belum Ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail']['nomor_pengiriman'] ?? ''}}');                        
                        $('input[name="no_penjualan"]').val('{{ $results['results']['detail_penjualan']['nomor'] ?? 'Belum Ditentukan' }}');
                        $('input[name="in_no_penjualan"]').val('{{ $results['results']['detail_penjualan']['nomor'] ?? ''}}');
                        $('input[name="customer"]').val('{{ $results['results']['detail_customer']['nama'] ?? 'Belum Ditentukan' }}');
                        $('input[name="gudang"]').val('{{ $results['results']['detail_gudang']['nama'] ?? 'Belum Ditentukan' }}');

                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');

                        $('input[name="no_so"]').val('{{ $results['results']['detail']['nomor_pengiriman'] ?? 'Belum Ditentukan' }}');
                        $('input[name="keterangan"]').val('{{ $results['results']['detail']['ket'] ?? 'Belum Ditentukan' }}');

                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                        
                        $.get("/admin/listprodpengiriman",{code_data:'{{$results['results']['detail']['nomor_penjualan']}}',status_data:'Yes',nomor_penjualan:'{{$results['results']['detail']['nomor_penjualan']}}',nomor_pengiriman:'{{$results['results']['detail']['nomor_pengiriman']}}'},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="keterangan"]').focus();


                            $('input[name="keterangan"]').prop({enabled:true});
                            $('[line="list_produk_transakasi"] input').prop({enabled:true});
                            $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail']['nomor_pengiriman'] ?? '' }}');

                        });

                        $('[name="btn_cancel"]').click(function(){
                            if($('[name="btn_cancel"]').click){
                                var no_pengiriman = $('input[name="in_code_transaksi"]').val();
                                if(no_pengiriman != ''){
                                    $('div[data-model="confirmasi"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk membatalkan pengiriman barang {{$results['results']['detail']['nomor_pengiriman']}}.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').remove();
                                            loadingpage(20000);
                                            window.location.href = "/admin/deletersobarang?d={{$code_data}}&tipe_data=input&cdpo="+no_pengiriman;
                                        }
                                    });
                                }
                            }
                        });

                        $('[name="del_data"]').click(function(){
                            if($('[name="del_data"]').click){
                                var no_pengiriman = $('input[name="in_code_transaksi"]').val();
                                if(no_pengiriman != ''){
                                    $('div[data-model="confirmasi"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus pengiriman barang {{$results['results']['detail']['nomor_pengiriman']}}.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').remove();
                                            loadingpage(20000);
                                            window.location.href = "/admin/deletersobarang?d={{$code_data}}&tipe_data=input&cdpo="+no_pengiriman;
                                        }
                                    });
                                }
                            }
                        });
                            
                        $('[btn="save_data"]').click(function(){
                            if($('[btn="save_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                <?php if(count($results['results']['list_produk']) > 0){?>
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan dan selesaikan pengiriman barang {{$results['results']['detail']['nomor_pengiriman']}}. Setelah simpan dan selesai maka data tidak bisa diubah kembali.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').remove();
                                            loadingpage(2000000);
                                            var keterangan = $('input[name="keterangan"]').val();  
                                            window.location.href = "/admin/updatersobarang?code_data={{$results['results']['detail']['code_data']}}&tipe_data=input&keterangan="+encodeURIComponent(keterangan);  
                                        }
                                    });
                                <?php }else{ ?>
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda belum menerima barang, silakan input qty yang diterima.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                <?php } ?>
                            }
                        });
                    });
                </script>
            @endsection
            
@endsection