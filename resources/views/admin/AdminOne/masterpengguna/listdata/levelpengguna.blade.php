@extends('admin.AdminOne.layout.assets')
@section('title', 'Level Pengguna')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Level Pengguna</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['newlevelusers'] == 'Yes')<a load="true" href="/admin/newlevelusers"><button type="button" class="btn btn-primary">Tambah Data</button></a>@endif
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
													<th style="min-width:100px; text-align: center;">Nama Level</th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<script>
														function ViewData{{$no}}() {
															loadingpage(2000);
															window.location.href = "/admin/editlevel?d={{$view_data['code_data']}}";
														}
													</script>
													<tr onclick="ViewData{{$no}}()">
														<td style="text-align:center;">{{$no}}</td>
														<td style="text-align:left;">{{$view_data['level_name']}}</td>
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