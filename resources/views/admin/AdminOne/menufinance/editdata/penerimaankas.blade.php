@extends('admin.AdminOne.layout.assets')
@section('title', 'Penerimaan Kas')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Penerimaan Kas</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypenerimaankas'] == 'Yes')
                                            <a href="/admin/historypenerimaankas"><button type="button" class="btn btn-success" btn="history_data">History Penerimaan Kas</button></a>
                                            
                                            <a href="printpenerimaankas?d={{$results['results']['detail']['code_data']}}" target="_blank"><button type="button" class="btn btn-secondary" name="btn_print" btn="print_data"><i class="fa fa-print"></i> Print Penerimaan Kas</button></a>
                                            
                                            <?php if($res_user['level'] == 'LV5677001'){?> <button type="button" class="btn btn-danger" name="del_data" btn="del_data"><i class="fa fa-trash-o"></i> Hapus Penerimaan Kas</button> <?php } ?> 
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
										<input type="text" name="in_no_pembelian" value="" readonly="true" style="display:none;" />
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Diterima Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input class="pointer" type="text" name="full_name" value="{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum ditentukan' }}" readonly="true">
                                                </div>
                                            </div>
										</div>
                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-4 col-form-label">Tanggal Transaksi <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Transaksi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nomor_transaksi" class="col-sm-4 col-form-label">No. Penerimaan Kas <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="nomor_transaksi" placeholder="No. Faktur" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="akun_biaya" class="col-sm-4 col-form-label">Akun Biaya <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="akun_biaya" placeholder="Akun Biaya" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="jumlah" class="col-sm-4 col-form-label">Jumlah <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="jumlah" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789,',this)" readonly="true"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-2 col-form-label">Keterangan  <span>*</span></label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
									</div> 
								</form>
                            </div>
                        </div>
					</div>
				</div>
            </div>         
            
			@section('script')
				<script type="text/javascript">
                    $(document).ready(function(){

                        $('.bg_act_page_main button').prop({disabled:true});
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});
                        $('[btn="print_data"]').prop({disabled:false});
                        $('[btn="del_data"]').prop({disabled:false});

                        $('input[name="in_no_pembelian"]').val('');
                        $('input[name="no_pembelian"]').val('');
                        $('input[name="supplier"]').val('');
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
                        
                        $('[btn="save_data"]').show();
                        $('[btn="cancel_data"]').show();
                        $('input[name="code_transaksi"]').prop({disabled:true});
                        $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                        $('input[name="no_pembelian"]').prop({disabled:true});
                            
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? 'Belum ditentukan' }}');
                        $('input[name="code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="nomor_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan'}}');

                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        
                        $('input[name="akun_biaya"]').val('{{ $results['results']['detail']['jenis'] ?? 'Belum ditentukan' }}');
                        $('input[name="jumlah"]').val('{{ number_format ($results['results']['detail']['nilai'] ?? 0,0,"",".") }}');
                        $('input[name="keterangan"]').val('{{ $results['results']['detail']['keterangan'] }}');
                    });

                    $('[name="del_data"]').click(function(){
                        if($('[name="del_data"]').click){
                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus penerimaan kas {{$results['results']['detail']['nomor']}}.</div>');
                            $('button[btn-action="aciton-confirmasi"]').remove();
                            $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                            $('button[btn-action="aciton-confirmasi"]').click(function(){
                                if($('button[btn-action="aciton-confirmasi"]').click){
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    $('button[btn-action="close-confirmasi"]').remove();
                                    loadingpage(20000);
                                    window.location.href = "/admin/deletepenerimaankas?d={{$results['results']['detail']['code_data']}}&tipe_data=penerimaankas";
                                }  
                            });
                        }
                    });
                </script>
            @endsection
            
@endsection