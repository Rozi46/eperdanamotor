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
                                        @endif
                                        @if($level_user['inputpembayaranhutang'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save">Simpan Data</button>
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
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;"  />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_nomor_pembelian" value="" readonly="true" style="display:none;" />

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
                                                    <input type="text" name="nomor_pembelian" placeholder="No. Pembelian (PO)" value="" autofocus>
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
                                                    <input type="text" name="pembayaran" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)"/>
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
                        $('input[name="nomor_transaksi"]').val('{{$code_data}}');
                        $('input[name="pembayaran"]').val('0').prop({disabled:true});
                        $('button[name="btn_save"]').prop({disabled:true});


                        $('input[name="pembayaran"]').keyup(function(){
                            var pembayaran = $('input[name="pembayaran"]').val();
                            $('button[name="btn_save"]').prop('disabled', pembayaran === '' || pembayaran === '0');
                        });
                        
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        // $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        // $('input[name="tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');

                        $('input[name="tgl_transaksi"]').val('{{ date("Y-m-d") }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ date("Y-m-d") }}');
                        
                        // $('input[name="tgl_transaksi"]').change(function(){
                        //     var value = $('input[name="tgl_transaksi"]').val();
                        //     $('input[name="in_tgl_transaksi"]').val(value);
                        //     getcodepurchasepayment();
                        // });

                        $('input[name="tgl_transaksi"]').change(function(){
                            let value = $(this).val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodepurchasepayment();
                        });
        
                        function getcodepurchasepayment(){     
                            var tgl_transaksi = $('input[name="tgl_transaksi"]').val(); 
                            $.getJSON("/admin/getcodepurchasepayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                                $('input[name="in_code_transaksi"]').val(results.code_data);
                                $('input[name="nomor_transaksi"]').val(results.code_data);
                                $('input[name="code_data"]').val(results.code_data);
                            });
                        }

                        $('input[name="tgl_transaksi"]').datepicker({
                            // format: 'dd MM yyyy',
                            format: 'yyyy-mm-dd',
                            startDate: '-2y',
                            endDate: '0d',
                            autoclose : true,
                            // language: "id",
                            orientation: "bottom"
                        });
                        
                        $('input[name="nomor_pembelian"]').keyup(function(){
                            var nomor_pembelian = $('input[name="nomor_pembelian"]').val();
                            if(nomor_pembelian == ''){
                                $('input[name="in_nomor_pembelian"]').val('');
                                $('input[name="nama_supplier"]').val('Nama Supplier');
                                $('input[name="jumlah_hutang"]').val('0');
                                $('input[name="jumlah_bayar"]').val('0');
                                $('input[name="sisa_hutang"]').val('0');
                                $('input[name="pembayaran"]').val('0');
                            }
                        });
                        
                        $('[name="nomor_pembelian"]').autocomplete({
                            minLength:1,
                            source:"/admin/listpurchasepayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    var nomor_pembelian = val.item.code_data;
                                    if(nomor_pembelian == ''){
                                        $('input[name="in_nomor_pembelian"]').val('');
                                        $('input[name="nama_supplier"]').val('Nama Supplier');
                                        $('input[name="jumlah_hutang"]').val('0');
                                        $('input[name="jumlah_bayar"]').val('0');
                                        $('input[name="sisa_hutang"]').val('0');
                                        $('input[name="pembayaran"]').val('0').prop({disabled:true});
                                    }else{
                                        $.getJSON("/admin/detailpurchasepayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_data="+nomor_pembelian, function(results){
                                            $('input[name="nama_supplier"]').val(results.results.detail_supplier.nama);                                            
                                            $('input[name="jumlah_hutang"]').val(results.results.detail_hutang.jumlah);                                           
                                            $('input[name="jumlah_bayar"]').val(results.results.detail_hutang.bayar);                                           
                                            $('input[name="sisa_hutang"]').val(results.results.detail_hutang.sisa);
                                            $('input[name="pembayaran"]').val('').prop({disabled:false}).focus();
                                        });
                                        $('input[name="in_nomor_pembelian"]').val(nomor_pembelian);
                                    }
                                }
                            }
                        });
                        
                        $('input[name="pembayaran"]').keyup(function(){
                            var pembayaran = parseFloat($('input[name="pembayaran"]').val());
                            var sisa_hutang = parseFloat($('input[name="sisa_hutang"]').val());
                            if(pembayaran > sisa_hutang){
                                // alert('Pembayaran tidak boleh melebihi hutang!');
                                $('button[name="btn_save"]').prop({disabled:true});
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Data pembayaran melebihi nilai sisa hutang Rp.' + sisa_hutang + '.</div>');
                                $('button[btn-action="close-confirmasi"]');
                                $('button[btn-action="close-confirmasi"]').click(function(){
                                    if($('button[btn-action="close-confirmasi"]').click){ 
                                        $('input[name="pembayaran"]').val(sisa_hutang);
                                        $('button[name="btn_save"]').prop({disabled:false});
                                    }
                                });
                            }else{
                                $('button[name="btn_save"]').prop({disabled:false});
                            }
                        }); 
                            
                        $('button[name="btn_save"]').click(function(){
                            var nomor_pembelian = $('input[name="nomor_pembelian"]').val();
                            if($(('button[name="btn_save"]')).click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan pembayaran pembelian ' + nomor_pembelian + '. <br>Setelah simpan maka data tidak bisa diubah kembali.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        $('form[name="form_data"]').submit();  
                                    }
                                });
                            }
                        });
                    });

                </script>
            @endsection

@endsection