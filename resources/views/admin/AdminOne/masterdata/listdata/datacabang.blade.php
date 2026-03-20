@extends('admin.AdminOne.layout.assets')
@section('title', 'Data Cabang')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Data Cabang</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['newcabang'] == 'Yes')<a load="true" href="/admin/newcabang"><button type="button" class="btn btn-primary">Tambah Data</button></a>@endif
										
										@if($level_user['exportcabang'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('listcabang')"><i class="fa fa-download"></i> Export Data</button>@endif
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
													<th style="min-width:100px; text-align: center;">Kode Data</th>
													<th style="min-width:250px; text-align: center;">Nama Cabang</th>
													<th style="min-width:200px; text-align: center;">Nama PIC</th>
													<th style="min-width:150px; text-align: center;">No. HP PIC</th>
													<th style="min-width:250px; text-align: center;">Alamat</th>
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
                                                                    $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus barang {{$view_data['nama_cabang']}}.</div>');
                                                                    $('button[btn-action="aciton-confirmasi"]').remove();
                                                                    $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                                                                    $('button[btn-action="aciton-confirmasi"]').click(function(){
                                                                        if($('button[btn-action="aciton-confirmasi"]').click){
                                                                            loadingpage(20000);
                                											window.location.href = "/admin/deletecabang?d={{$view_data['id']}}";
                                                                        }
                                                                    });
                                                                }
                                                            });
														});
													</script>
													<tr>
														<td style="text-align:center;">{{$no}}</td>
														<td style="text-align:center;">{{$view_data['kode_cabang']}} </td>
														<td>{{$view_data['nama_cabang']}} </td>
														<td>{{$view_data['nama_pic']}} </td>
														<td>{{$view_data['nomor_pic']}} </td>
														<td>{{$view_data['alamat']}} </td>
														<td class="colright" style="text-align:center;">
															<div class="dropdown dropleft">
																<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">Atur</button>
																<div class="dropdown-menu">
																	<h5 class="dropdown-header">Pengaturan Data</h5>
																	<a load="true" class="dropdown-item" href="editcabang?d={{$view_data['id']}}">Lihat/Ubah Data</a>
																	<a class="dropdown-item @if($listdata['count_used'][$view_data['id']] > 0) disabled @endif @if($level_user['deletecabang'] == 'No') disabled @endif" <?php if($listdata['count_used'][$view_data['id']] == 0){ if($level_user['deletecabang'] == 'Yes'){ ?> btn="del_data_{{$view_data['id']}}"<?php } }?>>Hapus Data</a>
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