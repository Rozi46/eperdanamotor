@extends('admin.AdminOne.layout.assets')
@section('title', 'Data Pengguna')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Data Pengguna</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['newusers'] == 'Yes')<a load="true" href="/admin/newusers"><button type="button" class="btn btn-primary">Tambah Data</button></a>@endif
										
										@if($level_user['exportusers'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('listusers')"><i class="fa fa-download"></i> Export Data</button>@endif
									</div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt">
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
													<?php if($res_user['tipe_user'] == 'Super User'){?>
														<th style="min-width:200px; text-align: center;">Nama Perusahaan</th>
														<!-- <th style="min-width:150px; text-align: center;">Nama Cabang</th> -->
													<?php } ?>
													<th style="min-width:200px; text-align: center;">Nama Pengguna</th>
													<th style="min-width:150px; text-align: center;">Kode Pengguna</th>
													<th style="min-width:100px; text-align: center;">Email Pengguna</th>
													<th style="min-width:250px; text-align: center;">Masuk Terakhir</th>
													<th style="min-width:100px; text-align: center;">Status</th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<script>
														function ViewData{{$no}}() {
															loadingpage(2000);
															window.location.href = "/admin/editusers?d={{$view_data['id']}}";
														}
													</script>
													<tr onclick="ViewData{{$no}}()">
														<td style="text-align:center;">{{$no}}</td>
														<?php if($res_user['tipe_user'] == 'Super User'){?>
															<td>{{$listdata['detail_perusahaan'][$view_data['id']]['kantor']}}</td>
														<?php } ?>
														<td>{{$view_data['full_name']}}</td>
														<td style="text-align:center;">{{$view_data['code_data']}}</td>
														<td>{{$view_data['email']}}</td>
														<td><?php if($view_data['created_at'] != $view_data['updated_at']){?> {{Date::parse($view_data['updated_at'])->format('l, j F Y - H:i')}}<?php }else{echo "Belum Ada Aktivitas";} ?> </td>
														<td style="text-align:center;">
															@if($view_data['status_data'] == 'Aktif')
																<div class="alert alert-success" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																	<strong>{{ $view_data['status_data'] ?? 'Belum Ditentukan'}}</strong>
																</div>
															@else
																<div class="alert alert-danger" style="margin: 0 auto; display: inline-block; text-align: center; font-size: 14px; padding: 2px 10px;">
																	<strong>{{ $view_data['status_data'] ?? 'Belum Ditentukan'}}</strong>
																</div>
															@endif
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

@endsection