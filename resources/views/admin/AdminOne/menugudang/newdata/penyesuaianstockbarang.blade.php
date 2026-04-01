@extends('admin.AdminOne.layout.assets')
@section('title', 'Penyesuaian Stock Barang')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Penyesuaian Stock Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypenyesuaianstockbarang'] == 'Yes')
                                            <a href="/admin/historypenyesuaianstockbarang"><button type="button" class="btn btn-success" btn="history_data">History Penyesuaian</button></a>
                                        @endif
										@if($level_user['penyesuaianstockbarang'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" btn="save_data">Simpan Data</button>
                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/penyesuaianstockbarang">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display: none;" />
										<input type="text" name="code_barang" value="" readonly="true" style="display: none;" />
										<input type="text" name="code_gudang" value="" readonly="true" style="display: none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<div class="col-md-4 bg_form_page" style="display: none;">
                                            <div class="form-group row form_input text-left">
                                                <label for="data_gudang" class="col-sm-3 col-form-label">Data Gudang <span>*</span></label>
                                                <div class="col-sm-9 input">
                                                    <select name="data_gudang" placeholder="Data Gudang">
                                                        @foreach ($list_gudang['results'] as $view_data)
                                                            <option value="{{$view_data['code_data']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Penyesuaian <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Penjualan" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-3 col-form-label">No. Penyesuaian <span>*</span></label>
                                                <div class="col-sm-9 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Penyesuaian" value="{{$code_data}}" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nama_barang" class="col-sm-3 col-form-label">Nama Barang <span>*</span></label>
                                                <div class="col-sm-9 input">
                                                    <input type="text" name="nama_barang" placeholder="Nama Barang" value="" autofocus>
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($list_gudang['results'] as $view_data)
                                            <div class="col-md-6 bg_form_page">
                                                <div class="form-group row form_input text-left">
                                                    <label for="stock_awal" class="col-sm-6 col-form-label">Stock Saat Ini di {{$view_data['nama']}} <span>*</span></label>
                                                    <div class="col-sm-6 input">
                                                        <input type="text" name="stock_awal_{{$view_data['code_data']}}" placeholder="Stock Awal/Saat Ini" value="{{ old('stock_awal') }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 bg_form_page">
                                                <div class="form-group row form_input text-left">
                                                    <label for="selisih_stock" class="col-sm-6 col-form-label">Selisih Stock <span>*</span></label>
                                                    <div class="col-sm-6 input">
                                                        <input type="text" name="selisih_stock_{{$view_data['code_data']}}" placeholder="Selisih Stock" value="{{ old('selisih_stock') }}"  onKeyPress="return goodchars(event,'-0123456789,',this)">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 bg_form_page">
                                                <div class="form-group row form_input text-left">
                                                    <label for="stock_akhir" class="col-sm-6 col-form-label">Stock Akhir <span>*</span></label>
                                                    <div class="col-sm-6 input">
                                                        <input type="text" name="stock_akhir_{{$view_data['code_data']}}" placeholder="Stock Akhir" value="{{ old('stock_akhir') }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-3 col-form-label">Keterangan <span>*</span></label>
                                                <div class="col-sm-9 input">
												    <textarea name="keterangan">{{ old('keterangan') }}</textarea>
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

                        $('input[name="code_data"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        $('input[name="tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        
                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });
                        
                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $('input[name="tgl_transaksi"]').val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodepenyesuaianstock();
                        });

                        $('input[name^="selisih_stock_"]').on('blur', function(){
                            let val = parseNumber($(this).val());
                            $(this).val(formatNumber(val));
                        });

                        var data_gudang = $('select[name="data_gudang"]').val(); 
                        $('input[name="code_gudang"]').val(data_gudang); 
                        $('input[name="code_barang"]').val('');
                        $('input[name="nama_barang"]').val('').focus();
                        <?php foreach ($list_gudang['results'] as $view_data) {?>
                            $('input[name="stock_awal_{{$view_data['code_data']}}"]').val('0,00');
                            $('input[name="selisih_stock_{{$view_data['code_data']}}"]').val('0,00').prop({readonly:false});
                            $('input[name="stock_akhir_{{$view_data['code_data']}}"]').val('0,00');
                        <?php } ?>
                        
                        // Dev Penyesuaian Selisih Up
                        $('select[name="data_gudang"]').change(function(){
                            var data_gudang = $('select[name="data_gudang"]').val(); 
                            $('input[name="code_gudang"]').val(data_gudang); 
                            $('input[name="code_barang"]').val('');
                            $('input[name="nama_barang"]').val('').focus();
                            $('input[name="stock_awal"]').val('0,00');
                            $('input[name="selisih_stock"]').val('0,00').prop({readonly:false});
                            $('input[name="stock_akhir"]').val('0,00');
                            $('button[name="btn_save"]').html('Simpan Data');
                        });
                        
                        // Dev Penyesuaian Selisih Up
                        $('input[name="nama_barang"]').focus(function(){
                            $('input[name="nama_barang"]').select();
                        });

                        $('input[name="nama_barang"]').keyup(function(){
                            var code_gudang = $('input[name="code_gudang"]').val();
                            $('button[name="btn_save"]').html('Simpan Data');
                            
                            $('[name="nama_barang"]').autocomplete({
                                minLength:1,
                                source:"/admin/listbarangstockopname?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_gudang="+code_gudang,
                                autoFocus: true,
                                select:function(event, val){
                                    if(val.item.code_data != undefined){
                                        $('input[name="code_barang"]').val(val.item.code_data);
                                        $.getJSON("/admin/liststockbarangSO?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_data="+val.item.code_data, function(results){
                                            $.each( results.results, function(key,val ) {
                                                <?php foreach ($list_gudang['results'] as $key_wh => $view_data) {?>
                                                    // $('input[name="stock_awal_{{$view_data['code_data']}}"]').val(val.stock_prod[<?php echo $key_wh;?>].stock_akhir_<?php echo $view_data['code_data']; ?>);
                                                    // $('input[name="selisih_stock_{{$view_data['code_data']}}"]').val('0').focus();
                                                    // $('input[name="stock_akhir_{{$view_data['code_data']}}"]').val(val.stock_prod[<?php echo $key_wh;?>].stock_akhir_<?php echo $view_data['code_data']; ?>);

                                                    let stokRaw_{{$view_data['code_data']}} =
                                                        val.stock_prod[<?php echo $key_wh;?>].stock_akhir_<?php echo $view_data['code_data']; ?>;

                                                    // parse dari API (US / numeric)
                                                    let stokFloat_{{$view_data['code_data']}} =
                                                        parseFloat(stokRaw_{{$view_data['code_data']}}) || 0;

                                                    // FORMAT ID (1.234,56)
                                                    $('input[name="stock_awal_{{$view_data['code_data']}}"]').val(
                                                        formatNumber(stokFloat_{{$view_data['code_data']}}, 2)
                                                    );

                                                    // reset selisih
                                                    $('input[name="selisih_stock_{{$view_data['code_data']}}"]').val(
                                                        formatNumber(0, 2)
                                                    );

                                                    // stock akhir = stock awal
                                                    $('input[name="stock_akhir_{{$view_data['code_data']}}"]').val(
                                                        formatNumber(stokFloat_{{$view_data['code_data']}}, 2)
                                                    );
                                                <?php } ?>
                                            });
                                        }); 
                                    }
                                }
                            });
                        }); 
                        
                        <?php foreach ($list_gudang['results'] as $view_data) {?>
                            $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').keyup(function(){
                                var selisih_stock_<?php echo $view_data['code_data']; ?> = $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val();
                                // formatnumber('selisih_stock_<?php echo $view_data['code_data']; ?>','0');
                                hitung_stock_<?php echo $view_data['code_data']; ?>();
                            });
                            $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').change(function(){
                                var selisih_stock_<?php echo $view_data['code_data']; ?> = $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val();
                                // formatnumber('selisih_stock_<?php echo $view_data['code_data']; ?>','0');
                                hitung_stock_<?php echo $view_data['code_data']; ?>();
                            });
                        <?php } ?>

                        $('[btn="save_data"]').click(function(){
                            if($('[btn="save_data"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan penyesuaian stock. Setelah simpan maka data tidak bisa diubah kembali.</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                $('button[btn-action="aciton-confirmasi"]').click(function(){
                                    if($('button[btn-action="aciton-confirmasi"]').click){
                                        var code_data = $('input[name="code_data"]').val();
                                        var code_transaksi = $('input[name="code_transaksi"]').val(); 
                                        var tgl_transaksi = $('input[name="tgl_transaksi"]').val();    
                                        var data_gudang = $('select[name="data_gudang"]').val();   
                                        var nama_barang = $('input[name="nama_barang"]').val();   
                                        var code_barang = $('input[name="code_barang"]').val(); 

                                        <?php foreach ($list_gudang['results'] as $view_data) {?>
                                            var stock_awal_<?php echo $view_data['code_data']; ?> = $('input[name="stock_awal_<?php echo $view_data['code_data']; ?>"]').val();
                                            var selisih_stock_<?php echo $view_data['code_data']; ?> = $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val();
                                            var stock_akhir_<?php echo $view_data['code_data']; ?> = $('input[name="stock_akhir_<?php echo $view_data['code_data']; ?>"]').val();
                                        <?php } ?>

                                        var keterangan = $('textarea[name="keterangan"]').val();
                                        if(tgl_transaksi == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Tanggal Penyesuaian harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        }else if(code_transaksi == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">No. Penyesuaian harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        }else if(data_gudang == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Data Gudang harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        }else if(nama_barang == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Nama barang harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        }else if(code_barang == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Nama barang harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        <?php foreach ($list_gudang['results'] as $view_data) {?>
                                            }else if(stock_awal_<?php echo $view_data['code_data']; ?>  == ''){
                                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Pastikan Stock Awal/Saat Ini terinput.</div>');
                                                $('button[btn-action="aciton-confirmasi"]').remove();
                                                $('button[btn-action="close-confirmasi"]').click(function(){
                                                    if($('button[btn-action="close-confirmasi"]').click){
                                                        loadingpage(20000);
                                                        window.location.href = "/admin/penyesuaianstockbarang";
                                                    }
                                                });
                                            }else if(selisih_stock_<?php echo $view_data['code_data']; ?>  == ''){
                                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Selisih Stock harus diisi.</div>');
                                                $('button[btn-action="aciton-confirmasi"]').remove();
                                                $('button[btn-action="close-confirmasi"]').click(function(){
                                                    if($('button[btn-action="close-confirmasi"]').click){
                                                        loadingpage(20000);
                                                        window.location.href = "/admin/penyesuaianstockbarang";
                                                    }
                                                });
                                            }else if(stock_akhir_<?php echo $view_data['code_data']; ?>  == ''){
                                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Pastikan Stock Akhir terinput.</div>');
                                                $('button[btn-action="aciton-confirmasi"]').remove();
                                                $('button[btn-action="close-confirmasi"]').click(function(){
                                                    if($('button[btn-action="close-confirmasi"]').click){
                                                        loadingpage(20000);
                                                        window.location.href = "/admin/penyesuaianstockbarang";
                                                    }
                                                });
                                        <?php } ?>
                                        }else if(keterangan == ''){
                                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Keterangan harus diisi.</div>');
                                            $('button[btn-action="aciton-confirmasi"]').remove();
                                            $('button[btn-action="close-confirmasi"]').click(function(){
                                                if($('button[btn-action="close-confirmasi"]').click){
                                                    loadingpage(20000);
                                                    window.location.href = "/admin/penyesuaianstockbarang";
                                                }
                                            });
                                        }else{
                                            loadingpage(2000000);

                                            <?php foreach ($list_gudang['results'] as $view_data) { ?>
                                                stock_awal_<?php echo $view_data['code_data']; ?> = toDbNumber(stock_awal_<?php echo $view_data['code_data']; ?>);
                                                selisih_stock_<?php echo $view_data['code_data']; ?> = toDbNumber(selisih_stock_<?php echo $view_data['code_data']; ?>);
                                                stock_akhir_<?php echo $view_data['code_data']; ?> = toDbNumber(stock_akhir_<?php echo $view_data['code_data']; ?>);
                                            <?php } ?>

                                            $.ajax({
                                                type: "POST",
                                                url: "/admin/savepenyesuaianstock?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['admin_login_perdana']}}",
                                                data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&data_gudang="+data_gudang+"&code_barang="+code_barang+"&<?php foreach ($list_gudang['results'] as $view_data) {?>stock_awal_<?php echo $view_data['code_data']; ?>="+stock_awal_<?php echo $view_data['code_data']; ?>+"&selisih_stock_<?php echo $view_data['code_data']; ?>="+selisih_stock_<?php echo $view_data['code_data']; ?>+"&stock_akhir_<?php echo $view_data['code_data']; ?>="+stock_akhir_<?php echo $view_data['code_data']; ?>+"&<?php } ?>keterangan="+encodeURIComponent(keterangan),
                                                cache: false,
                                                success: function(data){
                                                    if(data.status_message == 'success'){   
                                                        window.location.href = "/admin/historypenyesuaianstockbarang";
                                                    }else{
                                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                                                        $('button[btn-action="aciton-confirmasi"]').remove();
                                                        window.location.reload();
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                                
                            }
                        });
                    });  
        
                    function getcodepenyesuaianstock(){     
                        var tgl_transaksi = $('input[name="tgl_transaksi"]').val(); 
                        $.getJSON("/admin/getcodepenyesuaianstock?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                            $('input[name="code_data"]').val(results.code_data);
                            $('input[name="code_transaksi"]').val(results.code_data);
                        });
                    }
                    
                    // Ubah format Indonesia ke float (1.234,56 → 1234.56)
                    function parseNumber(val) {
                        if (!val) return 0;
                        val = val.toString().replace(/\./g, '').replace(',', '.');
                        return parseFloat(val) || 0;
                    }

                    // Format float ke Indonesia (1234.56 → 1.234,56)
                    function formatNumber(val, decimal = 2) {
                        return new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: decimal,
                            maximumFractionDigits: decimal
                        }).format(val);
                    }

                    function toDbNumber(val) {
                        if (!val) return 0;
                        return parseNumber(val).toFixed(2);
                    }                    
                    
                    <?php foreach ($list_gudang['results'] as $view_data) {?>
                        function hitung_stock_<?php echo $view_data['code_data']; ?>(){
                            // var stock_awal_<?php echo $view_data['code_data']; ?> = $('input[name="stock_awal_<?php echo $view_data['code_data']; ?>"]').val();
                            // var stock_awal_<?php echo $view_data['code_data']; ?> = stock_awal_<?php echo $view_data['code_data']; ?>.replace(",", ".");
                            // var stock_awal_<?php echo $view_data['code_data']; ?> = parseInt(stock_awal_<?php echo $view_data['code_data']; ?>);
                            
                            // var selisih_stock_<?php echo $view_data['code_data']; ?> = $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val();
                            // var selisih_stock_<?php echo $view_data['code_data']; ?> = selisih_stock_<?php echo $view_data['code_data']; ?>.replace(",", ".");
                            // var selisih_stock_<?php echo $view_data['code_data']; ?> = parseInt(selisih_stock_<?php echo $view_data['code_data']; ?>);

                            // var stock_akhir_<?php echo $view_data['code_data']; ?> = (selisih_stock_<?php echo $view_data['code_data']; ?>+stock_awal_<?php echo $view_data['code_data']; ?>);
                            // if($('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val() == '-'){
                            //     $('input[name="stock_akhir_<?php echo $view_data['code_data']; ?>"]').val(stock_awal_<?php echo $view_data['code_data']; ?>);
                            // }else if($('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val() != ''){
                            //     $('input[name="stock_akhir_<?php echo $view_data['code_data']; ?>"]').val(stock_akhir_<?php echo $view_data['code_data']; ?>);
                            // }else{
                            //     $('input[name="stock_akhir_<?php echo $view_data['code_data']; ?>"]').val(stock_awal_<?php echo $view_data['code_data']; ?>);
                            // }                            

                            let stockAwal = parseNumber(
                                $('input[name="stock_awal_<?php echo $view_data['code_data']; ?>"]').val()
                            );

                            let selisih = parseNumber(
                                $('input[name="selisih_stock_<?php echo $view_data['code_data']; ?>"]').val()
                            );

                            let stockAkhir = stockAwal + selisih;

                            $('input[name="stock_akhir_<?php echo $view_data['code_data']; ?>"]').val(
                                formatNumber(stockAkhir)
                            );                            
                        }
                    <?php } ?>
                </script>
            @endsection
@endsection