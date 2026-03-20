@extends('admin.AdminOne.cashier.layout.assets')
@section('title', 'History Penjualan')

@section('content')
            <div class="page_main_full">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">History Penjualan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
                                        <a href="dash"><button type="button" class="btn btn-primary">Input Penjualan</button></a>
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
										<button type="button" class="btn btn-default filter" onclick="datefilter()" >Filter</button>
									</div>
								</div>
							</div>
							<div class="col-md-12 data_page">
								<div class="row bg_data_page">
									<div class="table_data freezeHead freezeCol">
										<table class="table_view table-striped table-hover">
											<thead>
												<tr>
													<th style="min-width:30px; text-align: center;">No</th>
													<th style="min-width:140px; text-align: center;">Tanggal</th>
													<th class="colleft" style="min-width:150px; text-align: center;">No. Penjualan</th>
													<th style="min-width:150px; text-align: center;">Nama Customer</th>
													<th style="min-width:250px; text-align: center;">Nama Barang</th>
													<th style="min-width:110px; text-align: center;">Qty Penjualan</th>
													<th style="min-width:100px; text-align: center;">Qty Dikirim</th>
													<th style="min-width:120px; text-align: center;">Harga Jual</th>
													<th style="min-width:120px; text-align: center;">Diskon Barang</th>
													<th style="min-width:120px; text-align: center;">Sub Total</th>
													<th style="min-width:120px; text-align: center;">PPN</th>
													<th style="min-width:120px; text-align: center;">Total</th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<script>
														function viewdata_{{$no}}() {
															loadingpage(2000);
															window.location.href = "/cash/viewpenjualan?d={{$listdata['detail_penjualan'][$view_data['id']]['nomor']}}";
														}
													</script>
													<tr>
														<td style="text-align:center;">{{$no}}
                                                        </td>
														<td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
														<td>{{ $view_data['nomor'] ?? 'Belum Ditentukan' }}</td>
                                                        <td>{{ $listdata['detail_customer'][$view_data['id']]['nama'] ?? 'Belum Ditentukan' }}</td>
														<td style="text-align:left;">{{ $listdata['produk'][$view_data['id']]['nama'] ?? 'Belum Ditentukan'}}</td>
														<td style="text-align:center;">{{ number_format($view_data['jumlah_jual'] ?? 0,0,"",".") }} {{ $listdata['satuan_prod'][$view_data['id']]['nama'] ?? 'Belum Ditentukan' }}</td>
                                                        <td style="text-align:center;">{{ number_format($view_data['jumlah_kirim'] ?? 0,0,"",".") }} {{ $listdata['satuan_prod'][$view_data['id']]['nama'] ?? 'Belum Ditentukan' }}</td>
														<td style="text-align:right;">{{ number_format($view_data['harga'] ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($view_data['diskon_harga'] ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($view_data['total_harga']-$view_data['diskon_harga'] ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($view_data['ppn'] ?? 0,2,",",".") }}</td>
														<td style="text-align:right;">{{ number_format($view_data['total_harga']-$view_data['diskon_harga']-$view_data['ppn'] ?? 0,2,",",".") }}</td>
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
                            loadingpage(2000);
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

                        // $('input[name="datefilterstart"]').change(function(e){
                        //     datefilter();
                        // });
						
                        // $('input[name="datefilterend"]').change(function(e){
                        //     datefilter();
                        // });

                        $('input[id="countvd"]').keyup(function(e){
                            if(e.keyCode == 13) {
                                datefilter();
                            }
						});

                        $('input[id="countvd"]').change(function(e){
                            datefilter();
						});
						
                        $('[line="btn_page_awal"]').attr('href','/cash/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_min"]').attr('href','/cash/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_plus"]').attr('href','/cash/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

						$('[line="btn_page_akhir"]').attr('href','/cash/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');
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
							var datefilterstart = "2021-01-01";
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
							window.location.href = "/cash/historypenjualanbarang?tp="+tipe_data+"&d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search+"&searchdate="+datefilterstart+"sd"+datefilterend;
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
						window.location.href = "/cash/exportpenjualanbarang?tp="+tipe_data+"&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate;
					}
                </script>
            @endsection
@endsection