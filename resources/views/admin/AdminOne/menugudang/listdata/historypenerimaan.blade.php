@extends('admin.AdminOne.layout.assets')
@section('title', 'History Penerimaan')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">History Penerimaan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['inputpenerimaanbarang'] == 'Yes')<a href="/admin/menupenerimaanbarang"><button type="button" class="btn btn-primary">Input Penerimaan</button></a>@endif
										
										@if($level_user['exportpenerimaanbarang'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('penerimaanbarang')"><i class="fa fa-download"></i> Export Data</button>@endif
									</div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt" style="padding-bottom: 2px;">
							<div class="col-md-12 bg_act_page_main page">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-right">
										@include('admin.AdminOne.layout.pagination')
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main dt">
							<div class="col-md-12 bg_act_page_main page">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left" style="margin-bottom:6px;">
										Filter Data 
										<select name="tipe_data" placeholder="Tipe Penjualan" style="padding-top: 8px; padding-bottom: 6px;">
											<option value="transaksi">Per Transaksi</option>
											<option value="item">Per Barang</option>
										</select>
										<input type="text" name="datefilterstart" placeholder="Dari tanggal" style="width: 90px; text-align: padding-left: 0px; center; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterstart)->format('d M Y'); ?>"/> 
										- <input type="text" name="datefilterend" placeholder="Sampai tanggal" style="width: 90px; text-align: center; padding-left: 0px; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterend)->format('d M Y'); ?>" />
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
													<th style="width:160px; text-align: center;">Tanggal</th>
													<th class="colleft" style="width:200px; text-align: center;">No. Penerimaan</th>
                                                    <th style="min-width:175px; text-align: center;">Nama Supplier</th>
                                                    <th style="min-width:175px; text-align: center;">Pembelian Oleh</th>
													<th style="width:125px; text-align: center;">No. Pembelian</th>
													<th style="width:250px; text-align: center;">Keterangan</th>
													<th style="width:100px; text-align: center;">Qty</th>
													<th style="width:150px; text-align: center;">Gudang</th>
													<th style="width:120px; text-align: center;">Status</th>
													<th class="colright" style="min-width:30px; text-align: center;"><i class="head fa fa-cog"></i></th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<script>
														function viewdata_{{$no}}() {
															loadingpage(2000);
															window.location.href = "/admin/viewpenerimaan?d={{$view_data['nomor_penerimaan']}}";
														}
                                                        <?php if($view_data['status_transaksi'] != 'Proses'){?>
                                                            function printdata_{{$no}}() {
                                                                window.open("/admin/printpenerimaan?d={{$view_data['nomor_penerimaan']}}");
                                                            }
                                                        <?php } ?>
													</script>
													<tr>
														<td style="text-align:center;">{{$no}}
                                                            <br/><?php if($res_user['level'] == 'LV5677001'){?> <i class="fa fa-building" title="{{$listdata['detail_perusahaan'][$view_data['nomor_penerimaan']]['kantor']}} | {{$listdata['detail_cabang'][$view_data['nomor_penerimaan']]['nama_cabang']}}"></i><?php } ?>
                                                        </td>
														<td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
														<td class="colleft link" style="text-align:center;" title="Detail"  onclick="viewdata_{{$no}}()">{{ $view_data['nomor_penerimaan'] ?? 'Belum Ditentukan' }}</td>
                                                        <td>{{ $listdata['detail_supplier'][$view_data['nomor_penerimaan']]['nama'] ?? 'Belum Ditentukan' }}</td>
                                                        <td>{{ $listdata['user_pembelian'][$view_data['nomor_penerimaan']]['full_name'] ?? 'Belum Ditentukan'}}</td>
                                                        <td>{{ $listdata['detail_pembelian'][$view_data['nomor_penerimaan']]['nomor'] ?? 'Belum Ditentukan'}}</td>
														<td style="text-align:left;">{{ $view_data['ket'] ?? 'Belum Ditentukan'}}</td>
														<td style="text-align:center;">{{ number_format($listdata['qty_penerimaan'][$view_data['nomor_penerimaan']] ?? 0,0,"",".") }}</td>
                                                        <td>{{ $listdata['detail_gudang'][$view_data['nomor_penerimaan']]['nama'] ?? 'Belum Ditentukan' }}</td>
														<td style="text-align:center;">{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan' }}</td>
														
													    <td <?php if($view_data['status_transaksi'] != 'Proses'){?>class="link"<?php } ?> style="text-align: center; <?php if($view_data['status_transaksi'] == 'Proses'){?>cursor: not-allowed;<?php } ?> " onclick="printdata_{{$no}}()"><i class="<?php if($view_data['status_transaksi'] != 'Proses'){?>white<?php }else{ ?>head<?php } ?> fa fa-print" title="Print" style="text-align: center;"></i></td>
													</tr>
												@empty
													<tr>
														<td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; font-size: 14px;" colspan="20">Tidak ada data yang tersedia</td>
													</tr>
												@endforelse
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
                        $('select[name="tipe_data"]').change(function(){
                            datefilter();
                        });
                        var tipe_data = $('select[name="tipe_data"]').val();
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
						
                        $('[line="btn_page_awal"]').attr('href','/admin/historypenerimaan?tp='+tipe_data+'&d={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_min"]').attr('href','/admin/historypenerimaan?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/historypenerimaan?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/historypenerimaan?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');
                    });

					function datefilter() {
						loadingpage(2000);
						var key_search = encodeURIComponent($('input[name="key-search"]').val());
						var tipe_data = $('select[name="tipe_data"]').val();
						var datefilterstart = $('input[name="datefilterstart"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						if (curr_month_datefilterstart < 10){
							var curr_month_datefilterstart = '0'+curr_month_datefilterstart;
						}
						var curr_year_datefilterstart = datefilterstart.getFullYear();
						if(key_search != ''){
							// var datefilterstart = "2020-01-01";
                            var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;
						}else{
							var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;
						}

						var datefilterend = $('input[name="datefilterend"]');
						var datefilterend = new Date(datefilterend.val());
						var curr_date_datefilterend = datefilterend.getDate();
						var curr_month_datefilterend = datefilterend.getMonth() + 1;
						if (curr_month_datefilterend < 10){
							var curr_month_datefilterend = '0'+curr_month_datefilterend;
						}
						var curr_year_datefilterend = datefilterend.getFullYear();
						var datefilterend = curr_year_datefilterend+"-"+curr_month_datefilterend+"-"+ curr_date_datefilterend;

						if($('input[name="datefilterstart"]').val() != '' && $('input[name="datefilterend"]').val() != ''){
							window.location.href = "/admin/historypenerimaan?tp="+tipe_data+"&d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search+"&searchdate="+datefilterstart+"sd"+datefilterend;
						}else{
							window.location.reload();
						}
					}

					function exportdata() {
						var tipe_data = $('select[name="tipe_data"]').val();
						var datefilterstart = $('input[name="datefilterstart"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						if (curr_month_datefilterstart < 10){
							var curr_month_datefilterstart = '0'+curr_month_datefilterstart;
						}
						var curr_year_datefilterstart = datefilterstart.getFullYear();
						var datefilterstart = curr_year_datefilterstart+"-"+curr_month_datefilterstart+"-"+ curr_date_datefilterstart;

						var datefilterend = $('input[name="datefilterend"]');
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
						window.location.href = "/admin/exportpenerimaanbarang?tp="+tipe_data+"&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate;
					}
                </script>
            @endsection
@endsection