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
                                            <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data" style="display:none;">Simpan Data</button>
                                            <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data" style="display:none;">Batalkan Penjualan</button>
                                        @endif                                     
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
                        <!-- <div class="col-md-12 bg_page_main" line="form_action"> -->
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/saveppenjualan">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_customer" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang" value="" readonly="true" style="display:none;" />
                                        
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Penjualan <span>*</span></label>
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
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Penjualan <span>*</span></label>
                                                <div class="col-sm-8 input" enabled="false">
                                                    <input disabled="true" type="text" name="code_transaksi" placeholder="No. Penjualan" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="customer" class="col-sm-4 col-form-label">Customer<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    @if($level_user['newcustomer'] == 'Yes')
                                                        <?php if($status_data != 'Yes'){?>
                                                            <a href="/admin/newcustomer?ac=menupenjualanbarang" title="Tambah Customer">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fa fa-plus"></i></div>
                                                                </div>
                                                            </a>
                                                        <?php } ?>
                                                    @endif
                                                    <input type="text" name="customer" placeholder="Customer" value="" autofocus>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang" class="col-sm-6 col-form-label">Gudang <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <select name="gudang" placeholder="Gudang">	
                                                        <option value="" selected="true">Pilih Gudang</option>
                                                        @foreach ($list_gudang as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="type_harga" class="col-sm-4 col-form-label">Type Harga <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="type_harga" placeholder="Type Harga"  value="">
                                                            <option value="Harga Normal" selected="true">Harga Normal</option>	
                                                            <option value="Harga Khusus">Harga Khusus</option>
                                                    </select>
                                                </div>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label class="col-sm-2 col-form-label">Mekanik<span>*</span></label>
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
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan') }}"/>
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
                                                    <th style="min-width:250px; text-align: center;">Nama Barang</th>
                                                    <th style="min-width:75px; text-align: center;">Satuan Barang</th>
                                                    <th style="width:150px; text-align: center;">Harga</th>
                                                    <th style="width:100px; text-align: center;">Qty</th>
                                                    <th style="width:150px; text-align: center;">Diskon</th>
                                                    <th style="width:150px; text-align: center;">Netto</th>
                                                    <th style="min-width:100px; text-align: center;">Total Harga</th>
                                                    <th style="width:25px; text-align: center;"></th>
                                                </tr>
                                            </thead>
											<tbody line="list_produk_transakasi">
                                                <tr>
                                                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" >
                                                        <i class="fa fa-shopping-bag"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Subtotal :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Diskon :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"><span class="def" line="view_kurs_harga"></span>0,00</td>
                                                </tr>
                                                <!-- <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr> -->
											</tbody>
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
                        $('select[name="gudang"] option[value=""]').prop("selected", true);
                        $('input[name="in_gudang"]').val('null');
                        $('input[name="in_customer"]').val('null');                        
                        // $('input[name="customer"]').val('').prop({disabled:true}); 
                        $('input[name="in_cabang"]').val('null');  
                        $('select[name="nama_perusahaan"] option[value=""]').prop("selected", true);   

                        $('input[name="in_gudang"]').val('0a864ba3-d838-11eb-8038-204747ab6caa');
                        $('select[name="gudang"] option[value="0a864ba3-d838-11eb-8038-204747ab6caa"]').prop("selected", true);  

                        $('input[name="in_customer"]').val('aa9c66df-5f5a-494b-aa2c-ea60031fcc68'); 
                        $('input[name="customer"]').val('Customer - Bukittinggi');

                        $('.bg_act_page_main button').prop({disabled:true});
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});
                        // $('input[name="data_produk"]').prop({disabled:true}).val('');
                        $('input[name="data_produk"]').prop({disabled:false}).focus();

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        $('input[name="tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        
                        $('input[name="code_transaksi"]').keyup(function(){
                            var value = $('input[name="code_transaksi"]').val();
                            $('input[name="in_code_transaksi"]').val(value);
                        });
                        
                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $('input[name="tgl_transaksi"]').val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodepenjualan();
                        });
                        
                        $('select[name="gudang"]').change(function(){
                            var value = $('select[name="gudang"]').val();
                            $('input[name="in_gudang"]').val(value);
                        });
                        
                        $('select[name="nama_perusahaan"]').change(function(){
                            var value = $('select[name="nama_perusahaan"]').val();
                            $('input[name="in_cabang"]').val(value);
                        });

                        $('#nama_mekanik').select2({
                            placeholder: 'Pilih Mekanik',
                            allowClear: true,
                            width: '100%'
                        });

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });   

                        // $('input[name="customer"]').prop({disabled:false}).focus();
                        
                        $('input[name="customer"]').keyup(function(){
                            var customer = $('input[name="in_customer"]').val();
                            var gudang = $('select[name="gudang"]').val();
                            var perusahaan = $('select[name="nama_perusahaan"]').val();
                            if(customer == ''){
                                $('input[name="in_customer"]').val('null');
                                $('input[name="data_produk"]').prop({disabled:true}).val('');
                            }else{
                                $('input[name="data_produk"]').prop({disabled:false}).val('');
                            }
                        });

                        $('[name="customer"]').autocomplete({
                            minLength:1,
                            source:"/admin/listopcustomer?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    $('input[name="in_customer"]').val(val.item.code_data);
                                }
                            }
                        });
                        
                        // $('input[name="data_produk"]').prop({disabled:false}).focus();
                        
                        $('[name="data_produk"]').autocomplete({
                            minLength:1,
                            source:"/admin/listbarangtransaksi?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    orderproduk(val.item.code_data);
                                }
                            }
                        });
                    });
        
                    function getcodepenjualan(){     
                        var tgl_transaksi = $('input[name="tgl_transaksi"]').val(); 
                        $.getJSON("/admin/getcodepenjualan?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                            $('input[name="in_code_transaksi"]').val(results.code_data);
                            $('input[name="code_transaksi"]').val(results.code_data);
                        });
                    }

                    function orderproduk(produk){
                        $('.bg_act_page_main button').prop({disabled:true});
                        $('input[name="data_produk"]').prop({disabled:true});
                        var code_data = $('input[name="code_data"]').val();
                        var code_transaksi = $('input[name="in_code_transaksi"]').val();
                        var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                        var code_customer = $('input[name="in_customer"]').val();
                        var code_gudang = $('input[name="in_gudang"]').val();
                        var type_harga = $('select[name="type_harga"]').val();
                        var codeMekanik = $('select[name="nama_mekanik"]').val();
                        var keterangan = $('input[name="keterangan"]').val();
                        $.ajax({
                            type: "POST",
                            url: "/admin/saveprodpenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                            data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&code_customer="+code_customer+"&code_gudang="+code_gudang+"&type_harga="+type_harga+"&code_produk="+produk+"&code_mekanik="+codeMekanik+"&keterangan="+keterangan+"&qty=1",
                            cache: false,
                            success: function(data){
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
                                }else{
                                    $('input[name="data_produk"]').prop({readonly:true});
                                    $('[btn="save_data"]').show();
                                    $('[btn="cancel_data"]') .show();
                                    $('input[name="code_transaksi"]').prop({disabled:true});
                                    $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                                    $('input[name="customer"]').prop({disabled:true});
                                    $('[title="Tambah customer"]').hide();
                                    $('select[name="gudang"]').prop({disabled:true});
                                    window.location.href = "/admin/viewpenjualan?d="+data.code;
                                }
                            }
                        });
                    }

                </script>
            @endsection
@endsection