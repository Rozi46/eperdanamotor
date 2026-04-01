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
                                            <a href="/admin/historymutasiterima"><button type="button" class="btn btn-success" btn="history_data">History Mutasi Terima</button></a>
                                        @endif
                                        @if($level_user['inputmutasiterima'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data" style="display:none;">Simpan & Selesai</button>
                                            <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data" style="display:none;">Batalkan Penerimaan</button>
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
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Mutasi <span>*</span></label>
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
                                                <label for="code_transaksi" class="col-sm-2 col-form-label">No. Mutasi <span>*</span></label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Mutasi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="no_mutasi_kirim" class="col-sm-6 col-form-label">No. Mutasi Kirim <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="no_mutasi_kirim" placeholder="No. Mutasi Kirim" value="" autofocus>
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
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan') }}"/>
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
                                                    <th style="width:150px; text-align: center;">Qty Mutasi</th>
                                                    <th style="width:150px; text-align: center;">Qty Diterima</th>
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
                        
                        $('input[name="no_mutasi_terima"]').val('');
                        $('input[name="in_no_mutasi_kirim"]').val('');
                        $('input[name="no_mutasi_kirim"]').val('');
                        $('input[name="gudang_asal"]').val('');
                        $('input[name="gudang_tujuan"]').val('');
                        $('input[name="gudang"]').val('');

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
                            getcodemutasiterima();
                        });
        
                        function getcodemutasiterima(){     
                            var tgl_transaksi = $('input[name="tgl_transaksi"]').val(); 
                            $.getJSON("/admin/getcodemutasiterima?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                                $('input[name="in_code_transaksi"]').val(results.code_data);
                                $('input[name="code_transaksi"]').val(results.code_data);
                            });

                            // $.ajax({
                            //     type: "GET",
                            //     url: "/admin/getcodemutasiterima",
                            //     data: {
                            //         token: "{{ $request['token'] }}",
                            //         u: "{{ $request['u'] }}",
                            //         tgl_transaksi: tgl_transaksi
                            //     },
                            //     dataType: "json",
                            //     success: function(results) {
                            //         $('input[name="in_code_transaksi"]').val(results.code_data);
                            //         $('input[name="code_transaksi"]').val(results.code_data);
                            //     },
                            //     error: function(xhr, status, error) {
                            //         console.log("Error: " + error);
                            //     }
                            // });

                        }

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });
                        
                        $('input[name="no_mutasi_kirim"]').keyup(function(){
                            var no_mutasi_kirim = $('input[name="no_mutasi_kirim"]').val();
                            if(no_mutasi_kirim == ''){
                                $('input[name="in_no_mutasi_kirim"]').val('');
                                $('input[name="gudang_asal"]').val('');
                                $('input[name="gudang_tujuan"]').val('');
                                $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" ><i class="fa fa-shopping-bag"></i></td></tr>');
                            }
                        });
                        
                        $('[name="no_mutasi_kirim"]').autocomplete({
                            minLength:1,
                            source:"/admin/listopmutasi?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    var no_mutasi_kirim = val.item.code_data;
                                    var no_mutasi_terima = $('input[name="code_transaksi"]').val();
                                    if(no_mutasi_kirim == ''){
                                        $('input[name="in_no_pembelian"]').val('');
                                        $('input[name="gudang_asal"]').val('');
                                        $('input[name="gudang_tujuan"]').val('');
                                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" ><i class="fa fa-shopping-bag"></i></td></tr>');
                                    }else{
                                        $.getJSON("/admin/detailopmutasi?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_data="+no_mutasi_kirim, function(results){
                                            $('input[name="gudang_asal"]').val(results.results.detail_gudang_asal.nama);
                                            $('input[name="gudang_tujuan"]').val(results.results.detail_gudang_tujuan.nama);
                                        });
                                        $('input[name="in_no_mutasi_kirim"]').val(no_mutasi_kirim);
                                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                                        $.get("/admin/listprodmutasiterima",{code_data:no_mutasi_kirim,code_mutasi_terima:no_mutasi_terima},function(listproduk){
                                            $('[line="list_produk_transakasi"]').html(listproduk);
                                            $('input[name="keterangan"]').focus();
                                        });

                                    }
                                }
                            }
                        });
                    });
                </script>
            @endsection
            
@endsection