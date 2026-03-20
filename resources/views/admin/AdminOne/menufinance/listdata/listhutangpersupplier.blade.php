@extends('admin.AdminOne.layout.assets')
@section('title', 'Data Hutang')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Data Hutang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['menuhutang'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('hutang')"><i class="fa fa-download"></i> Export Data</button>@endif
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
											<option value="supplier">Per Supplier</option>
											<option value="item">Per Transaksi</option>
										</select>
										<!-- <button type="button" class="btn btn-default filter" onclick="datefilter()">Filter</button> -->
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
                                                    <th style="width:250px; text-align: center;">Nama Supplier</th>
													<th style="width:200px; text-align: center;">Total Hutang</th>
													@if($level_user['menuhutang'] == 'Yes')
														<th class="colright" style="width:30px; text-align: center;"><i class="head fa fa-cog"></i></th>
													@endif
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<tr>
														<td style="text-align:center;">{{$no}}</td>
                                                        <td>{{ $view_data['supplier_nama'] ?? 'Belum ditentukan' }} <br>
															<small>
																Jumlah Transaksi : {{ number_format($view_data['jumlah_pembelian'] ?? 0, 0, ',', '.') }}
															</small><br>
														</td>
														<td style="text-align:right;">{{ number_format($view_data['total_sisa'] ?? 0, 2, ',', '.') }}</td>

														@if($level_user['menuhutang'] == 'Yes')
															<td style="text-align:center;">
																<a load="true" href="kartuhutang?d={{$view_data['id_data']}}"><button type="button" class="btn btn-primary btn-sm">Kartu Hutang</button></a>
															</td>
														@endif
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

                        $('input[id="countvd"]').keyup(function(e){
                            if(e.keyCode == 13) {
                                datefilter();
                            }
						});

                        $('input[id="countvd"]').change(function(e){
                            datefilter();
						});
						
                        $('[line="btn_page_awal"]').attr('href','/admin/menuhutang?tp='+tipe_data+'&d={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_min"]').attr('href','/admin/menuhutang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/menuhutang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/menuhutang?tp='+tipe_data+'&d={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');
                    });

					function datefilter() {
						loadingpage(2000);
						var key_search = encodeURIComponent($('input[name="key-search"]').val());
						var tipe_data = $('select[name="tipe_data"]').val();
                        window.location.href = "/admin/menuhutang?tp="+tipe_data+"&d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search;
					}

					function exportdata() {
						var tipe_data = $('select[name="tipe_data"]').val();
						window.location.href = "/admin/exportlisthutang?tp="+tipe_data+"&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val());
					}
                </script>
            @endsection
@endsection