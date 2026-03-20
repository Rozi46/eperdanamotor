@extends('admin.AdminOne.layout.assets')
@section('title', 'Mutasi Kirim')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Mutasi Kirim</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historymutasikirim'] == 'Yes')
                                            <a href="/admin/historymutasikirim"><button type="button" class="btn btn-success" btn="history_data">History Mutasi Kirim</button></a>
                                        @endif
                                        @if($level_user['inputmutasikirim'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data" style="display:none;">Simpan Data</button>
                                            <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data" style="display:none;">Batalkan Mutasi</button>
                                        @endif                                     
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
                        <!-- <div class="col-md-12 bg_page_main" line="form_action"> -->
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savemutasikirim">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang_asal" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang_tujuan" value="" readonly="true" style="display:none;" />
                                        
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-4 col-form-label">Tanggal Mutasi <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Mutasi" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Mutasi <span>*</span></label>
                                                <div class="col-sm-8 input" enabled="false">
                                                    <input disabled="true" type="text" name="code_transaksi" placeholder="No. Mutasi" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_asal" class="col-sm-4 col-form-label">Gudang Asal<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="gudang_asal" placeholder="Gudang asal">	
                                                        <option value="" selected="true">Gudang Asal</option>
                                                        @foreach ($list_gudang as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_tujuan" class="col-sm-4 col-form-label">Gudang Tujuan<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="gudang_tujuan" placeholder="Gudang Tujuan">	
                                                        <option value="" selected="true">Gudang Tujuan</option>
                                                        @foreach ($list_gudang as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
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
                                                    <th style="width:100px; text-align: center;">Qty</th>
                                                </tr>
                                            </thead>
											<tbody line="list_produk_transakasi">
                                                <tr>
                                                    <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="5" >
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


			@section('script')
				<script type="text/javascript">
                    $(document).ready(function(){
                        $('select[name="gudang_asal"], select[name="gudang_tujuan"]').val('');
                        $('.bg_act_page_main button').prop({disabled: true});
                        $('[onclick="BackPage()"]').prop({disabled: false});
                        $('[btn="history_data"]').prop({disabled: false});
                        $('input[name="data_produk"]').prop({disabled: true}).val('');
                        $('input[name="in_code_transaksi"], input[name="code_transaksi"]').val('{{$code_data}}');

                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        
                        $('input[name="code_transaksi"]').keyup(function(){
                            var value = $('input[name="code_transaksi"]').val();
                            $('input[name="in_code_transaksi"]').val(value);
                        });

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        }); 
                        
                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $('input[name="tgl_transaksi"]').val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodemutasikirim();
                        });
                        
                        $('select[name="gudang_asal"], select[name="gudang_tujuan"]').change(function(){
                            var asal = $('select[name="gudang_asal"]').val();
                            var tujuan = $('select[name="gudang_tujuan"]').val();
                            $('input[name="in_gudang_asal"]').val(asal);
                            $('input[name="in_gudang_tujuan"]').val(tujuan);
                            $('input[name="data_produk"]').prop('disabled', !(asal && tujuan));
                        });
                        
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

                    function getcodemutasikirim() {     
                        $.getJSON("/admin/getcodemutasikirim?token={{ $request['token'] }}&u={{ $request['u'] }}&tgl_transaksi=" + $('input[name="tgl_transaksi"]').val(), function(results) {
                            $('input[name="in_code_transaksi"], input[name="code_transaksi"]').val(results.code_data);
                        });
                    }

                    // function orderproduk(produk){
                    //     $('.bg_act_page_main button').prop({disabled:true});
                    //     $('input[name="data_produk"]').prop({disabled:true});
                    //     var code_data = $('input[name="code_data"]').val();
                    //     var code_transaksi = $('input[name="in_code_transaksi"]').val();
                    //     var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                    //     var code_gudang_asal = $('input[name="in_gudang_asal"]').val();
                    //     var code_gudang_tujuan = $('input[name="in_gudang_tujuan"]').val();
                    //     var keterangan = $('input[name="keterangan"]').val();
                    //     $.ajax({
                    //         type: "POST",
                    //         url: "/admin/savemutasikirim?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                    //         data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&code_gudang_asal="+code_gudang_asal+"&code_gudang_tujuan="+code_gudang_tujuan+"&code_produk="+produk+"&keterangan="+keterangan+"&qty=1",
                    //         cache: false,
                    //         success: function(data){
                    //             if(data.status_message == 'failed'){
                    //                 if(data.note.code_transaksi != ''){
                    //                     $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    //                     $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Mutasi sudah terdaftar.</div>');
                    //                     $('button[btn-action="aciton-confirmasi"]').remove();
                    //                     window.location.reload();
                    //                 }else{
                    //                     $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    //                     $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                    //                     $('button[btn-action="aciton-confirmasi"]').remove();
                    //                     window.location.reload();
                    //                 }
                    //             }else{
                    //                 $('input[name="data_produk"]').prop({readonly:true});
                    //                 $('[btn="save_data"]').show();
                    //                 $('[btn="cancel_data"]') .show();
                    //                 $('input[name="code_transaksi"]').prop({disabled:true});
                    //                 $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                    //                 $('input[name="supplier"]').prop({disabled:true});
                    //                 $('[title="Tambah Supplier"]').hide();
                    //                 $('select[name="gudang"]').prop({disabled:true});
                    //                 window.location.href = "/admin/viewmutasikirim?d="+data.code;
                    //             }
                    //         }
                    //     });
                    // }

                    function orderproduk(produk) {
                        // Nonaktifkan elemen terkait sebelum memulai proses
                        $('.bg_act_page_main button, input[name="data_produk"]').prop('disabled', true);

                        // Ambil semua nilai input sekali dan simpan dalam variabel
                        var data = {
                            code_data: $('input[name="code_data"]').val(),
                            code_transaksi: $('input[name="in_code_transaksi"]').val(),
                            tgl_transaksi: $('input[name="in_tgl_transaksi"]').val(),
                            code_gudang_asal: $('input[name="in_gudang_asal"]').val(),
                            code_gudang_tujuan: $('input[name="in_gudang_tujuan"]').val(),
                            keterangan: $('input[name="keterangan"]').val(),
                            code_produk: produk,
                            qty: 1
                        };

                        // Kirim data melalui AJAX
                        $.ajax({
                            type: "POST",
                            url: "/admin/savemutasikirim?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                            data: data, // Kirim data sebagai objek
                            cache: false,
                            success: function(response) {
                                if (response.status_message === 'failed') {
                                    var message = response.note.code_transaksi ? 'No. Mutasi sudah terdaftar.' : 'Data gagal disimpan.';
                                    $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                    $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">' + message + '</div>');
                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                    window.location.reload();
                                } else {
                                    // Atur elemen setelah sukses
                                    $('input[name="data_produk"]').prop('readonly', true);
                                    $('[btn="save_data"], [btn="cancel_data"]').show();
                                    $('input[name="code_transaksi"], input[name="tgl_transaksi"]').prop('disabled', true).removeClass('pointer');
                                    $('select[name="gudang_asal"], select[name="gudang_tujuan"]').prop('disabled', true);
                                    window.location.href = "/admin/viewmutasikirim?d=" + response.code;
                                }
                            }
                        });
                    }


                </script>
            @endsection
@endsection