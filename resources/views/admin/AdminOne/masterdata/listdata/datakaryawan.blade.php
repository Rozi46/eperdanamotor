@extends('admin.AdminOne.layout.assets')
@section('title', 'Data Karyawan')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Data Karyawan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['newkaryawan'] == 'Yes')<a load="true" href="/admin/newkaryawan"><button type="button" class="btn btn-primary">Tambah Data</button></a>@endif
										
										@if($level_user['exportkaryawan'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('listkaryawan')"><i class="fa fa-download"></i> Export Data</button>@endif
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
													<th style="min-width:200px; text-align: center;">Nama</th>
													<th style="min-width:250px; text-align: center;">Tempat, Tgl Lahir</th>
													<th style="min-width:25px; text-align: center;">Jenis Kelamin</th>
													<th style="min-width:150px; text-align: center;">Jabatan</th>
													<th style="min-width:100px; text-align: center;">No. Hp</th>
													<th style="min-width:200px; text-align: center;">Alamat</th>
													<th style="min-width:100px; text-align: center;">Status Data</th>													
													<th class="colright" style="width:30px; text-align: center;"><i class="head fa fa-cog"></i></th>
												</tr>
											</thead>
											<tbody>
												<?php $no = 0;?> @forelse($results['data'] as $view_data) <?php $no++ ;?>
													<script type="text/javascript">
														$(document).ready(function(){
															$('[btn="del_data_{{$view_data['id']}}"]').click(function(){
																if($('[btn="del_data_{{$view_data['id']}}"]').click){
																	$('div[data-model="confirmasi"]').modal({backdrop: false});
																	$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$view_data['nama']}}.</div>');
																	$('button[btn-action="aciton-confirmasi"]').remove();
																	$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
																	$('button[btn-action="aciton-confirmasi"]').click(function(){
																		if($('button[btn-action="aciton-confirmasi"]').click){
																			loadingpage(20000);
																			window.location.href = "/admin/deletekaryawan?d={{$view_data['id']}}";
																		}
																	});
																}
															});
															if($('input[name="status_data_{{$view_data['id']}}"]').val() == 'Aktif' ){
																$('input[name="btncheckbox_{{$view_data['id']}}"]').prop('checked', true);
															}
															$('input[btn="btncheckbox_{{$view_data['id']}}"]').on('click', function(){
																loadingpage(20000);
																<?php if($level_user['editkaryawan'] == 'Yes'){ ?>
																	$.ajax({
																		type: "GET",
																		url: "/admin/upstatuskaryawan?_token={{csrf_token()}}&token=<?php echo $request['token'];?>&u=<?php echo $request['u'];?>&id=<?php echo $view_data['id'];?>",
																		cache: false,
																		success: function(data){
																			if(data.status_message == 'success'){
																				window.location.href = "/admin/{{$url_active}}";
																			}else{
																				window.location.href = "/admin/{{$url_active}}";
																			}
																		}
																	});
																<?php }else{ ?>
																	window.location.href = "/admin/{{$url_active}}";
																<?php } ?>
															});
														});
													</script>
													<tr>
														<td style="text-align:center;">{{$no}}</td>
														<td>{{$view_data['nama']}}</td>
														<td>{{$view_data['tempat_lahir']}}, {{Date::parse($view_data['tanggal_lahir'])->format('j F Y')}}</td>
														<td style="text-align:center;">{{$view_data['jenis_kelamin']}}</td>
														<td>{{$view_data['jabatan']}}</td>
														<td>{{$view_data['no_hp']}}</td>
														<td>{{$view_data['alamat']}}</td>
														<td style="text-align:center;">
															<div class="checkboxlios">
																<input type="text" name="status_data_{{$view_data['id']}}" value="{{$view_data['status_data']}}" style="display:none;" />
																<input type="checkbox" class="ios" name="btncheckbox_{{$view_data['id']}}" btn="btncheckbox_{{$view_data['id']}}" style="display:none;" <?php if($level_user['editkaryawan'] == 'No'){ ?>disabled="true"<?php }?>/>
															</div>
														</td>
														<td class="colright" style="text-align:center;">
															<div class="dropdown dropleft">
																<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">Atur</button>
																<div class="dropdown-menu">
																	<h5 class="dropdown-header">Pengaturan Data</h5>
																	<a load="true" class="dropdown-item" href="editkaryawan?d={{$view_data['id']}}">Lihat/Ubah Data</a>
																	<a class="dropdown-item @if($level_user['deletekaryawan'] == 'No') disabled @endif" <?php if($level_user['deletekaryawan'] == 'Yes'){ ?> btn="del_data_{{$view_data['id']}}"<?php }?>>Hapus Data</a>
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
						$(".ios").iosCheckbox();
					});
				</script>
			@endsection

@endsection