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
                                        @endif
                                        @if($level_user['inputpenerimaankas'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save">Simpan Data</button>
                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main form_action" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/saveppenerimaankas">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$code_data}}" readonly="true" style="display:none;" />
										<input type="text" name="in_code_transaksi" value="" readonly="true" style="display:none;" />
										<input type="text" name="in_tgl_transaksi" value="" readonly="true" style="display:none;" />

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
                                                    <select name="akun_biaya" placeholder="Akun biaya">
                                                        <option value="Modal Awal" selected="true">Modal Awal</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="jumlah" class="col-sm-4 col-form-label">Jumlah <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="jumlah" placeholder="0" value="" onKeyPress="return goodchars(event,'0123456789,',this)"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-2 col-form-label">Keterangan  <span>*</span></label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="">
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
                        $('select[name="akun_biaya"] option[value="Modal Awal"]').prop("selected", true);
                        $('input[name="jumlah"]').val('0').focus();
                        $('input[name="keterangan"]').val('');
                        $('button[name="btn_save"]').prop({disabled:true});


                        $('input[name="keterangan"]').keyup(function(){
                            var keterangan = $('input[name="keterangan"]').val();
                            if(keterangan != ''){
                                $('button[name="btn_save"]').prop({disabled:false});
                            }else{
                                $('button[name="btn_save"]').prop({disabled:true});
                            }
                        });
                        
                        $('[onclick="BackPage()"]').prop({disabled:false});
                        $('[btn="history_data"]').prop({disabled:false});

                        $('input[name="in_code_transaksi"]').val('{{$code_data}}');
                        $('input[name="code_transaksi"]').val('{{$code_data}}');

                        // $('input[name="in_tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');
                        // $('input[name="tgl_transaksi"]').val('<?php echo Date::parse(date("d F Y"))->add(0, 'day')->format('d F Y'); ?>');

                        $('input[name="tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');
                        $('input[name="in_tgl_transaksi"]').val('{{ now()->format("Y-m-d") }}');

                        // $('input[name="tgl_transaksi"]').change(function(){
                        //     var value = $('input[name="tgl_transaksi"]').val();
                        //     $('input[name="in_tgl_transaksi"]').val(value);
                        //     getcodepay();
                        // });

                        // $('input[name="tgl_transaksi"]').change(function(){
                        //     var value = $(this).val().trim();
                        //     var bulan = {
                        //         'Januari':'01',
                        //         'Februari':'02',
                        //         'Maret':'03',
                        //         'April':'04',
                        //         'Mei':'05',
                        //         'Juni':'06',
                        //         'Juli':'07',
                        //         'Agustus':'08',
                        //         'September':'09',
                        //         'Oktober':'10',
                        //         'November':'11',
                        //         'Desember':'12'
                        //     };

                        //     var split = value.split(/\s+/);
                        //     var hari  = split[0];
                        //     var bulanStr = split[1];
                        //     var tahun = split[2];
                        //     var bulanNum = bulan[bulanStr];

                        //     if(!bulanNum){
                        //         console.error('Format bulan tidak dikenali:', bulanStr);
                        //         return;
                        //     }

                        //     var formatTanggal = tahun+'-'+bulanNum+'-'+hari.padStart(2,'0');
                        //     $('input[name="in_tgl_transaksi"]').val(formatTanggal);

                        //     getcodepay();
                        // });
        
                        function getcodepay(){     
                            var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val(); 
                            $.getJSON("/admin/getcodepay?token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&tgl_transaksi="+tgl_transaksi, function(results){
                                $('input[name="in_code_transaksi"]').val(results.code_data);
                                $('input[name="nomor_transaksi"]').val(results.code_data);
                            });
                        }

                        $('input[name="tgl_transaksi"]').datepicker({
                            format: 'yyyy-mm-dd',
                            startDate: '-2y',
                            endDate: '0d',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });
                            
                        $('button[name="btn_save"]').click(function(){
                            var nomor_transaksi = $('input[name="nomor_transaksi"]').val();
                            if($(('button[name="btn_save"]')).click){
                                $('div[data-model="confirmasi"]').modal({backdrop: false});
                                $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">Anda yakin untuk simpan penerimaan kas ' + nomor_transaksi + '. <br>Setelah simpan maka data tidak bisa diubah kembali.</div>');
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