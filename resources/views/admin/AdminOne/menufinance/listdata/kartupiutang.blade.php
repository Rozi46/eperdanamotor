@extends('admin.AdminOne.layout.assets')
@section('title', 'Kartu Piutang')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Kartu Piutang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['menutagihan'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('kartupiutang')"><i class="fa fa-download"></i> Export Data</button>@endif
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
											<thead>
												<tr>
													<th style="width:30px; text-align: center;">No</th>
													<th style="min-width:150px; text-align: center;">Tanggal Transaksi</th>
													<th style="width:150px; text-align: center;">No. Transaksi</th>
													<th style="min-width:150px; text-align: center;">Penjualan Oleh</th>
													<th style="min-width:300px; text-align: center;">Nama Produk</th>
													<th style="width:100px; text-align: center;">Jumlah</th>
													<th style="width:100px; text-align: center;">Satuan</th>
													<th style="width:150px; text-align: center;">Harga Netto</th>
													<th style="width:150px; text-align: center;">Total Harga</th>
													<th style="width:150px; text-align: center;">Total</th>
													<th style="min-width:150px; text-align: center;">Jumlah Bayar</th>
													<th style="width:150px; text-align: center;">Saldo Piutang</th>
													<th style="width:200px; text-align: center;">Keterangan</th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0; ?>
												@forelse($results['data'] as $view_data)
													<?php 
														$no++; 
														$produk_list = $listdata['list_produk'][$view_data['nomor']] ?? [];
														$rowspan = count($produk_list) > 0 ? count($produk_list) : 1; 
													?>
													@if(count($produk_list) > 0)
														@foreach($produk_list as $index => $view_produk)
														<tr>
															@if($index === 0)
																<td style="text-align:center;" rowspan="{{ $rowspan }}">{{ $no }}</td>
																<td rowspan="{{ $rowspan }}">{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
																<td rowspan="{{ $rowspan }}">{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
																<td rowspan="{{ $rowspan }}">{{ $listdata['user_input'][$view_data['nomor']]['full_name'] ?? 'Belum ditentukan' }}</td>
															@endif
															<td>{{ $listdata['detail_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
															<td style="text-align:center;">{{ $view_produk['jumlah_jual'] ?? 'Belum ditentukan' }}</td>
															<td style="text-align:center;">{{ $listdata['satuan_barang_produk'][$view_produk['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>
															<td style="text-align:right;">{{ number_format($view_produk['harga_netto'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">{{ number_format($view_produk['total_harga'] ?? 0,2,",",".") }}</td>
															@if($index === 0)
																<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['jumlah'] ?? 0,2,",",".") }}</td>
																<td style="text-align:right;" rowspan="{{ $rowspan }}">
																	{{ number_format($view_data['bayar'],2,",",".") }}<br>
																	@forelse($listdata['detail_tanggal_bayar'][$view_data['nomor']] ?? [] as $bayar)
																		<small>{{ Date::parse($bayar['tanggal'])->format('j F Y') }} : {{ number_format($bayar['jumlah'] ?? 0, 2, ',', '.') }}</small><br>
																	@empty
																		Belum dibayar
																	@endforelse
																</td>
																<td style="text-align:right;" rowspan="{{ $rowspan }}">{{ number_format($view_data['sisa'] ?? 0,2,",",".") }}</td>
																<td rowspan="{{ $rowspan }}">{{ $view_data['ket'] ?? 'Belum ditentukan' }}</td>
															@endif
														</tr>
														@endforeach
													@else
														<tr>
															<td style="text-align:center;">{{ $no }}</td>
															<td>{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}</td>
															<td>{{ $view_data['nomor'] ?? 'Belum ditentukan' }}</td>
															<td>{{ $listdata['user_input'][$view_data['nomor']]['full_name'] ?? 'Belum ditentukan' }}</td>
															<td colspan="5" style="text-align: center;">Tidak ada produk</td>
															<td style="text-align:right;">{{ number_format($view_data['jumlah'] ?? 0,2,",",".") }}</td>
															<td style="text-align:right;">
																{{ number_format($view_data['bayar'],2,",",".") }}<br>
																@forelse($listdata['detail_tanggal_bayar'][$view_data['nomor']] ?? [] as $bayar)
																	<small>{{ Date::parse($bayar['tanggal'])->format('j F Y') }} : {{ number_format($bayar['jumlah'] ?? 0, 2, ',', '.') }}</small><br>
																@empty
																	Belum dibayar
																@endforelse
															</td>
															<td style="text-align:right;">{{ number_format($view_data['sisa'] ?? 0,2,",",".") }}</td>
															<td>{{ $view_data['ket'] ?? 'Belum ditentukan' }}</td>
														</tr>
													@endif
												@empty
													<tr>
														<td style="text-align:center; padding: 20px; background-color: #FFFFFF; font-weight: 600; font-size: 14px;" colspan="13">Tidak ada data yang tersedia</td>
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
						
                        $('[line="btn_page_awal"]').attr('href','/admin/kartupiutang?d={{$request['d']}}&page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_min"]').attr('href','/admin/kartupiutang?d={{$request['d']}}&page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

                        $('[line="btn_page_plus"]').attr('href','/admin/kartupiutang?d={{$request['d']}}&page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');

						$('[line="btn_page_akhir"]').attr('href','/admin/kartupiutang?d={{$request['d']}}&page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}');
                    });

					function datefilter() {
						loadingpage(2000);
						var key_search = encodeURIComponent($('input[name="key-search"]').val());
                        window.location.href = "/admin/kartupiutang?d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+key_search;
					}

					function exportdata() {
						window.location.href = "/admin/exportkartupiutang?d={{$request['d']}}&page=1&vd="+encodeURIComponent($('input[id="countvd"]').val())+"&keysearch="+encodeURIComponent($('input[name="key-search"]').val());
					}
                </script>
            @endsection
@endsection