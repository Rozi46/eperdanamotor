@extends('admin.AdminOne.layout.assets')
@section('title', 'Pembelian Barang')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pembelian Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypembelianbarang'] == 'Yes')
                                            <a href="/admin/historypembelianbarang"><button type="button" class="btn btn-success" btn="history_data">History Pembelian</button></a>
                                        @endif
                                        @if($level_user['inputpembelianbarang'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data" style="display:none;">Simpan Data</button>
                                            <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data" style="display:none;">Batalkan Pembelian</button>
                                        @endif                                     
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
                        <!-- <div class="col-md-12 bg_page_main" line="form_action"> -->
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/saveppembelian">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_supplier" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_gudang" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_cabang" value="" readonly="true" style="display:none;" />
                                        
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-6 col-form-label">Tanggal Pembelian <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Pembelian" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Pembelian <span>*</span></label>
                                                <div class="col-sm-8 input" enabled="false">
                                                    <input disabled="true" type="text" name="code_transaksi" placeholder="No. Pembelian" value="">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="supplier" class="col-sm-4 col-form-label">Supplier<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <!-- @if($level_user['newsupplier'] == 'Yes')
                                                        <?php if($status_data != 'Yes'){?>
                                                            <a href="/admin/newsupplier?ac=menupembelianbarang" title="Tambah Supplier">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fa fa-plus"></i></div>
                                                                </div>
                                                            </a>
                                                        <?php } ?>
                                                    @endif -->
                                                    <input type="text" name="supplier" placeholder="Supplier" value="" autofocus>
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
                                                <label for="nama_perusahaan" class="col-sm-4 col-form-label">Nama Perusahan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="nama_perusahaan" placeholder="Nama Perusahaan">	
                                                        <option value="" selected="true">Pilih Perusahaan</option>
                                                        @foreach ($list_cabang as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama_cabang']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="jenis_ppn" class="col-sm-4 col-form-label">Jenis PPN <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="jenis_ppn" placeholder="Jenis Pembelian"  value=>								
                                                            <option value="" style="display:none;">Jenis PPN</option>
                                                            <option value="Include" selected="true">Include</option>	
                                                            <option value="Exclude">Exclude</option>
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
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">Cash Diskon :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="6">Grand Total :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"><span class="def" line="view_kurs_harga"></span>0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">DPP :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="6">PPN :</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:13px; font-weight: 600;" colspan="3">0,00</td>
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
                        $('select[name="gudang"] option[value=""]').prop("selected", true);
                        $('input[name="in_gudang"]').val('null');
                        $('input[name="in_supplier"]').val('null');                        
                        $('input[name="supplier"]').val('').prop({disabled:true}); 
                        $('input[name="in_cabang"]').val('null');  
                        $('select[name="nama_perusahaan"] option[value=""]').prop("selected", true);   

                        $('input[name="in_gudang"]').val('0a864ba3-d838-11eb-8038-204747ab6caa');
                        $('select[name="gudang"] option[value="0a864ba3-d838-11eb-8038-204747ab6caa"]').prop("selected", true);   

                        $('input[name="in_cabang"]').val('2adaa199-aa81-11ed-8a36-0045e27a3ed8');
                        $('select[name="nama_perusahaan"] option[value="2adaa199-aa81-11ed-8a36-0045e27a3ed8"]').prop("selected", true); 

                        $('.bg_act_page_main button').prop({disabled:true});
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:true}).val('');

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        // $('input[name="tgl_transaksi"]').val('{{ Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y') }}');
                        // $('input[name="in_tgl_transaksi"]').val('{{ Date::now()->format("Y-m-d") }}');

                        $('input[name="tgl_transaksi"]').val('{{ now()->translatedFormat("d F Y") }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        
                        $('input[name="code_transaksi"]').keyup(function(){
                            var value = $('input[name="code_transaksi"]').val();
                            $('input[name="in_code_transaksi"]').val(value);
                        });
                        
                        // $('input[name="tgl_transaksi"]').change(function(){
                        //     var value = $('input[name="tgl_transaksi"]').val();
                        //     $('input[name="in_tgl_transaksi"]').val(value);
                        //     getcodepembelian();
                        // });

                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $(this).val().trim();
                            var bulan = {
                                'Januari':'01',
                                'Februari':'02',
                                'Maret':'03',
                                'April':'04',
                                'Mei':'05',
                                'Juni':'06',
                                'Juli':'07',
                                'Agustus':'08',
                                'September':'09',
                                'Oktober':'10',
                                'November':'11',
                                'Desember':'12'
                            };

                            var split = value.split(/\s+/);
                            var hari  = split[0];
                            var bulanStr = split[1];
                            var tahun = split[2];
                            var bulanNum = bulan[bulanStr];

                            if(!bulanNum){
                                console.error('Format bulan tidak dikenali:', bulanStr);
                                return;
                            }

                            var formatTanggal = tahun+'-'+bulanNum+'-'+hari.padStart(2,'0');
                            $('input[name="in_tgl_transaksi"]').val(formatTanggal);

                            getcodepembelian();
                        });
                        
                        $('select[name="gudang"]').change(function(){
                            var value = $('select[name="gudang"]').val();
                            $('input[name="in_gudang"]').val(value);
                        });
                        
                        $('select[name="nama_perusahaan"]').change(function(){
                            var value = $('select[name="nama_perusahaan"]').val();
                            $('input[name="in_cabang"]').val(value);
                        });

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-2y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });   

                        $('input[name="supplier"]').prop({disabled:false}).focus();
                        
                        $('input[name="supplier"]').keyup(function(){
                            var supplier = $('input[name="in_supplier"]').val();
                            var gudang = $('select[name="gudang"]').val();
                            var perusahaan = $('select[name="nama_perusahaan"]').val();
                            if(supplier == ''){
                                $('input[name="in_supplier"]').val('null');
                                $('input[name="data_produk"]').prop({disabled:true}).val('');
                            }else{
                                $('input[name="data_produk"]').prop({disabled:false}).val('');
                            }
                        });

                        $('[name="supplier"]').autocomplete({
                            minLength:1,
                            source:"/admin/listopsupplier?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    $('input[name="in_supplier"]').val(val.item.code_data);
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
        
                    function getcodepembelian(){     
                        var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val(); 
                        $.getJSON("/admin/getcodepembelian?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                            $('input[name="in_code_transaksi"]').val(results.code_data);
                            $('input[name="code_transaksi"]').val(results.code_data);
                        });
                    }

                    function orderproduk(produk){
                        $('.bg_act_page_main button').prop({disabled:true});
                        $('input[name="data_produk"]').prop({disabled:true});
                        var code_data = $('input[name="code_data"]').val();
                        var code_transaksi = $('input[name="in_code_transaksi"]').val();
                        var jenis_ppn = $('select[name="jenis_ppn"]').val();
                        var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                        var code_supplier = $('input[name="in_supplier"]').val();
                        var code_gudang = $('input[name="in_gudang"]').val();
                        var code_cabang = $('input[name="in_cabang"]').val();
                        var keterangan = $('input[name="keterangan"]').val();
                        $.ajax({
                            type: "POST",
                            url: "/admin/saveprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                            data:"code_data="+code_data+"&code_transaksi="+code_transaksi+"&tgl_transaksi="+tgl_transaksi+"&jenis_ppn="+jenis_ppn+"&code_supplier="+code_supplier+"&code_gudang="+code_gudang+"&code_produk="+produk+"&keterangan="+keterangan+"&code_cabang="+code_cabang+"&qty=1",
                            cache: false,
                            success: function(data){
                                if(data.status_message == 'failed'){
                                    if(data.note.code_transaksi != ''){
                                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">No. Pembelian sudah terdaftar.</div>');
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
                                    $('input[name="supplier"]').prop({disabled:true});
                                    $('[title="Tambah Supplier"]').hide();
                                    $('select[name="gudang"]').prop({disabled:true});
                                    window.location.href = "/admin/viewpembelian?d="+data.code;
                                }
                            }
                        });
                    }

                    // function orderproduk(produk) {
                    //     // Nonaktifkan elemen terkait sebelum memulai proses
                    //     $('.bg_act_page_main button, input[name="data_produk"]').prop('disabled', true);

                    //     // Ambil semua nilai input dan simpan dalam objek data
                    //     var data = {
                    //         code_data: $('input[name="code_data"]').val(),
                    //         code_transaksi: $('input[name="in_code_transaksi"]').val(),
                    //         jenis_ppn: $('select[name="jenis_ppn"]').val(),
                    //         tgl_transaksi: $('input[name="in_tgl_transaksi"]').val(),
                    //         code_supplier: $('input[name="in_supplier"]').val(),
                    //         code_gudang: $('input[name="in_gudang"]').val(),
                    //         code_produk: produk,
                    //         keterangan: $('input[name="keterangan"]').val(),
                    //         code_cabang: $('input[name="in_cabang"]').val(),
                    //         qty: 1
                    //     };

                    //     // Kirim data menggunakan Fetch API
                    //     fetch("/admin/saveprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}", {
                    //         method: "POST", // Metode HTTP POST
                    //         headers: {
                    //             "Content-Type": "application/json" // Set header untuk JSON
                    //         },
                    //         body: JSON.stringify(data) // Kirim data sebagai string JSON
                    //     })
                    //     .then(response => response.json()) // Mengonversi respons menjadi JSON
                    //     .then(data => {
                    //         if (data.status_message === 'failed') {
                    //             var message = data.note.code_transaksi ? 'No. Pembelian sudah terdaftar.' : 'Data gagal disimpan.';
                    //             $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
                    //             $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">' + message + '</div>');
                    //             $('button[btn-action="aciton-confirmasi"]').remove();
                    //             window.location.reload();
                    //         } else {
                    //             // Atur elemen setelah sukses
                    //             $('input[name="data_produk"]').prop('readonly', true);
                    //             $('[btn="save_data"], [btn="cancel_data"]').show();
                    //             $('input[name="code_transaksi"], input[name="tgl_transaksi"]').prop('disabled', true).removeClass('pointer');
                    //             $('input[name="supplier"]').prop('disabled', true);
                    //             $('[title="Tambah Supplier"]').hide();
                    //             $('select[name="gudang"]').prop('disabled', true);
                    //             window.location.href = "/admin/viewpembelian?d=" + data.code;
                    //         }
                    //     })
                    //     .catch(error => {
                    //         console.error('Error:', error);
                    //         // Tampilkan pesan kesalahan jika ada
                    //         $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
                    //         $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi.</div>');
                    //         $('button[btn-action="aciton-confirmasi"]').remove();
                    //     });
                    // }


                </script>
            @endsection
@endsection