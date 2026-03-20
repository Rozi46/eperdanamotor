@extends('admin.AdminOne.layout.assets')
@section('title', 'Mutasi Terima')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Mutasi Terima</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historymutasiterima'] == 'Yes')
                                            <a href="/admin/historymutasiterima">
                                                <button type="button" class="btn btn-success" btn="history_data">History Mutasi Terima</button>
                                            </a>
                                        @endif

                                        @if($level_user['inputmutasiterima'] == 'Yes')
                                            @if($results['results']['detail_mutasi']['status_transaksi'] == 'Proses' && ($results['results']['detail_mutasi_terima']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'))
                                                <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>
                                                <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Mutasi Terima</button>
                                            @endif
                                        @endif

                                        @if($level_user['historymutasiterima'] == 'Yes')
                                            @if($results['results']['detail_mutasi']['status_transaksi'] == 'Finish')
                                                <a href="printmutasiterima?d={{$results['results']['detail_mutasi_terima']['nomor']}}" target="_blank">
                                                    <button type="button" class="btn btn-secondary" name="btn_print" btn="print_data">
                                                        <i class="fa fa-print"></i> Print Mutasi Terima
                                                    </button>
                                                </a>
                                            @endif
                                        @endif   
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savepenerimaan">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_no_mutasi_kirim" value="" readonly="true" style="display:none;" />
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Mutasi Terima Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input class="pointer" type="text" name="full_name" value="{{$results['results']['user_transaksi']['full_name']}}" readonly="true">
                                                </div>
                                            </div>
										</div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Mutasi</label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Mutasi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-8 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-2 col-form-label">No. Mutasi</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Mutasi" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="no_mutasi_kirim" class="col-sm-6 col-form-label">No. Mutasi Kirim</label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="no_mutasi_kirim" placeholder="No. Mutasi Kirim" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_asal" class="col-sm-4 col-form-label">Gudang Asal</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="gudang_asal" placeholder="Gudang Asal" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_tujuan" class="col-sm-4 col-form-label">Gudang Tujuan</label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="gudang_tujuan" placeholder="Gudang Tujuan" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan') }}" {{ !($results['results']['detail_mutasi']['status_transaksi'] == 'Proses' && ($results['results']['detail_mutasi_terima']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa')) ? 'readonly' : '' }}>
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
                                                    <th style="min-width:150px; text-align: center;">Qty Mutasi</th>
                                                    <th style="width:150px; text-align: center;">Qty Terima</th>
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
                        
                        $('input[name="in_no_mutasi_kirim"]').val('');
                        $('input[name="no_mutasi_kirim"]').val('');
                        $('input[name="gudang_asal"]').val('');
                        $('input[name="gudang_tujuan"]').val('');

                        $('input[name="in_code_transaksi"]').val('{{ $code_data ?? ''}}');
                        $('input[name="code_transaksi"]').val('{{ $code_data ?? 'Belum ditentukan' }}');

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
                        $('input[name="no_mutasi_kirim"]').prop({disabled:true});
                            
                        $('input[name="code_data"]').val('{{ $results['results']['detail_mutasi_terima']['code_data'] ?? 'Belum ditentukan' }}');
                        $('input[name="code_transaksi"]').val('{{ $results['results']['detail_mutasi_terima']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail_mutasi_terima']['nomor'] ?? '' }}');                        
                        $('input[name="no_mutasi_kirim"]').val('{{ $results['results']['detail_mutasi_kirim']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="in_no_pembelian"]').val('{{ $results['results']['detail_mutasi_kirim']['nomor'] ?? ''}}');
                        $('input[name="gudang_asal"]').val('{{ $results['results']['detail_gudang_asal']['nama'] ?? 'Belum ditentukan' }}');
                        $('input[name="gudang_tujuan"]').val('{{ $results['results']['detail_gudang_tujuan']['nama'] ?? 'Belum ditentukan' }}');
                        $('input[name="tgl_transaksi"]').val('{{ Date::parse($results['results']['detail_mutasi_terima']['tanggal'])->format('d F Y') }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ Date::parse($results['results']['detail_mutasi_terima']['tanggal'])->format('d F Y') }}');
                        $('input[name="keterangan"]').val('{{ $results['results']['detail_mutasi_terima']['ket'] ?? 'Belum ditentukan' }}');

                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                        
                        $.get("/admin/listprodmutasiterima",{
                            code_data:'{{ $results['results']['detail_mutasi_terima']['code_data'] ?? ''}}',
                            status_data:'Yes',
                            nomor_mutasi_terima:'{{ $results['results']['detail_mutasi_terima']['nomor'] ?? ''}}',
                            nomor_mutasi_kirim:'{{ $results['results']['detail_mutasi_kirim']['nomor'] ?? ''}}'
                        },function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="keterangan"]').focus();

                            $('input[name="keterangan"]').prop({enabled:true});
                            $('[line="list_produk_transakasi"] input').prop({enabled:true});
                            $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail_mutasi_terima']['nomor'] ?? ''}}');

                        });

                        $('[name="btn_cancel"]').click(function(){
                            if($('[name="btn_cancel"]').click){
                                var no_mutasi_terima = $('input[name="in_code_transaksi"]').val();
                                if(no_mutasi_terima != ''){
                                    $('div[data-model="confirmasi"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk membatalkan mutasi terima {{$results['results']['detail_mutasi_terima']['nomor']}}.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').remove();
                                            loadingpage(20000);
                                            window.location.href = "/admin/deletemutasiterima?d={{$code_data}}&tipe_data=input&no_mutasi_terima="+no_mutasi_terima;
                                        }
                                    });
                                }
                            }
                        });
                            
                        $('[btn="save_data"]').click(function(){
                            if($('[btn="save_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                <?php if($results['results']['count_prod_mt'] > 0){?>
                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan dan selesaikan mutasi terima {{$results['results']['detail_mutasi_terima']['nomor']}}. Setelah simpan dan selesai maka data tidak bisa diubah kembali.</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').remove();
                                            loadingpage(2000000);
                                            var keterangan = $('input[name="keterangan"]').val();  
                                            window.location.href = "/admin/updatemutasiterima?code_data={{$results['results']['detail_mutasi_terima']['code_data']}}&tipe_data=input&keterangan="+encodeURIComponent(keterangan);    
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