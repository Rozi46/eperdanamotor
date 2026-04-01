@extends('admin.AdminOne.cashier.layout.assets')
@section('title', 'Dashboard Cashier')

@section('content')
			<div class="page_main_full">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-xl-9" style="padding-right: 5px;">
							
								<div class="col-md-12 bg_page_main form_action" >
									<div class="col-md-12 data_page">
										<form method="post" name="form_data" enctype="multipart/form-data" action="saveppenjualan">
											{{csrf_field()}}
											<div class="row bg_data_page form_page content">
                                                <input type="text" name="code_data" value="{{ $results['results']['detail']['nomor'] ?? '' }}" readonly="true" style="display:none;" />
                                                <input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
                                                <input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
                                                <input type="text" name="in_data_perusahaan" value="" readonly="true" style="display:none;" />
                                                <input type="text" name="in_customer" value="" readonly="true" style="display:none;" />
                                                <input type="text" name="in_gudang" value="" readonly="true" style="display:none;" />

                                                <div class="col-md-6 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="tgl_transaksi" class="col-sm-4 col-form-label">Tanggal Penjualan</label>
                                                        <div class="col-sm-8 input">
                                                            <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                            </div>
                                                            <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Penjualan" value="" readonly="true">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="code_transaksi" class="col-sm-4 col-form-label">No. Penjualan</label>
                                                        <div class="col-sm-8 input">
                                                            <input type="text" name="code_transaksi" placeholder="No. Penjualan" value="" readonly="true">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="customer" class="col-sm-2 col-form-label">Customer</label>
                                                        <div class="col-sm-10 input">
                                                            <input type="text" name="customer" placeholder="Customer" value="" readonly="true">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="gudang" class="col-sm-4 col-form-label">Gudang</label>
                                                        <div class="col-sm-8 input">
                                                            <input type="text" name="gudang" placeholder="Pilih Gudang" value="" readonly="true" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="mekanik" class="col-sm-2 col-form-label">Mekanik</label>
                                                        <div class="col-sm-10 input">
                                                            <select id="nama_mekanik" name="nama_mekanik" placeholder="Mekanik" multiple>
                                                                @foreach ($list_mekanik as $view_data)
                                                                    <option value="{{$view_data['code_data']}}">{{$view_data['nama']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="type_harga" class="col-sm-4 col-form-label">Type Harga</label>
                                                        <div class="col-sm-8 input">                                                    
                                                            <input type="text" name="type_harga" placeholder="Type harga" value="" readonly="true" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 bg_form_page">
                                                    <div class="form-group row form_input text-left">
                                                        <label for="jenis_penjualan" class="col-sm-4 col-form-label">Jenis Penjualan <span>*</span></label>
                                                        <div class="col-sm-8 input">
                                                            <select name="jenis_penjualan" placeholder="Jenis Penjualan"  value="">
                                                                    <option value="Cash" selected="true">Cash</option>	
                                                                    <option value="Kredit">Kredit</option>
                                                            </select>
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
                                                            <th style="width:150px; text-align: center;">Satuan Barang</th>
                                                            <th style="width:150px; text-align: center;">Harga</th>
                                                            <th style="width:75px; text-align: center;">Qty</th>
                                                            <th style="width:150px; text-align: center;">Diskon</th>
                                                            <th style="min-width:100px; text-align: center;">Netto</th>
                                                            <th style="min-width:100px; text-align: center;">Total Harga</th>
                                                            <?php if($results['results']['detail']['status_transaksi'] != 'Finish' && $results['results']['counttransaksi'] == 0){?>
                                                                <?php if($results['results']['detail']['kode_user'] == $res_user['id']){?>
                                                                    <th style="width:25px; text-align: center;"></th>
                                                                <?php } elseif ($res_user['level'] == 'LV5677001') {?> 
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
						<div class="col-xl-3" style="padding-left: 5px; position: fixed;right:0;">						
                            <div class="col-md-12 bg_page_main">
                                <!-- <div class="col-md-12 data_page"> -->
                                    <!-- <div class="bg_logo_company_sidebar"> -->
                                        <!-- <?php if($request['data_company']['foto'] == NULL){?> -->
                                            <!-- <img src="{{ asset('/image/public/icon.png') }}" alt="Logo" style="width: 150px; height: 100px;"> -->
                                        <!-- <?php }else{?> -->
                                            <!-- <img src="{{ asset('/image/public/'.$request['data_company']['foto'].'') }}" alt="Logo" style="width: 150px; height: 100px;"> -->
                                        <!-- <?php } ?> -->
                                        <!-- <div class="nm_company"><?php echo $request['data_company']['kantor']; ?> </div> -->
                                        <!-- <div class="nm_company"><?php echo $request['data_company']['alamat']; ?> </div> -->
                                    <!-- </div>	 -->
                                <!-- </div> -->
                                <div class="col-md-12 hd_page_main" style ="line-height: 40px;">Total Transaksi</div>
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_form_page">
                                            <div class="form_input text-right">
                                                <div class="val_con_dash _data" id="list_total_transakasi" style="font-size: 20px; font-weight: 600; text-align: right; color: red;">Rp {{ number_format($results['results']['detail']['grand_total'], 2, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="col-md-12 hd_page_main">Pembayaran</div>
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_form_page">
                                            <div class="form_input text-left">
                                                <div class="tag_title"></div>
                                                <input type="text" name="list_pembayaran" id="list_pembayaran" placeholder="0" value="0" style="font-size: 20px; font-weight: 600; text-align: right; color: red;"/>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->

                                <!-- <div class="col-md-12 hd_page_main">Kembalian</div>
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_form_page">
                                            <div class="form_input text-right">
                                                <div class="val_con_dash _data" id="list_kembalian" style="font-size: 20px; font-weight: 600; text-align: right; color: red;">Rp 0,00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="col-md-12 bg_form_page">
                                    <div class="form_input text-center">
                                        <div class="btn_130">
                                            <button type="button" class="btn btn-success" name="btn_save" style ="height: 62px;">Finish Transaksi</button>
                                            <button type="button" class="btn btn-primary" name="btn_pending" style ="height: 62px;">Pendingkan Transaksi</button>
                                            <button type="button" class="btn btn-danger" name="btn_cancel" style ="height: 62px;">Batal Transaksi</button>
                                            <button type="button" class="btn btn-info" name="btn_history" style ="height: 62px;">History Transaksi</button>
                                            <button type="button" class="btn btn-warning" name="btn_stock"style ="height: 62px;">Stock Barang</button>
                                            <!-- <button type="button" class="btn btn-danger" style ="height: 62px;">Tutup Transaksi</button> -->
                                        </div>
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
                        $('#nama_mekanik').select2({
                            placeholder: 'Pilih Mekanik',
                            allowClear: true,
                            width: '100%'
                        });

                        $('[line="list_total_transakasi"]').val('Rp. <?php echo number_format($results['results']['detail']['grand_total'],2,",",".") ?>');
                        
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
                        $('input[name="code_transaksi"]').prop({disabled:true});
                        $('input[name="tgl_transaksi"]').prop({disabled:true}).removeClass('pointer');
                        $('input[name="gudang"]').prop({disabled:true});   
                        $('input[name="data_produk"]').prop({readonly:false}).focus();

                        
                        $('input[name="full_name"]').val('{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum Ditentukan' }}');    
                        $('input[name="code_data"]').val('{{ $results['results']['detail']['code_data'] ?? 'Belum Ditentukan' }}');
                        $('input[name="tgl_transaksi"]').val('{{ $results['results']['detail']['tanggal'] }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ $results['results']['detail']['tanggal'] }}');
                        $('input[name="code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? 'Belum Ditentukan' }}');
                        $('input[name="in_code_transaksi"]').val('{{ $results['results']['detail']['nomor'] ?? '' }}');
                        $('input[name="in_data_perusahaan"]').val('{{ $results['results']['detail']['kode_kantor'] ?? '' }}');    
                        $('input[name="in_customer"]').val('{{ $results['results']['detail']['kode_customer'] ?? '' }}');
                        $('input[name="customer"]').val('{{ $results['results']['detail_customer']['nama'] ?? 'Belum Ditentukan' }}');
                        $('select[name="nama_mekanik"]').val(@json( array_keys($results['results']['detail_mekanik'] ?? [] ))).trigger('change');     
                        $('input[name="in_gudang"]').val('{{ $results['results']['detail']['kode_gudang'] ?? '' }}');
                        $('input[name="gudang"]').val('{{ $results['results']['detail_gudang']['nama'] ?? 'Belum Ditentukan' }}');
                        $('input[name="type_harga"]').val('{{ $results['results']['type_harga'] ?? 'Belum Ditentukan' }}');
                        $('input[name="keterangan"]').val('{{ $results['results']['detail']['ket'] ?? 'Belum Ditentukan' }}');
                        $('select[name="jenis_penjualan"]').val('{{ $results['results']['detail']['jenis_penjualan'] ?? 'Belum Ditentukan' }}');

                        
                        var perusahaan = $('input[name="in_data_perusahaan"]').val();
                        $('[name="data_produk"]').autocomplete({
                            minLength:1,
                            source:"listbarangtransaksi?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_perusahaan="+perusahaan,
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    orderproduk(val.item.code_data);
                                }
                            }
                        });
                        
                        $('input[name="customer"]').keyup(function(){
                            var supplier = $('input[name="customer"]').val();
                            if(supplier == ''){
                                $('input[name="in_customer"]').val('null');
                            }
                        });

                        var perusahaan = $('input[name="in_data_perusahaan"]').val();
                        $('[name="scustomer"]').autocomplete({
                            minLength:1,
                            source:"getopcusotmer?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_perusahaan="+perusahaan,
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    $('input[name="in_customer"]').val(val.item.code_data);
                                }
                            }
                        });
                        
                        $('.bg_act_page_main button').prop({disabled:true});

                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                    
                        $.get("listprodpenjualan",{code_data:'{{$results['results']['detail']['nomor']}}',focus_line:'{{$request['fc']}}'},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });

                        // Pembayaran 
                            $('input[name="list_pembayaran"]').val(0);
                            var totalTransaksiElement = document.getElementById("list_total_transakasi");
                            var pembayaranElement = document.getElementById("list_pembayaran");
                            var kembalianElement = document.getElementById("list_kembalian");   

                            // Hitung kembalian saat pembayaran diinput
                            $('input[name="list_pembayaran"]').keyup(function(){
                                var pembayaran = $('input[name="list_pembayaran"]').val();                            
                                var totalTransaksi = {{ $results['results']['detail']['grand_total'] }}; 
                                var kembalian = pembayaran - totalTransaksi;

                                kembalianElement.textContent = kembalian >= 0
                                    ?  kembalian.toLocaleString('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 2
                                    })
                                    : 'Rp 0,00';
                            });

                            $('input[name="list_pembayaran"]').change(function(){
                                var pembayaran = $('input[name="list_pembayaran"]').val();                            
                                var totalTransaksi = {{ $results['results']['detail']['grand_total'] }}; 
                                var kembalian = pembayaran - totalTransaksi;

                                kembalianElement.textContent = kembalian >= 0
                                    ?  kembalian.toLocaleString('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 2
                                    })
                                    : 'Rp 0,00';
                            });
                        // end Pembayaran

                        $('[name="btn_cancel"]').click(function(){
                            if($('[name="btn_cancel"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk membatalkan transaksi {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="action-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="action-confirmasi">Yakin</button>');
                                $('button[btn-action="action-confirmasi"]').click(function(){
                                    if($('button[btn-action="action-confirmasi"]').click){
                                        $('button[btn-action="action-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        loadingpage(20000);
                                        window.location.href = "deletepenjualan?d={{$results['results']['detail']['code_data']}}&tipe_data=penjualan";
                                    }  
                                });
                            }
                        });
                            
                        $('[name="btn_save"]').click(function(){
                            if($('[name="btn_save"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk finish transaksi {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="action-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="action-confirmasi">Yakin</button>');
                                $('button[btn-action="action-confirmasi"]').click(function(){
                                    if($('button[btn-action="action-confirmasi"]').click){
                                        $('button[btn-action="action-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        var customer = $('input[name="in_customer"]').val();
                                        var ket = $('input[name="keterangan"]').val();
                                        var jenisPenjualan = $('select[name="jenis_penjualan"]').val();
                                        var codeMekanik = $('select[name="nama_mekanik"]').val();
                                        loadingpage(5000);
                                        window.location.href = "updatepenjualan?d={{$results['results']['detail']['nomor']}}&customer="+customer+"&tipe_data=penjualan&jenis_penjualan="+jenisPenjualan+"&code_mekanik="+codeMekanik+"&ket="+encodeURIComponent(ket);                                         
                                        loadingpage(20000);
                                        window.open("printsalesorder?d={{$results['results']['detail']['nomor']}}");
                                    }
                                });
                            }
                        });
                            
                        $('[name="btn_pending"]').click(function(){
                            if($('[name="btn_pending"]').click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk pending transaksi {{$results['results']['detail']['nomor']}}.</div>');
                                $('button[btn-action="action-confirmasi"]').remove();
                                $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="action-confirmasi">Yakin</button>');
                                $('button[btn-action="action-confirmasi"]').click(function(){
                                    if($('button[btn-action="action-confirmasi"]').click){
                                        $('button[btn-action="action-confirmasi"]').remove();
                                        $('button[btn-action="close-confirmasi"]').remove();
                                        var customer = $('input[name="in_customer"]').val();
                                        var ket = $('input[name="keterangan"]').val();
                                        var jenisPenjualan = $('select[name="jenis_penjualan"]').val();
                                        loadingpage(20000);
                                        window.location.href = "pendingpenjualan?d={{$results['results']['detail']['nomor']}}&customer="+customer+"&tipe_data=penjualan&jenis_penjualan="+jenisPenjualan+"&ket="+encodeURIComponent(ket);   
                                    }
                                });
                            }
                        });

                    });

                    function orderproduk(produk){
                        // $('.bg_act_page_main button').prop({disabled:true});
                        $('input[name="data_produk"]').prop({disabled:true});

                        $('[line="list_produk_transakasi"]').html('<tr><td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20"><div class="col-md-12 load_data_i text-center"> <div class="spinner-grow spinner-grow-sm text-muted"></div> <div class="spinner-grow spinner-grow-sm text-secondary"></div> <div class="spinner-grow spinner-grow-sm text-dark"></div></div></td></tr>');
                        
                        // Ambil nilai dari form
                        var codeData = $('input[name="code_data"]').val();
                        var codeTransaksi = $('input[name="in_code_transaksi"]').val();
                        var tglTransaksi = $('input[name="in_tgl_transaksi"]').val();
                        var codeCustomer = $('input[name="in_customer"]').val();
                        var codeGudang = $('input[name="in_gudang"]').val();
                        var typeHarga = $('input[name="type_harga"]').val();
                        var keterangan = $('input[name="keterangan"]').val();
                        var jenisPenjualan = $('select[name="jenis_penjualan"]').val();

                        // Kirim data melalui AJAX
                        $.ajax({
                            type: "POST",
                            url: `saveprodpenjualan?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}`,
                            data: {
                                code_data: codeData,
                                code_transaksi: codeTransaksi,
                                tgl_transaksi: tglTransaksi,
                                code_customer: codeCustomer,
                                code_gudang: codeGudang,
                                type_harga: typeHarga,
                                code_produk: produk,
                                keterangan: keterangan,
                                jenis_penjualan: jenisPenjualan,
                                qty: 1
                            },
                            cache: false,
                            success: function (data) {
                                $.get("listprodpenjualan",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                });
                                if(data.status_message == 'failed'){
                                    if(data.note.code_transaksi != ''){
                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Penjualan sudah terdaftar.</div>');
                                        $('button[btn-action="action-confirmasi"]').remove();
                                        window.location.reload();
                                    }else{
                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan.</div>');
                                        $('button[btn-action="action-confirmasi"]').remove();
                                        window.location.reload();
                                    }
                                }
                            }
                        });
                    }

                </script>

                <!-- <script>
                    $(function () {
                        const el = {
                            produk: $('[name="data_produk"]'),
                            customer: $('[name="customer"]'),
                            inCustomer: $('[name="in_customer"]'),
                            mekanik: $('#nama_mekanik'),
                            table: $('[line="list_produk_transakasi"]'),
                            pembayaran: $('[name="list_pembayaran"]'),
                            kembalian: $('#list_kembalian')
                        };

                        const state = {
                            token: "{{ $request['token'] }}",
                            user: "{{ $request['u'] }}",
                            kode: "{{ $results['results']['detail']['nomor'] }}",
                            total: {{ $results['results']['detail']['grand_total'] ?? 0 }}
                        };

                        init();

                        // =====================
                        // INIT
                        // =====================
                        function init() {
                            initSelect2();
                            initAutocomplete();
                            initProdukList();
                            initPembayaran();
                            bindActions();
                        }

                        function initSelect2() {
                            el.mekanik.select2({
                                placeholder: 'Pilih Mekanik',
                                allowClear: true,
                                width: '100%'
                            });
                        }

                        // =====================
                        // AUTOCOMPLETE
                        // =====================
                        function initAutocomplete() {
                            const perusahaan = $('[name="in_data_perusahaan"]').val();

                            el.produk.autocomplete({
                                minLength: 1,
                                source: `/cash/listbarangtransaksi?token=${state.token}&u=${state.user}&code_perusahaan=${perusahaan}`,
                                select: (e, ui) => ui.item.code_data && orderProduk(ui.item.code_data)
                            });

                            el.customer.autocomplete({
                                minLength: 1,
                                source: `/cash/getopcustomer?token=${state.token}&u=${state.user}&code_perusahaan=${perusahaan}`,
                                select: (e, ui) => el.inCustomer.val(ui.item.code_data)
                            });
                        }

                        // =====================
                        // LOAD PRODUK
                        // =====================
                        function initProdukList() {
                            renderLoading();

                            $.get('/cash/listprodpenjualan', {
                                code_data: state.kode
                            }, function (html) {
                                el.table.html(html);
                                el.produk.prop('disabled', false).focus();
                            });
                        }

                        function renderLoading() {
                            el.table.html(`<tr>
                                <td colspan="20" class="text-center p-4">
                                    <div class="spinner-border"></div>
                                </td>
                            </tr>`);
                        }

                        // =====================
                        // ORDER PRODUK
                        // =====================
                        function orderProduk(produk) {

                            toggleLoading(true);
                            renderLoading();

                            const payload = {
                                _token: "{{ csrf_token() }}",
                                token: state.token,
                                u: state.user,
                                code_data: $('[name="code_data"]').val(),
                                code_transaksi: $('[name="in_code_transaksi"]').val(),
                                tgl_transaksi: $('[name="in_tgl_transaksi"]').val(),
                                code_customer: el.inCustomer.val(),
                                code_gudang: $('[name="in_gudang"]').val(),
                                type_harga: $('[name="type_harga"]').val(),
                                jenis_penjualan: $('[name="jenis_penjualan"]').val(),
                                keterangan: $('[name="keterangan"]').val(),
                                code_produk: produk,
                                qty: 1
                            };

                            $.post('/cash/saveprodpenjualan', payload)
                                .done(() => initProdukList())
                                .fail(() => showError('Gagal simpan produk'))
                                .always(() => toggleLoading(false));
                        }

                        // =====================
                        // PEMBAYARAN
                        // =====================
                        function initPembayaran() {
                            el.pembayaran.val(0);

                            el.pembayaran.on('keyup change', function () {
                                const bayar = parseFloat($(this).val()) || 0;
                                const kembali = bayar - state.total;

                                el.kembalian.text(
                                    kembali > 0 ? formatRupiah(kembali) : 'Rp 0,00'
                                );
                            });
                        }

                        function formatRupiah(num) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(num);
                        }

                        // =====================
                        // BUTTON ACTION
                        // =====================
                        function bindActions() {

                            $('[name="btn_cancel"]').click(() => confirmAction(
                                'Batalkan transaksi?',
                                `/cash/deletepenjualan?d=${state.kode}`
                            ));

                            $('[name="btn_pending"]').click(() => submitAction('pendingpenjualan'));

                            $('[name="btn_save"]').click(() => submitAction('updatepenjualan', true));
                        }

                        function submitAction(url, print = false) {

                            const params = $.param({
                                d: state.kode,
                                customer: el.inCustomer.val(),
                                jenis_penjualan: $('[name="jenis_penjualan"]').val(),
                                ket: $('[name="keterangan"]').val(),
                                code_mekanik: el.mekanik.val()
                            });

                            confirmAction('Lanjutkan proses?', `/cash/${url}?${params}`, print);
                        }

                        function confirmAction(msg, url, print = false) {
                            if (!confirm(msg)) return;

                            window.location.href = url;

                            if (print) {
                                window.open(`/cash/printsalesorder?d=${state.kode}`);
                            }
                        }

                        // =====================
                        // UTIL
                        // =====================
                        function toggleLoading(state) {
                            el.produk.prop('disabled', state);
                            $('.bg_act_page_main button').prop('disabled', state);
                        }

                        function showError(msg) {
                            alert(msg);
                        }

                    });
                </script> -->
            @endsection

@endsection