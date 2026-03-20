@extends('admin.AdminOne.layout.assets')
@section('title', 'History Penjualan')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">History Penjualan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['inputpenjualanbarang'] == 'Yes')<a href="/admin/menupenjualanbarang"><button type="button" class="btn btn-primary">Input Penjualan</button></a>@endif
										
										@if($level_user['exportpenjualanbarang'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata()"><i class="fa fa-download"></i> Export Data</button>@endif
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
						<!-- <div class="col-md-12 bg_page_main form_action dt"> -->
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
													<th style="min-width:140px; text-align: center;">Tanggal</th>
													<th class="colleft" style="min-width:150px; text-align: center;">No. Penjualan</th>
													<th style="min-width:170px; text-align: center;">Penjualan Oleh</th>
													<th style="min-width:170px; text-align: center;">Nama Mekanik</th>
													<th style="min-width:260px; text-align: center;">Nama Customer</th>

													<th style="min-width:300px; text-align: center;">Nama Produk</th>
													<th style="width:100px; text-align: center;">Jumlah</th>
													<th style="width:100px; text-align: center;">Satuan</th>
													<th style="min-width:150px; text-align: center;">Harga</th>
													<th style="min-width:150px; text-align: center;">Diskon Barang (-)</th>
													<th style="min-width:150px; text-align: center;">Harga Netto</th>
													<th style="min-width:150px; text-align: center;">Total Harga</th>

													<!-- <th style="min-width:125px; text-align: center;">DPP</th>
													<th style="min-width:125px; text-align: center;">PPN</th> -->
													<th style="min-width:125px; text-align: center;">Sub Total</th>
													<th style="min-width:125px; text-align: center;">Diskon (-)</th>
													<th style="min-width:125px; text-align: center;">Biaya Kirim (+)</th>
													<th style="min-width:125px; text-align: center;">Grand Total</th>
													<th style="min-width:125px; text-align: center;">Ket</th>
													<th style="min-width:125px; text-align: center;">Gudang</th>
													<th style="width:120px; text-align: center;">Status</th>
													<th class="colright" style="min-width:30px; text-align: center;"><i class="head fa fa-cog"></i></th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) 
													<?php 
														$no++;
														$produk_list = $listdata['list_produk'][$view_data['nomor']] ?? [];
														$rowspan = count($produk_list) > 0 ? count($produk_list) : 1;
													?>					
                                                    <script>
                                                        function viewdata_{{$no}}() {
                                                            loadingpage(2000);
                                                            window.location.href = "/admin/viewpenjualan?d={{$view_data['nomor']}}";
                                                        }
                                                        <?php if($view_data['kode_user'] != null && $view_data['status_transaksi'] != 'Proses'){?>
                                                            function printdata_{{$no}}() {
                                                                window.open("/admin/printsalesorder?d={{$view_data['nomor']}}");
                                                            }
                                                        <?php } ?>
                                                    </script>
													@if(count($produk_list) > 0)
														@foreach($produk_list as $index => $view_produk)
															<tr>
																@if($index === 0)
																	<td style="text-align:center;" rowspan="{{ $rowspan }}">{{$no}}
																	</td>
																	<td rowspan="{{ $rowspan }}">{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
																	<td class="colleft link" rowspan="{{ $rowspan }}" style="text-align:center;" title="Detail" onclick="viewdata_{{$no}}()">{{ $view_data['nomor'] ?? 'Belum Ditentukan'}}</td>
																	<td rowspan="{{ $rowspan }}">{{ $view_data['nama_user_input'] ?? 'Belum Ditentukan' }}</td>
																	<td style="text-align:left;" rowspan="{{ $rowspan }}">{{ (!empty($listdata['detail_mekanik'][$view_data['code_data']]) ? implode(', ', array_column($listdata['detail_mekanik'][$view_data['code_data']], 'nama')) : 'Belum Ditentukan') }}</td>
																	<td rowspan="{{ $rowspan }}">{{ $listdata['detail_customer'][$view_data['code_data']]['nama'] ?? 'Belum ditentukan'}}</td>
																@endif

																<td>{{ $listdata['detail_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
																<td style="text-align:center;">{{ number_format($view_produk['jumlah_jual'] ?? 0,2,",",".") }}</td>
																<td style="text-align:center;">{{ $listdata['satuan_barang_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
																<td style="text-align:right;">{{ number_format($view_produk['harga'] ?? 0,2,",",".") }}</td>
																<td style="text-align:center;">{{ number_format($view_produk['diskon_harga'] ?? 0,2,",",".") }} + {{ number_format($view_produk['diskon_harga2'] ?? 0,2,",",".") }}</td>
																<td style="text-align:right;">{{ number_format($view_produk['harga_netto'] ?? 0,2,",",".") }}</td>
																<td style="text-align:right;">{{ number_format($view_produk['total_harga'] ?? 0,2,",",".") }}</td>

																@if($index === 0)
																	<!-- <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['sub_total'] ?? 0,2,",",".") }}</td> -->
																	<!-- <td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['ppn'] ?? 0,2,",",".") }}</td> -->
																	<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['total'] ?? 0,2,",",".") }}</td>
																	<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['diskon_harga'] ?? 0,2,",",".") }}</td>
																	<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['biaya_kirim'] ?? 0,2,",",".") }}</td>
																	<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['grand_total'] ?? 0,2,",",".") }}</td>
																	<td rowspan="{{ $rowspan }}">{{ $view_data['ket'] ?? 'Belum Ditentukan' }}</td>
																	<td rowspan="{{ $rowspan }}">{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
																	<td rowspan="{{ $rowspan }}" style="text-align:center;">
																		@if($view_data['status_transaksi'] == 'Proses')
																			<div class="alert alert-warning" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																				<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																			</div>
																		@elseif($view_data['status_transaksi'] == 'Pending')
																			<div class="alert alert-danger" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																				<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																			</div>
																		@else
																			<div class="alert alert-success" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																				<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																			</div>
																		@endif
																	</td>
																	<td rowspan="{{ $rowspan }}" <?php if($view_data['status_transaksi'] != 'Proses'){?>class="colright link"<?php }else{ ?> class="colright" <?php } ?>style="text-align: center; <?php if($view_data['kode_user'] == null && $view_data['status_transaksi'] == 'Proses' ){?>cursor: not-allowed;<?php } ?> " onclick="printdata_{{$no}}()"><i class="colright <?php if($view_data['kode_user'] != null && $view_data['status_transaksi'] != 'Proses'){?>white<?php }else{ ?>head<?php } ?> fa fa-print" title="Print" style="text-align: center; <?php if($view_data['kode_user'] == null or $view_data['status_transaksi'] == 'Proses' ){?>cursor: not-allowed;<?php } ?> "></i></td>
																@endif
															</tr>
														@endforeach
													@else
														<tr>
															<td style="text-align:center;">{{$no}}</td>
															<td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
															<td class="colleft link" style="text-align:center;" title="Detail"  onclick="viewdata_{{$no}}()">{{ $view_data['nomor'] ?? 'Belum Ditentukan' }}</td>
															<td style="text-align:left;">{{ $view_data['nama_user_input'] ?? 'Belum Ditentukan' }}</td>
															<td style="text-align:left;">{{ $listdata['detail_customer'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
															<td colspan="7" style="text-align:center;">Tidak ada data yang tersedia</td>
															<!-- <td style="text-align:right;">{{ number_format($view_data['sub_total'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">{{ number_format($view_data['ppn'] ?? 0,2,",",".") }}</td> -->
															<td style="text-align:right;">{{ number_format($view_data['total'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">{{ number_format($view_data['diskon_harga'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">{{ number_format($view_data['biaya_kirim'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">{{ number_format($view_data['grand_total'] ?? 0,2,",",".") }}</td>
															<td>{{ $view_data['ket'] ?? 'Belum Ditentukan' }}</td>
															<td>{{ $listdata['detail_gudang'][$view_data['code_data']]['nama'] ?? 'Belum Ditentukan' }}</td>
															<td style="text-align:center;">
																@if($view_data['status_transaksi'] == 'Proses')
																	<div class="alert alert-warning" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																		<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																	</div>
																@elseif($view_data['status_transaksi'] == 'Pending')
																	<div class="alert alert-danger" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																		<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																	</div>
																@else
																	<div class="alert alert-success" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																		<strong>{{ $view_data['status_transaksi'] ?? 'Belum Ditentukan'}}</strong>
																	</div>
																@endif
															</td>
															<td <?php if($view_data['status_transaksi'] != 'Proses'){?>class="colright link"<?php }else{ ?> class="colright" <?php } ?>style="text-align: center; <?php if($view_data['kode_user'] == null && $view_data['status_transaksi'] == 'Proses' ){?>cursor: not-allowed;<?php } ?> " onclick="printdata_{{$no}}()"><i class="colright <?php if($view_data['kode_user'] != null && $view_data['status_transaksi'] != 'Proses'){?>white<?php }else{ ?>head<?php } ?> fa fa-print" title="Print" style="text-align: center; <?php if($view_data['kode_user'] == null or $view_data['status_transaksi'] == 'Proses' ){?>cursor: not-allowed;<?php } ?> "></i></td>
														</tr>
													@endif
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
						
                        $('[line="btn_page_awal"]').attr('href','/admin/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_min"]').attr('href','/admin/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/historypenjualanbarang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');
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
							// var datefilterstart = "2021-01-01";
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
							window.location.href = "/admin/historypenjualanbarang?tp="+tipe_data+"&d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search+"&searchdate="+datefilterstart+"sd"+datefilterend;
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
						window.location.href = "/admin/exportpenjualanbarang?tp="+tipe_data+"&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate;
					}
                </script>
            @endsection
@endsection