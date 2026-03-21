@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Level Pengguna')

@section('content')

			<div class="page_main">
				<div class="container text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Ubah Level Pengguna</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editlevelusers'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0) @if($results['code_data'] != 'LV5677001') @if($level_user['deletelevelusers'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif @endif @endif

                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/actionlevel">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="code_data" value="{{$results['code_data']}}" readonly="true" style="display:none;" />
										<div class="col-md-12 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Nama Level <span>*</span></div>
												<input type="text" name="level_name" placeholder="Nama Level" value="{{$results['level_name']}}" autofocus/>
                                            </div>
										</div>
										<div class="col-md-12 bg_form_page"> 
											@foreach ($list_akses['menu'] as $view_menu)
												<div class="col-md-12 col_level">
													<div class="bg_level">
														<div class="level" btn="{{$view_menu['nama_akses']}}">
															<i class="fa fa-caret-right"></i> Menu {{$view_menu['nama_menu']}}
														</div>
														<div class="checkboxlevel">
															<input type="text" name="{{$view_menu['nama_akses']}}" value="No" style="display: none;" />
															<input type="checkbox" class="ios" name_level="{{$view_menu['nama_akses']}}" line="btn_level"/>
														</div>
													</div>
												</div>
												<div class="col-md-12 col_level submenu" datamenu="{{$view_menu['nama_akses']}}">
													@foreach ($list_akses['submenu'][$view_menu['id']] as $view_submenu)
														<div class="bg_level">
															<div class="level" btn="{{$view_submenu['nama_akses']}}">
																<i class="fa fa-caret-right"></i>
																<?php if($view_menu['nama_akses'] != 'mutasistockproduk'){?>Menu<?php } ?> {{$view_submenu['nama_menu']}}
															</div>
															<div class="checkboxlevel">
																<input type="text" name="{{$view_submenu['nama_akses']}}" value="No" style="display: none;" />
																<input type="checkbox" class="ios" name_level="{{$view_submenu['nama_akses']}}" line="btn_level"/>
															</div>
														</div>
														<div class="col-md-12 col_level btnaction" datamenu="{{$view_submenu['nama_akses']}}">
															@foreach ($list_akses['action'][$view_submenu['id']] as $view_action)
																<div class="bg_level">
																	<div class="level">
																		<i class="fa fa-caret-right"></i> {{$view_action['nama_menu']}}
																	</div>
																	<div class="checkboxlevel">
																		<input type="text" name="{{$view_action['nama_akses']}}" value="No" style="display: none;" />
																		<input type="checkbox" class="ios" name_level="{{$view_action['nama_akses']}}" line="btn_level"/>
																	</div>
																</div>
																<div class="col-md-12 col_level btnsubaction" datamenu="{{$view_action['nama_akses']}}">
																	@foreach ($list_akses['subaction'][$view_action['id']] as $view_subaction)
																		<div class="bg_level">
																			<div class="level">
																				<i class="fa fa-caret-right"></i> {{$view_subaction['nama_menu']}}
																			</div>
																			<div class="checkboxlevel">
																				<input type="text" name="{{$view_subaction['nama_akses']}}" value="No" style="display: none;" />
																				<input type="checkbox" class="ios" name_level="{{$view_subaction['nama_akses']}}" line="btn_level"/>
																			</div>
																		</div>
																	@endforeach
																</div>
															@endforeach
														</div>
													@endforeach
												</div>
											@endforeach
										</div>
									</div> 
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			@section('script')
				<script type="text/javascript">
					$(document).ready(function(){
                        @if($level_user['editlevelusers'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
						@endif
						                    
						@foreach ($results['results'] as $view_data)
							$('input[name="{{$view_data['data_menu']}}"]').val('{{$view_data['access_rights']}}');
							if($('input[name="{{$view_data['data_menu']}}"]').val() == 'Yes' ){
								$('input[name_level="{{$view_data['data_menu']}}"]').prop('checked', true);
							}
							$('[btn="{{$view_data['data_menu']}}"]').click(function(){
								if($('[btn="{{$view_data['data_menu']}}"]').click){
									$('[datamenu="{{$view_data['data_menu']}}"]').toggle(600);
									$('[datasubmenu="{{$view_data['data_menu']}}"]').toggle(600);
								}
							});
						@endforeach

						$(".ios").iosCheckbox();
						$('input[line="btn_level"]').on('click', function(){
							if($(this).is(':checked')){
								$('input[name="'+$(this).attr("name_level")+'"]').val('No');
								$('[datamenu="'+$(this).attr("name_level")+'"]').hide();
								$('[datasubmenu="'+$(this).attr("name_level")+'"]').hide();
							}else{
								$('input[name="'+$(this).attr("name_level")+'"]').val('Yes');
								$('[datamenu="'+$(this).attr("name_level")+'"]').show();
								$('[datasubmenu="'+$(this).attr("name_level")+'"]').show();
							}
						});
					});
					
					function DeleteData() {
						$('div[data-model="confirmasi"]').modal({backdrop: false});
						$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$results['level_name']}}.</div>');
						$('button[btn-action="aciton-confirmasi"]').remove();
						$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
						$('button[btn-action="aciton-confirmasi"]').click(function(){
							if($('button[btn-action="aciton-confirmasi"]').click){
								loadingpage(20000);
								window.location.href = "/admin/deletelevel?d={{$results['code_data']}}";
							}
						});
					}
				</script>
            @endsection
@endsection