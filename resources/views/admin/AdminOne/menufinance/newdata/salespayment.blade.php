@extends('admin.AdminOne.layout.assets')
@section('title', 'Pembayaran Penjualan')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pembayaran Penjualan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['historypembayaranpiutang'] == 'Yes')
                                            <a href="/admin/historypembayaranpiutang"><button type="button" class="btn btn-success" btn="history_data">History Pembayaran</button></a>
                                        @endif
                                        @if($level_user['inputpembayaranpiutang'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save">Simpan Data</button>
                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savesalespayment">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_nomor_penjualan" value="" readonly="true" style="display:none;" />

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
                                                <label for="nama_customer" class="col-sm-6 col-form-label">Nama Customer <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="nama_customer" placeholder="Nama Customer" value="" readonly="true">
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nomor_penjualan" class="col-sm-6 col-form-label">No. Penjualan (SO) <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="nomor_penjualan" placeholder="No. Penjualan (SO)" value="" autofocus>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="jumlah_piutang" class="col-sm-6 col-form-label">Jumlah Piutang<span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="jumlah_piutang" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
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
                                                <label for="sisa_piutang" class="col-sm-6 col-form-label">Sisa Piutang<span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <input type="text" name="sisa_piutang" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789.',this)" readonly="true"/>
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
				<!-- <script type="text/javascript">
                    $(document).ready(function(){
                        $('input[name="nomor_transaksi"]').val('{{$code_data}}');
                        $('input[name="pembayaran"]').val('0').prop({disabled:true});;
                        $('button[name="btn_save"]').prop({disabled:true});


                        $('input[name="pembayaran"]').keyup(function(){
                            var pembayaran = $('input[name="pembayaran"]').val();
                            $('button[name="btn_save"]').prop('disabled', pembayaran === '' || pembayaran === '0');
                        });
                        
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        $('input[name="tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        
                        $('input[name="tgl_transaksi"]').change(function(){
                            var value = $('input[name="tgl_transaksi"]').val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodesalespayment();
                        });
        
                        function getcodesalespayment(){     
                            var tgl_transaksi = $('input[name="tgl_transaksi"]').val(); 
                            $.getJSON("/admin/getcodesalespayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                                $('input[name="in_code_transaksi"]').val(results.code_data);
                                $('input[name="nomor_transaksi"]').val(results.code_data);
                            });
                        }

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-2y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });
                        
                        $('input[name="nomor_penjualan"]').keyup(function(){
                            var nomor_penjualan = $('input[name="nomor_penjualan"]').val();
                            if(nomor_penjualan == ''){
                                $('input[name="in_nomor_penjualan"]').val('');
                                $('input[name="nama_customer"]').val('Nama Customer');
                                $('input[name="jumlah_piutang"]').val('0');
                                $('input[name="jumlah_bayar"]').val('0');
                                $('input[name="sisa_piutang"]').val('0');
                                $('input[name="pembayaran"]').val('0');
                            }
                        });
                        
                        $('[name="nomor_penjualan"]').autocomplete({
                            minLength:1,
                            source:"/admin/listsalespayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>",
                            autoFocus: true,
                            select:function(event, val){
                                if(val.item.code_data != undefined){
                                    var nomor_penjualan = val.item.code_data;
                                    if(nomor_penjualan == ''){
                                        $('input[name="in_nomor_penjualan"]').val('');
                                        $('input[name="nama_customer"]').val('Nama Customer');
                                        $('input[name="jumlah_piutang"]').val('0');
                                        $('input[name="jumlah_bayar"]').val('0');
                                        $('input[name="sisa_piutang"]').val('0');
                                        $('input[name="pembayaran"]').val('0').prop({disabled:true});
                                    }else{
                                        $.getJSON("/admin/detailsalespayment?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&code_data="+nomor_penjualan, function(results){
                                            $('input[name="nama_customer"]').val(results.results.detail_customer.nama);                                            
                                            $('input[name="jumlah_piutang"]').val(results.results.detail_piutang.jumlah);                                           
                                            $('input[name="jumlah_bayar"]').val(results.results.detail_piutang.bayar);                                           
                                            $('input[name="sisa_piutang"]').val(results.results.detail_piutang.sisa);
                                            $('input[name="pembayaran"]').val('').prop({disabled:false}).focus();
                                        });
                                        $('input[name="in_nomor_penjualan"]').val(nomor_penjualan);
                                    }
                                }
                            }
                        });
                        
                        $('input[name="pembayaran"]').keyup(function(){
                            var pembayaran = parseFloat($('input[name="pembayaran"]').val());
                            var sisa_piutang = parseFloat($('input[name="sisa_piutang"]').val());
                            if(pembayaran > sisa_piutang){
                                // alert('Pembayaran tidak boleh melebihi piutang!');
                                $('button[name="btn_save"]').prop({disabled:true});
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Data pembayaran melebihi nilai sisa piutang Rp.' + sisa_piutang + '.</div>');
                                $('button[btn-action="close-confirmasi"]');
                                $('button[btn-action="close-confirmasi"]').click(function(){
                                    if($('button[btn-action="close-confirmasi"]').click){ 
                                        $('input[name="pembayaran"]').val(sisa_piutang);
                                        $('button[name="btn_save"]').prop({disabled:false});
                                    }
                                });
                            }else{
                                $('button[name="btn_save"]').prop({disabled:false});
                            }
                        });
                            
                        $('button[name="btn_save"]').click(function(){
                            var nomor_penjualan = $('input[name="nomor_penjualan"]').val();
                            if($(('button[name="btn_save"]')).click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan pembayaran penjualan ' + nomor_penjualan + '. <br>Setelah simpan maka data tidak bisa diubah kembali.</div>');
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

                </script> -->

                <script>
                    $(function () {

                        // INIT STATE
                        const el = {
                            tgl: $('input[name="tgl_transaksi"]'),
                            tglHidden: $('input[name="in_tgl_transaksi"]'),
                            nomorTransaksi: $('input[name="nomor_transaksi"]'),
                            kode: $('input[name="code_data"]'),
                            kodeHidden: $('input[name="in_code_transaksi"]'),
                            nomorPenjualan: $('input[name="nomor_penjualan"]'),
                            nomorPenjualanHidden: $('input[name="in_nomor_penjualan"]'),
                            customer: $('input[name="nama_customer"]'),
                            piutang: $('input[name="jumlah_piutang"]'),
                            bayar: $('input[name="jumlah_bayar"]'),
                            sisa: $('input[name="sisa_piutang"]'),
                            pembayaran: $('input[name="pembayaran"]'),
                            btnSave: $('button[name="btn_save"]')
                        };

                        // DEFAULT VALUE
                        const today = '{{ date("Y-m-d") }}';

                        el.tgl.val(today);
                        el.tglHidden.val(today);
                        el.nomorTransaksi.val('{{$code_data}}');
                        el.kode.val('{{$code_data}}');
                        el.kodeHidden.val('{{$code_data}}');

                        disablePembayaran(true);
                        toggleSave(false);

                        // DATEPICKER
                        el.tgl.datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: '-2y',
                            endDate: '0d',
                            autoclose: true,
                            orientation: "bottom"
                        }).on('changeDate', function () {
                            let val = el.tgl.val();
                            el.tglHidden.val(val);
                            generateKode();
                        });

                        // GENERATE CODE
                        function generateKode() {
                            $.getJSON(`/admin/getcodesalespayment`, {
                                token: '{{ $request["token"] }}',
                                u: '{{ $request["u"] }}',
                                tgl_transaksi: el.tgl.val()
                            }, function (res) {
                                el.kode.val(res.code_data);
                                el.kodeHidden.val(res.code_data);
                                el.nomorTransaksi.val(res.code_data);
                            });
                        }

                        // AUTOCOMPLETE SO
                        el.nomorPenjualan.autocomplete({
                            minLength: 1,
                            source: `/admin/listsalespayment?token={{ $request['token'] }}&u={{ $request['u'] }}`,
                            select: function (event, ui) {
                                let kode = ui.item.code_data;

                                if (!kode) return resetForm();

                                el.nomorPenjualanHidden.val(kode);

                                $.getJSON(`/admin/detailsalespayment`, {
                                    token: '{{ $request["token"] }}',
                                    u: '{{ $request["u"] }}',
                                    code_data: kode
                                }, function (res) {

                                    let data = res.results;

                                    el.customer.val(data.detail_customer.nama);
                                    el.piutang.val(data.detail_piutang.jumlah);
                                    el.bayar.val(data.detail_piutang.bayar);
                                    el.sisa.val(data.detail_piutang.sisa);

                                    el.pembayaran.val('').prop('disabled', false).focus();
                                });
                            }
                        });

                        // INPUT PEMBAYARAN
                        el.pembayaran.on('keyup', function () {

                            let bayar = parseFloat(el.pembayaran.val()) || 0;
                            let sisa = parseFloat(el.sisa.val()) || 0;

                            if (bayar <= 0) {
                                toggleSave(false);
                                return;
                            }

                            if (bayar > sisa) {
                                showWarning(`Pembayaran melebihi sisa piutang Rp.${sisa}`);
                                el.pembayaran.val(sisa);
                                toggleSave(true);
                                return;
                            }

                            toggleSave(true);
                        });

                        // BUTTON SAVE
                        el.btnSave.on('click', function () {
                            let nomor = el.nomorPenjualan.val();
                            showConfirm(
                                `Anda yakin untuk simpan pembayaran penjualan ${nomor}? <br>Setelah simpan maka data tidak bisa diubah kembali!`,
                                function () {
                                    $('form[name="form_data"]').submit();
                                }
                            );
                        });

                        // RESET FORM
                        function resetForm() {
                            el.nomorPenjualanHidden.val('');
                            el.customer.val('Nama Customer');
                            el.piutang.val('0');
                            el.bayar.val('0');
                            el.sisa.val('0');
                            disablePembayaran(true);
                        }

                        // HELPERS
                        function toggleSave(state) {
                            el.btnSave.prop('disabled', !state);
                        }

                        function disablePembayaran(state) {
                            el.pembayaran.val('0').prop('disabled', state);
                        }

                        function showWarning(msg) {
                            $('div[data-model="confirmasi"]').modal({backdrop: false});
                            $('div[data-model="confirmasi"] .modal-body')
                                .html(`<div class="alert alert-warning">${msg}</div>`);
                        }

                        function showConfirm(msg, callback) {
                            let modal = $('div[data-model="confirmasi"]');

                            modal.modal({ backdrop: false });

                            modal.find('.modal-body')
                                .html(`<div class="alert alert-warning">${msg}</div>`);

                            // HAPUS tombol lama (anti double)
                            modal.find('#btnConfirm').remove();

                            // TAMBAH tombol baru
                            modal.find('[btn-action="close-confirmasi"]')
                                .before(`<button class="btn btn-primary btn-sm" id="btnConfirm">Yakin</button>`);

                            // EVENT TIDAK DOUBLE
                            $('#btnConfirm').off('click').on('click', function () {
                                callback();
                            });
                        }

                    });
                </script>
            @endsection

@endsection