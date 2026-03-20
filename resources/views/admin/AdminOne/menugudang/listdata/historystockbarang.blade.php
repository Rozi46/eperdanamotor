@extends('admin.AdminOne.layout.assets')
@section('title', 'History Stock Barang')
@section('content')
            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd" >
                            <div class="col-md-12 hd_page_main">History Stock Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										
										@if($level_user['exporthistorystockbarang'] == 'Yes')  
                                        <button type="button" class="btn btn-info back" onclick="exportdata('exporthistorystockbarang')"><i class="fa fa-download"></i> Export Data</button>
										@endif
                                    </div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt" style="padding-bottom: 2px;" >
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
										<input type="text" name="datefilterstart" placeholder="Dari tanggal" style="width: 90px; text-align:center; padding-left: 0px; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterstart)->format('d M Y'); ?>"/> 
										- <input type="text" name="datefilterend" placeholder="Sampai tanggal" style="width: 90px; text-align: center; padding-left: 0px; cursor: pointer;" readonly="" value="<?php echo Date::parse($datefilterend)->format('d M Y'); ?>" />
                                        <select name="nama_gudang" placeholder="Nama Gudang" style="width: 240px; padding-top: 8px; padding-bottom: 6px;">
                                            <option value="" {{ empty($nama_gudang) ? 'selected' : '' }}>Semua Gudang</option>
                                            @foreach ($list_gudang as $view_data)
                                                <option value="{{ $view_data['id'] }}" {{ $nama_gudang == $view_data['id'] ? 'selected' : '' }}>
                                                    {{ $view_data['nama'] }}
                                                </option>
                                            @endforeach
                                        </select>
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
													<th style="min-width:250px; text-align: center;">Nama Barang</th>
													<th style="min-width:100px; text-align: center;">Satuan</th>
													<th style="min-width:100px; text-align: center;">Stock Awal</th>
													<th style="min-width:100px; text-align: center;">Masuk</th>
													<th style="min-width:100px; text-align: center;">Keluar</th>
													<th style="min-width:100px; text-align: center;">Stock Akhir</th>
												</tr>
											</thead>
											<tbody>
												<?php $bg_one = "#FFFFFF"; $bg_two = "#F1F1F1"; $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
                                                    <?php
                                                        $css_in = '';
                                                        $css_out = '';
                                                        if($listdata['stock_masuk'][$view_data['kode_barang']] > 0){
                                                            $css_in = 'background-color: #932C25; color: #FFFFFF;';
                                                        }

                                                        if($listdata['stock_keluar'][$view_data['kode_barang']] > 0){
                                                            $css_out = 'background-color: #3789C1; color: #FFFFFF;';
                                                        }

														$bg = (($no % 2) == 1) ? $bg_two : $bg_one;
                                                    ?>

													<tr>
														<td style="text-align:center;">{{$no}}</td>
														<td style="text-align:left;">
															<div class="alert alert-primary" style="display: inline-block; margin: 1px auto 0; text-align: Lev; font-size: 14px; padding: 2px;" title="{{$view_data['nama_barang']}}">
																<strong>{{ $view_data['nama_barang'] ?? 'Belum Ditentukan' }}</strong>
															</div>
                                                            <small>                                                               
                                                                <br>Kategori : {{ $listdata['kategori'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan' }}<br>
																Merk : {{ $listdata['merk'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan'}}<br>
                                                                Supllier : {{ $listdata['supplier'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan'}}<br>
                                                            </small>
														</td>
                                                        
                                                        <td style="text-align:center;">{{ $view_data['nama'] ?? 'Belum Ditentukan' }}</td>
                                                        
                                                        <td style="text-align:center;">{{ number_format($listdata['stock_awal'][$view_data['kode_barang']]  ?? 0,2,",",".") }}</td>

														<td style="text-align:center; <?php echo $css_in; ?>">{{ number_format($listdata['stock_masuk'][$view_data['kode_barang']] ??  0,2,",",".") }}</td>

														<td style="text-align:center; <?php echo $css_out; ?>">{{ number_format($listdata['stock_keluar'][$view_data['kode_barang']] ??  0,2,",",".") }}</td>
                                                        
                                                        <td style="text-align:center;">{{ number_format($listdata['stock_akhir'][$view_data['kode_barang']] ??  0,2,",",".") }}</td>
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

                        $('[line="btn_page_awal"]').attr('href','/admin/historystockbarang?td={{$request['d']}}&nama_gudang={{ $nama_gudang }}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_min"]').attr('href','/admin/historystockbarang?td={{$request['d']}}&nama_gudang={{ $nama_gudang }}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/historystockbarang?td={{$request['d']}}&nama_gudang={{ $nama_gudang }}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/historystockbarang?td={{$request['d']}}&nama_gudang={{ $nama_gudang }}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}&{{ $searchdate ?? '' }}');
                    });

					function datefilter() {
						loadingpage(2000);
						var key_search = encodeURIComponent($('input[name="key-search"]').val());
						var datefilterstart = $('input[name="datefilterstart"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						
                        var nama_gudang = $('select[name="nama_gudang"]').val();

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
							window.location.href = "/admin/historystockbarang?d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&nama_gudang="+nama_gudang+"&keysearch="+key_search+"&searchdate="+datefilterstart+"sd"+datefilterend;
						}else{
							window.location.reload();
						}
					}

					function exportdata(namedata) {
						var datefilterstart = $('input[name="datefilterstart"]');
						var datefilterstart = new Date(datefilterstart.val());
						var curr_date_datefilterstart = datefilterstart.getDate();
						var curr_month_datefilterstart = datefilterstart.getMonth() + 1;
						
                        var nama_gudang = $('select[name="nama_gudang"]').val();

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

						window.location.href = "/admin/exporthistorystockbarang?page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&nama_gudang="+nama_gudang+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val())+searchdate;
					}
                </script>
            @endsection
@endsection