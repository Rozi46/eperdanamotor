@extends('admin.AdminOne.layout.assets')
@section('title', 'History Transaksi Kas')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">History Transaksi Kas</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										
										@if($level_user['exportkas'] == 'Yes')
                                            <button type="button" class="btn btn-info back" onclick="exportdata('historykas')"><i class="fa fa-download"></i> Export Data</button>
										@endif                                        
											<!-- <button type="button" class="btn btn-secondary" onclick="printdata('historykas')"><i class="fa fa-print"></i> Print Data</button> -->
									</div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt">
							<div class="col-md-12 bg_act_page_main page">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										Filter Data 
										<input type="text" name="datefilterstart_full" placeholder="Dari tanggal" style="width: 90px; text-align: padding-left: 0px; center; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterstart)->format('d M Y'); ?>"/> 
										- <input type="text" name="datefilterend_full" placeholder="Sampai tanggal" style="width: 90px; text-align: center; padding-left: 0px; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterend)->format('d M Y'); ?>" />
                                        <input type="text" name="key-search" placeholder="Cari data..." style="width: 180px; text-align: left;" value="{{ $keysearch }}" />
										<button type="button" class="btn btn-default filter" onclick="datefilter()">Filter</button>
									</div>
								</div>
							</div>
							<div class="col-md-12 data_page">
								<div class="row bg_data_page">
									<div class="table_data freezeHead freezeCol">
										<table class="table_view table-striped table-hover">
											<thead>
												<tr>
													<th style="width:30px; text-align: center;">No</th>
													<th style="width:125px; text-align: center;">Tanggal</th>
													<th style="width:150px; text-align: center;">No. Voucher</th>
                                                    <th style="min-width:100px; text-align: center;">Uraian</th>
                                                    <th style="width:150px; text-align: center;">Saldo Awal</th>
                                                    <th style="width:150px; text-align: center;">Masuk</th>
                                                    <th style="width:150px; text-align: center;">Keluar</th>
                                                    <th style="width:150px; text-align: center;">Saldo Akhir</th>
												</tr>
											</thead>
											<tbody>
													<?php
														$css_in = 'background-color: #932C25; color: #FFFFFF;';
														$css_out = 'background-color: #3789C1; color: #FFFFFF;';
													?>
                                                <tr>
                                                    <td style="text-align: right; <?php echo $css_out; ?> cursor: default; font-size:16px; font-weight: 600;" colspan="4">Saldo Akhir <?php echo Date::parse($datefilterend)->format('d M Y'); ?> :</td>
                                                    <td style="text-align: right; <?php echo $css_out; ?> cursor: default; font-size:16px; font-weight: 600;" colspan="6"><?php echo number_format($listdata['saldo_akhir']['total'],2,",",".") ?></td>
                                                </tr>
                                                <!-- <?php $saldoawal = 0; $saldoakhir = 0; ?> -->
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php Date::setLocale('id'); $no++ ;?>
													<?php
														$masuk = $view_data['debet'];
														$keluar = $view_data['kredit'];

                                                        if($no == 1){														
															$e = $listdata['saldo_akhir']['total'];
															$saldoawal = $e + $keluar - $masuk;
															$saldoakhir = $saldoawal + $masuk - $keluar ;
                                                        }else{
															$saldoakhir = $saldoawal;
															$saldoawal = $saldoakhir + $keluar - $masuk;
                                                        }
													?>
													<tr>
														<td style="text-align:center;">{{$no}}
                                                            <br/><?php if($res_user['level'] == 'LV5677001'){?> <i class="fa fa-building" title="{{ $listdata['detail_perusahaan'][$view_data['id']]['kantor'] ?? 'Belum ditentukan' }}"></i><?php } ?>
                                                        </td>
														<td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
														<td style="text-align:center;">{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
                                                        <td>{{ $view_data['keterangan'] ?? 'Belum ditentukan' }}</td>
														<td style="text-align:right;">{{ number_format($saldoawal ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($masuk ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($keluar ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($saldoakhir ?? 0,2,",",".") }}</td>
													</tr>
												@empty
													<tr>
														<td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
													</tr>
												@endforelse
                                                <tr>
                                                    <td style="text-align: right; <?php echo $css_out; ?> cursor: default; font-size:16px; font-weight: 600;" colspan="4">Saldo Awal <?php echo Date::parse($datefilterstart)->format('d M Y'); ?> :</td>
                                                    <td style="text-align: right; <?php echo $css_out; ?> cursor: default; font-size:16px; font-weight: 600;" >{{ number_format($listdata['saldo_awal']['total'] ?? 0,2,",",".") }}</td>
                                                    <td style="text-align: right; background-color: #FFFFFF; cursor: default; font-size:16px; font-weight: 600;" colspan="3"></td>
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
                        $('select[name="tipe_data"] option[value="{{$request['tp']}}"]').prop("selected", true);

						var ak = $('select[name="ak"]').val();
						
                        $('select[name="ak"]').change(function(){
                            loadingpage(2000);
                            datefilter();
                        });
						
                        $('select[name="tipe_data"]').change(function(){
                            loadingpage(2000);
                            datefilter();
                        });

                        var tipe_data = $('select[name="tipe_data"]').val();

                        $('input[name="datefilterstart_full"]').datepicker({
                            format: 'dd M yyyy',
                            startDate: '-3y',
                            endDate: '0d',
                            autoclose : true,
                            orientation: "bottom"
                        });

                        $('input[name="datefilterend_full"]').datepicker({
                            format: 'dd M yyyy',
                            endDate: '0d',
                            autoclose : true,
                            orientation: "bottom"
                        });

                        $('input[name="key-search"]').keyup(function(e){
                            if(e.keyCode == 13) {
                                datefilter();
                            }
                        });
                        $('input[name="key-search"]').keyup(function(e){
                            if(e.keyCode == 13) {
                                datefilter();
                            }
                        });
                        $('input[name="key-search"]').change(function(e){
                            datefilter();
                        });

                        $('input[id="countvd"]').keyup(function(e){
                            if(e.keyCode == 13) {
                                datefilter();
                            }
						});

                        $('input[id="countvd"]').change(function(e){
                            datefilter();
						});
					});

					function datefilter() {
						var datefilterstart = $('input[name="datefilterstart_full"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						if (curr_month_datefilterstart < 10){
							var curr_month_datefilterstart = '0'+curr_month_datefilterstart;
						}
						var curr_year_datefilterstart = datefilterstart.getFullYear();
						var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;

						var datefilterend = $('input[name="datefilterend_full"]');
						var datefilterend = new Date(datefilterend.val());
						var curr_date_datefilterend = datefilterend.getDate();
						var curr_month_datefilterend = datefilterend.getMonth() + 1;
						if (curr_month_datefilterend < 10){
							var curr_month_datefilterend = '0'+curr_month_datefilterend;
						}
						var curr_year_datefilterend = datefilterend.getFullYear();
						var datefilterend = curr_year_datefilterend+"-"+curr_month_datefilterend+"-"+ curr_date_datefilterend;

						if($('input[name="datefilterstart_full"]').val() != '' && $('input[name="datefilterend_full"]').val() != ''){
							window.location.href = "/admin/historykas?keysearch="+encodeURIComponent($('input[name="key-search"]').val())+"&searchdate="+datefilterstart+"sd"+datefilterend;
						}else{
							window.location.reload();
						}
					}

					function exportdata(namedata) {
						var tipe_data = $('select[name="tipe_data"]').val();
						var ak = $('select[name="ak"]').val();
						var datefilterstart = $('input[name="datefilterstart_full"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						if (curr_month_datefilterstart < 10){
							var curr_month_datefilterstart = '0'+curr_month_datefilterstart;
						}
						var curr_year_datefilterstart = datefilterstart.getFullYear();
						var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;

						var datefilterend = $('input[name="datefilterend_full"]');
						var datefilterend = new Date(datefilterend.val());
						var curr_date_datefilterend = datefilterend.getDate();
						var curr_month_datefilterend = datefilterend.getMonth() + 1;
						if (curr_month_datefilterend < 10){
							var curr_month_datefilterend = '0'+curr_month_datefilterend;
						}
						var curr_year_datefilterend = datefilterend.getFullYear();
						var datefilterend = curr_year_datefilterend+"-"+curr_month_datefilterend+"-"+ curr_date_datefilterend;

						if(datefilterstart == 'NaN-NaN-NaN'){
							searchdate = "";
						}else{
							searchdate = "&searchdate="+datefilterstart+"sd"+datefilterend;
						}
						window.location.href = "/admin/exportkas?page=1&vd={{ $count_vd }}&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate;
					}

					function printdata(namedata) {
						var tipe_data = $('select[name="tipe_data"]').val();
						var ak = $('select[name="ak"]').val();
						var datefilterstart = $('input[name="datefilterstart_full"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						if (curr_month_datefilterstart < 10){
							var curr_month_datefilterstart = '0'+curr_month_datefilterstart;
						}
						var curr_year_datefilterstart = datefilterstart.getFullYear();
						var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;

						var datefilterend = $('input[name="datefilterend_full"]');
						var datefilterend = new Date(datefilterend.val());
						var curr_date_datefilterend = datefilterend.getDate();
						var curr_month_datefilterend = datefilterend.getMonth() + 1;
						if (curr_month_datefilterend < 10){
							var curr_month_datefilterend = '0'+curr_month_datefilterend;
						}
						var curr_year_datefilterend = datefilterend.getFullYear();
						var datefilterend = curr_year_datefilterend+"-"+curr_month_datefilterend+"-"+ curr_date_datefilterend;

						if(datefilterstart == 'NaN-NaN-NaN'){
							searchdate = "";
						}else{
							searchdate = "&searchdate="+datefilterstart+"sd"+datefilterend;
						}
						window.open("printdata"+namedata+"?tp="+tipe_data+"&ak="+ak+"&page=1&vd={{ $count_vd }}&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate);
					}
                </script>
            @endsection
@endsection