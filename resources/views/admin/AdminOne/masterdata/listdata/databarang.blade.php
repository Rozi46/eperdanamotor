@extends('admin.AdminOne.layout.assets')
@section('title', 'Data Barang')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd" line="hd_action">
                            <div class="col-md-12 hd_page_main">Data Barang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['newbarang'] == 'Yes')<a load="true" href="/admin/newbarang"><button type="button" class="btn btn-primary">Tambah Data</button></a>@endif
										
										@if($level_user['exportbarang'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('listbarang')"><i class="fa fa-download"></i> Export Data</button>@endif
									</div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt" line="form_action">
							<div class="col-md-12 bg_act_page_main page">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-right">
										@include('admin.AdminOne.layout.pagination')
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
													<!-- <th style="min-width:200px; text-align: center;">Kode Data</th> -->
													<th style="min-width:250px; text-align: center;">Nama Barang</th>
													<!-- <th style="min-width:50px; text-align: center;">Satuan</th>
													<th style="min-width:150px; text-align: center;">Kategori</th>
													<th style="min-width:150px; text-align: center;">Merk</th>
													<th style="min-width:150px; text-align: center;">Supplier</th> -->
													<th style="min-width:150px; text-align: center;">Harga Beli</th>
													<th style="min-width:150px; text-align: center;">Harga Jual</th>
													<th style="min-width:150px; text-align: center;">Harga Khusus</th>
													<th style="min-width:150px; text-align: center;">Harga Beli Tertinggi <br>Tanggal Transaksi</th>
													<th style="min-width:150px; text-align: center;">Harga Beli Terakhir <br>Tanggal Transaksi</th>
													<th class="colright" style="width:30px; text-align: center;"><i class="head fa fa-cog"></i></th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) 
													<?php 
														$no++ ;
													?>
													<script type="text/javascript">
                                                        $(document).ready(function(){
                                                            $('[btn="del_data_{{$view_data['id']}}"]').click(function(){
                                                                if($('[btn="del_data_{{$view_data['id']}}"]').click){
                                                                    $('div[data-model="confirmasi"]').modal({backdrop: false});
                                                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus barang {{$view_data['nama']}}.</div>');
                                                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                                                            loadingpage(20000);
                                											window.location.href = "/admin/deletebarang?d={{$view_data['id']}}";
                                                                        }
                                                                    });
                                                                }
                                                            });
														});
													</script>
													<tr>
														<td style="text-align:center;">{{$no}}</td>
														<td>
															<div class="alert alert-primary" style="display: inline-block; margin: 1px auto 0; text-align: Lev; font-size: 14px; padding: 2px;" title="{{$view_data['nama']}}">
																<strong>{{ $view_data['nama'] ?? 'Belum ditentukan' }}</strong>
															</div>
															<small>
																<br>Kode Data: {{ $view_data['kode'] ?? 'Belum ditentukan' }}<br>
																Satuan: {{ $listdata['satuan'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}<br>
																Kategori: {{ $listdata['kategori'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}<br>
																Merk: {{ $listdata['merk'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}<br>
																Supplier: {{ $listdata['supplier'][$view_data['id']]['nama'] ?? 'Belum ditentukan' }}<br>
															</small>
														</td>
														<td style="text-align:right;">{{ number_format($view_data['harga_beli'] ?? 0,2,",",".") }}</td>
														<td style="text-align: right;">
															{{ number_format($view_data['harga_jual1'] ?? 0, 2, ",", ".") }}<br>
															
															@if($view_data['harga_jual1'] < $view_data['harga_beli'])
																<div style="text-align: center;">
																	<div class="alert alert-danger" style="display: inline-block; margin: 10px auto 0; font-size: 14px; padding: 2px;" title="Harga jual lebih kecil dari harga beli">
																		<strong>Warning!</strong>
																	</div>
																</div>
															@endif
														</td>
														<td style="text-align:right;">
															{{ number_format($view_data['harga_jual2'] ?? 0, 2, ",", ".") }}<br>

															@if($view_data['harga_jual2'] < $view_data['harga_beli'])
																<div style="text-align: center;">
																	<div class="alert alert-danger" style="display: inline-block; margin: 10px auto 0; font-size: 14px; padding: 2px;" title="Harga jual lebih kecil dari harga beli">
																		<strong>Warning!</strong>
																	</div>
																</div>
															@endif
														</td>

														<td style="text-align:right;">
															@if(is_numeric($listdata['harga_beli'][$view_data['id']]))
																{{ number_format($listdata['harga_beli'][$view_data['id']], 2, ",", ".") }}
															@else
																{{ $listdata['harga_beli'][$view_data['id']] }}
															@endif
															<br>
															@if (strtotime($listdata['tanggal_beli'][$view_data['id']]))
																{{ Date::parse($listdata['tanggal_beli'][$view_data['id']])->format('j F Y') }}
															@else
																{{ $listdata['tanggal_beli'][$view_data['id']] }}
															@endif
														</td>

														<td style="text-align:right;">
															@if(is_numeric($listdata['harga_beli_terakhir'][$view_data['id']]))
																{{ number_format($listdata['harga_beli_terakhir'][$view_data['id']], 2, ",", ".") }}
															@else
																{{ $listdata['harga_beli_terakhir'][$view_data['id']] }}
															@endif
															<br>
															@if (strtotime($listdata['tanggal_beli_terakhir'][$view_data['id']]))
																{{ Date::parse($listdata['tanggal_beli_terakhir'][$view_data['id']])->format('j F Y') }}
															@else
																{{ $listdata['tanggal_beli_terakhir'][$view_data['id']] }}
															@endif
														</td>

														<td class="colright" style="text-align:center;">
															<div class="dropdown dropleft">
																<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">Atur</button>
																<div class="dropdown-menu">
																	<h5 class="dropdown-header">Pengaturan Data</h5>
																	<a load="true" class="dropdown-item" href="editbarang?d={{$view_data['id']}}">Lihat/Ubah Data</a>
																	<a class="dropdown-item @if($listdata['count_used'][$view_data['id']] > 0) disabled @endif @if($level_user['deletebarang'] == 'No') disabled @endif" <?php if($listdata['count_used'][$view_data['id']] == 0){ if($level_user['deletebarang'] == 'Yes'){ ?> btn="del_data_{{$view_data['id']}}"<?php } }?>>Hapus Data</a>
																</div>
															</div>
														</td>
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
					});
				</script>
			@endsection

@endsection