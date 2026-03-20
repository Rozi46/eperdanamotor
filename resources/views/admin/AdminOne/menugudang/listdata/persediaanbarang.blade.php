@extends('admin.AdminOne.layout.assets')
@section('title', 'Persediaan Barang')
@section('content')
            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd" >
                            <div class="col-md-12 hd_page_main">Persediaan Barang</div>
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
							<div class="col-md-12 data_page">
								<div class="row bg_data_page">
									<div class="table_data freezeHead freezeCol">
										<table class="table_view table-striped table-hover">
											<!-- <thead>
												<tr>
													<th style="width:30px; text-align: center;">No</th>
													<th style="min-width:250px; text-align: center;">Nama Barang</th>
													<th style="min-width:100px; text-align: center;">Satuan</th>
													<th style="min-width:100px; text-align: center;">Stock</th>
													<th style="min-width:100px; text-align: center;">Harga Beli Tertinggi <br> Tanggal Transaksi</th>
													<th style="min-width:100px; text-align: center;">Harga Jual Terakhir <br> Tanggal Transaksi</th>
												</tr>
											</thead> -->

                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width:30px; text-align:center;">No</th>
                                                    <th rowspan="2" style="min-width:250px; text-align:center;">Nama Barang</th>
                                                    <th rowspan="2" style="min-width:100px; text-align:center;">Satuan</th>
                                                    <th rowspan="2" style="min-width:100px; text-align:center;">Stock</th>

                                                    <!-- HEADER UTAMA STOCK -->
                                                    <th colspan="{{ count($listdata['list_gudang']) }}"style="min-width:100px; text-align:center;">Stock</th>

                                                    <th rowspan="2" style="min-width:140px; text-align:center;">Harga Beli Tertinggi<br>Tanggal Transaksi</th>
                                                    <th rowspan="2" style="min-width:140px; text-align:center;">Harga Jual Terakhir<br>Tanggal Transaksi</th>
                                                </tr>

                                                <tr>
                                                    <!-- SUB HEADER GUDANG -->
                                                    @foreach($listdata['list_gudang'] as $view_gudang)
                                                        <th style="min-width:100px; text-align:center;">{{ $view_gudang['nama'] ?? 'Belum Ditentukan' }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>

											<tbody>
												<?php $bg_one = "#FFFFFF"; $bg_two = "#F1F1F1"; $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<tr>
														<td style="text-align:center;">{{$no}}</td>
														<td style="text-align:left;" > 
															<div class="alert alert-primary" style="display: inline-block; margin: 1px auto 0; text-align: Lev; font-size: 14px; padding: 2px;" title="{{$view_data['nama_barang']}}">
																<strong>{{ $view_data['nama_barang'] ?? 'Belum Ditentukan' }}</strong>
															</div>
                                                            <a href="{{ url('editbarang') . '?d=' . $view_data['id'] }}" title="Lihat detail data">
                                                                <br>{{ $view_data['kode'] ?? ''}}<br>
                                                            </a>    
                                                            <small>
                                                                Kategori : {{ $listdata['kategori'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan'}}<br>
                                                                Merk : {{ $listdata['merk'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan'}}<br>
                                                                Supllier : {{ $listdata['supplier'][$view_data['kode_barang']]['nama'] ?? 'Belum Ditentukan'}}<br>
                                                            </small>
                                                        </td>
                                                        <td style="text-align:center;">{{ $view_data['nama_satuan'] ?? 'Belum Ditentukan' }}</td>
                                                        <td style="text-align:center;">
															<button type="button" class="btn btn-primary" btn="view_stock_{{$view_data['code_data']}}" title="Lihat Keberadaan Stock" style="font-size: 12px; height: 33px;margin-top: 0px;">{{ number_format($listdata['stock_akhir'][$view_data['kode_barang']]  ?? 0,2,",",".") }}</button>
                                                        </td>

                                                        <div class="modal fade" role="dialog" data-model="view_stock_{{$view_data['code_data']}}">
                                                            <div class="modal-dialog modal-md">
                                                                <div class="modal-content">
                                                                    <div class="modal-body">
                                                                        <div line="hd_model" class="hd_model">Keberadaan Stock Pergudang</div>
                                                                        <div class="col-md-12 data_page" style="padding:0px;">
                                                                            <div class="row bg_data_page" style="margin:0px;">
                                                                                <?php foreach($listdata['list_gudang'] as $view_gudang){?>
                                                                                    <div class="col-md-6 bg_page_main" style="border:none;">
                                                                                        <div class="col-md-12 data_page" style="padding: 5px; border: 1px solid #E6E7E8; border-radius: 10px;">
                                                                                            <div class="col-md-12 text-center hd_model">{{ $view_gudang['nama'] ?? 'Belum Ditentukan'}}</div>
                                                                                            <div class="col-md-12 text-center" style="height: 45px; line-height: 40px; font-size:14px; font-weight: 600;">{{ number_format($listdata['stok_pergudang'][$view_data['kode_barang']][$view_gudang['code_data']]  ?? 0,2,",",".") }} {{ $view_data['nama_satuan'] ?? 'Belum Ditentukan' }}</div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php } ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn-action="close-confirmasi">Tutup</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        @foreach($listdata['list_gudang'] as $view_gudang)                                                        
                                                            <td style="text-align:center;">{{ number_format($listdata['stok_pergudang'][$view_data['kode_barang']][$view_gudang['code_data']] ?? 0,2,",",".") }} {{ $view_data['nama_satuan'] ?? 'Belum Ditentukan' }}</td>
                                                        @endforeach

                                                        <td style="text-align:right;">
                                                            @if(is_numeric($listdata['harga_beli'][$view_data['kode_barang']]))
                                                                {{ number_format($listdata['harga_beli'][$view_data['kode_barang']], 2, ",", ".") }}
                                                            @else
                                                                {{ $listdata['harga_beli'][$view_data['kode_barang']] }}
                                                            @endif
                                                            <br>
                                                            @if (strtotime($listdata['tanggal_beli'][$view_data['kode_barang']]))
                                                                {{ Date::parse($listdata['tanggal_beli'][$view_data['kode_barang']])->format('j F Y') }}
                                                            @else
                                                                {{ $listdata['tanggal_beli'][$view_data['kode_barang']] }}
                                                            @endif
                                                        </td>

                                                        <td style="text-align:right;">
                                                            @if(is_numeric($listdata['harga_jual'][$view_data['kode_barang']]))
                                                                {{ number_format($listdata['harga_jual'][$view_data['kode_barang']], 2, ",", ".") }}
                                                            @else
                                                                {{ $listdata['harga_jual'][$view_data['kode_barang']] }}
                                                            @endif
                                                            <br>
                                                            @if (strtotime($listdata['tanggal_jual'][$view_data['kode_barang']]))
                                                                {{ Date::parse($listdata['tanggal_jual'][$view_data['kode_barang']])->format('j F Y') }}
                                                            @else
                                                                {{ $listdata['tanggal_jual'][$view_data['kode_barang']] }}
                                                            @endif
                                                        </td>
                                                        <script type="text/javascript">
                                                            $(document).ready(function(){
                                                                $('[btn="view_stock_{{$view_data['code_data']}}"]').click(function(){
                                                                    if($('[btn="view_stock_{{$view_data['code_data']}}"]').click){
                                                                        $('div[data-model="view_stock_{{$view_data['code_data']}}"]').modal({backdrop: true});
                                                                    }
                                                                });
                                                            });
                                                        </script>
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

                        $('[line="btn_page_awal"]').attr('href','/admin/persediaanbarang?td={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_min"]').attr('href','/admin/persediaanbarang?td={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/persediaanbarang?td={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/persediaanbarang?td={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');
                    });

					function datefilter() {
						loadingpage(2000);
						var key_search = encodeURIComponent($('input[name="key-search"]').val());

						if($('input[name="datefilterstart"]').val() != '' && $('input[name="datefilterend"]').val() != ''){
							window.location.href = "/admin/persediaanbarang?d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search;
						}else{
							window.location.reload();
						}
					}

					function exportdata(namedata) {
						window.location.href = "/admin/exportpersediaanbarang?page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val());
					}
                </script>
            @endsection
@endsection