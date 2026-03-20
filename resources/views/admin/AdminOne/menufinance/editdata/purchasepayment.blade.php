@extends('admin.AdminOne.layout.assets')
@section('title', 'Pembayaran Pembelian')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pembayaran Pembelian</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypembayaranhutang'] == 'Yes')
                                            <a href="/admin/historypembayaranhutang"><button type="button" class="btn btn-success" btn="history_data">History Pembayaran</button></a>
                                            
                                            <a href="printpurchasepayment?d={{$results['results']['detail']['nomor']}}" target="_blank"><button type="button" class="btn btn-secondary" name="btn_print" btn="print_data"><i class="fa fa-print"></i> Print Pembayaran</button></a>
                                        @endif  
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savepurchasepayment">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_nomor_pembelian" value="" readonly="true" style="display:none;" />

										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Pembayaran Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input class="pointer" type="text" name="full_name" value="{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum ditentukan' }}" readonly="true">
                                                </div>
                                            </div>
										</div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Transaksi <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Transaksi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nomor_transaksi" class="col-sm-6 col-form-label">No. Transaksi <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="nomor_transaksi" placeholder="No. Transaksi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nama_supplier" class="col-sm-6 col-form-label">Nama Supplier <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="nama_supplier" placeholder="Nama Supplier" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nomor_pembelian" class="col-sm-6 col-form-label">No. Pembelian (PO) <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="nomor_pembelian" placeholder="No. Pembelian (PO)" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="jumlah_hutang" class="col-sm-6 col-form-label">Jumlah Hutang<span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="jumlah_hutang" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="jumlah_bayar" class="col-sm-6 col-form-label">Jumlah Bayar<span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="jumlah_bayar" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="sisa_hutang" class="col-sm-6 col-form-label">Sisa Hutang<span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="sisa_hutang" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-8 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="pembayaran" class="col-sm-3 col-form-label">Pembayaran<span>*</span></label>
                                                <div class="col-sm-9 input">
                                                    <input type="text" name="pembayaran" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
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

            @include('admin.AdminOne.layout.listpopup')


			@section('script')
				<script type="text/javascript">
                    $(document).ready(function(){

                        $('.bg_act_page_main button').prop({disabled:true});
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});
                        $('[btn="print_data"]').prop({disabled:false});

                        $('input[name="in_nomor_pembelian"]').val('');
                        $('input[name="nomor_pembelian"]').val('');
                        $('input[name="nama_supplier"]').val('');
                        $('input[name="jumlah_hutang"]').val('');
                        $('input[name="jumlah_bayar"]').val('');
                        $('input[name="sisa_hutang"]').val('');
                        $('input[name="pembayaran"]').val('');

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
                        $('input[name="nomor_pembelian"]').prop({disabled:true});
                            
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? 'Belum ditentukan' }}');
                        $('input[name="code_transaksi"]').val('{{$results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{$results['results']['detail']['nomor'] ?? '' }}');
                        $('input[name="nomor_transaksi"]').val('{{$results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');

                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');
                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y'); ?>');

                        $('input[name="nomor_pembelian"]').val('{{ $results['results']['detail']['nomor_hutang'] ?? 'Belum ditentukan' }}');
                        $('input[name="nama_supplier"]').val('{{ $results['results']['detail_supplier']['nama'] ?? 'Belum ditentukan' }}');
                        $('input[name="jumlah_hutang"]').val('{{ number_format ($results['results']['detail_hutang']['jumlah'] ?? 0,2,",",".") }}');
                        $('input[name="jumlah_bayar"]').val('{{ number_format ($results['results']['jumlah_bayar'] ?? 0,2,",",".") }}');
                        $('input[name="sisa_hutang"]').val('{{ number_format ($results['results']['sisa_hutang'] ?? 0,2,",",".") }}');
                        $('input[name="pembayaran"]').val('{{ number_format ($results['results']['detail']['jumlah'] ?? 0,2,",",".") }}');
                    });
                </script>
            @endsection

@endsection