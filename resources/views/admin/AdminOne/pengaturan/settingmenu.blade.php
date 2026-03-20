@extends('admin.AdminOne.layout.assets')
@section('title', 'Pengaturan Menu & Akses')

@section('content')

			<div class="page_main">
				<div class="container text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Pengaturan Menu & Akses</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($res_user['level'] == 'LV5677001')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/actionsettingmenu">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="" readonly="true" style="display:none;" />
										@if($res_user['level'] == 'LV5677001')
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">No Urut <span>*</span></div>
													<input type="text" name="no_urut" placeholder="No Urut" value="{{ old('no_urut') }}" autofocus/>
												</div>
											</div>
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Nama Menu <span>*</span></div>
													<input type="text" name="nama_menu" placeholder="Nama Menu" value="{{ old('nama_menu') }}" autofocus/>
												</div>
											</div>
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Nama Akses <span>*</span></div>
													<input type="text" name="nama_akses" placeholder="Nama Akses" value="{{ old('nama_akses') }}" autofocus/>
												</div>
											</div>
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Menu Index</div>
													<select name="menu_index" placeholder="Menu Index">
														<option value="">Menu Index</option>
														@foreach ($list_akses['menu'] as $view_menu)
															<option value="{{$view_menu['id']}}">** {{$view_menu['nama_menu']}}</option>
															@foreach ($list_akses['submenu'][$view_menu['id']] as $view_submenu)
																<option value="{{$view_submenu['id']}}">**** {{$view_submenu['nama_menu']}}</option>
																@foreach ($list_akses['action'][$view_submenu['id']] as $view_action)
																	<option value="{{$view_action['id']}}">****** {{$view_action['nama_menu']}}</option>
																@endforeach
															@endforeach
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Type Menu</div>
													<select name="type_menu" placeholder="Type Menu">
														<option value="Menu">Menu</option>
														<option value="SubMenu">Sub Menu</option>
														<option value="Action">Action</option>
														<option value="SubAction">Sub Action</option>
													</select>
												</div>
											</div>
											<div class="col-md-2 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Icon Menu</div>
													<input type="text" name="icon_menu" placeholder="Icon Menu" value="{{ old('icon_menu') }}" autofocus/>
												</div>
											</div>
										@endif
                                        
										<div class="col-md-12 bg_form_page"> 
											@foreach ($list_akses['menu'] as $view_menu)
												<div class="col-md-12 col_level">
													<div class="bg_level">
														<div class="level">
															<span menu="{{$view_menu['nama_akses']}}" line="btn_level"><i class="fa fa-caret-right"></i> {{$view_menu['no_urut']}}. {{$view_menu['nama_menu']}} | {{$view_menu['nama_akses']}}</span> @if($res_user['level'] == 'LV5677001')<button type="button" class="btn btn-danger" style="padding:2px 5px; font-size:12px;" name="del_menu_{{$view_menu['id']}}"> Hapus</button>@endif
														</div>
													</div>
												</div>
												<script>
													$(document).ready(function(){
														$('[name="del_menu_{{$view_menu['id']}}"]').click(function(){
															if($('[name="del_menu_{{$view_menu['id']}}"]').click){
																$('div[data-model="confirmasi"]').modal({backdrop: false});
																$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$view_menu['nama_menu']}}.</div>');
																$('button[btn-action="aciton-confirmasi"]').remove();
																$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
																$('button[btn-action="aciton-confirmasi"]').click(function(){
																	if($('button[btn-action="aciton-confirmasi"]').click){
																		loadingpage(20000);
																		window.location.href = "/admin/delmenu?d={{$view_menu['id']}}";
																	}
																});
															}
														});
													});
												</script>
												<div class="col-md-12 col_level submenu" datamenu="{{$view_menu['nama_akses']}}">
													@foreach ($list_akses['submenu'][$view_menu['id']] as $view_submenu)
														<div class="bg_level">
															<div class="level">
																<span menu="{{$view_submenu['nama_akses']}}" line="btn_level"><i class="fa fa-caret-right"></i> {{$view_submenu['no_urut']}}. {{$view_submenu['nama_menu']}} | {{$view_submenu['nama_akses']}}</span> @if($res_user['level'] == 'LV5677001')<button type="button" class="btn btn-danger" style="padding:2px 5px; font-size:12px;" name="del_submenu_{{$view_submenu['id']}}"> Hapus</button>@endif
															</div>
														</div>
														<script>
															$(document).ready(function(){
																$('[name="del_submenu_{{$view_submenu['id']}}"]').click(function(){
																	if($('[name="del_submenu_{{$view_submenu['id']}}"]').click){
																		$('div[data-model="confirmasi"]').modal({backdrop: false});
																		$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$view_submenu['nama_menu']}}.</div>');
																		$('button[btn-action="aciton-confirmasi"]').remove();
																		$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
																		$('button[btn-action="aciton-confirmasi"]').click(function(){
																			if($('button[btn-action="aciton-confirmasi"]').click){
																				loadingpage(20000);
																				window.location.href = "/admin/delmenu?d={{$view_submenu['id']}}";
																			}
																		});
																	}
																});
															});
														</script>
														<div class="col-md-12 col_level btnaction" datamenu="{{$view_submenu['nama_akses']}}">
															@foreach ($list_akses['action'][$view_submenu['id']] as $view_action)
																<div class="bg_level">
																	<div class="level">
																		<span menu="{{$view_action['nama_akses']}}" line="btn_level"><i class="fa fa-caret-right"></i> {{$view_action['no_urut']}}. {{$view_action['nama_menu']}} | {{$view_action['nama_akses']}} </span> @if($res_user['level'] == 'LV5677001')<button type="button" class="btn btn-danger" style="padding:2px 5px; font-size:12px;" name="del_action_{{$view_action['id']}}"> Hapus</button>@endif
																	</div>
																</div>
																<script>
																	$(document).ready(function(){
																		$('[name="del_action_{{$view_action['id']}}"]').click(function(){
																			if($('[name="del_action_{{$view_action['id']}}"]').click){
																				$('div[data-model="confirmasi"]').modal({backdrop: false});
																				$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$view_action['nama_menu']}}.</div>');
																				$('button[btn-action="aciton-confirmasi"]').remove();
																				$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
																				$('button[btn-action="aciton-confirmasi"]').click(function(){
																					if($('button[btn-action="aciton-confirmasi"]').click){
																						loadingpage(20000);
																						window.location.href = "/admin/delmenu?d={{$view_action['id']}}";
																					}
																				});
																			}
																		});
																	});
																</script>
																<div class="col-md-12 col_level btnsubaction" datamenu="{{$view_action['nama_akses']}}">
																	@foreach ($list_akses['subaction'][$view_action['id']] as $view_subaction)
																		<div class="bg_level">
																			<div class="level">
																				<i class="fa fa-caret-right"></i> {{$view_subaction['no_urut']}}. {{$view_subaction['nama_menu']}} | {{$view_subaction['nama_akses']}} @if($res_user['level'] == 'LV5677001')<button type="button" class="btn btn-danger" style="padding:2px 5px; font-size:12px;" name="del_subaction_{{$view_subaction['id']}}"> Hapus</button>@endif
																			</div>
																		</div>
																		<script>
																			$(document).ready(function(){
																				$('[name="del_subaction_{{$view_subaction['id']}}"]').click(function(){
																					if($('[name="del_subaction_{{$view_subaction['id']}}"]').click){
																						$('div[data-model="confirmasi"]').modal({backdrop: false});
																						$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$view_subaction['nama_menu']}}.</div>');
																						$('button[btn-action="aciton-confirmasi"]').remove();
																						$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
																						$('button[btn-action="aciton-confirmasi"]').click(function(){
																							if($('button[btn-action="aciton-confirmasi"]').click){
																								loadingpage(20000);
																								window.location.href = "/admin/delmenu?d={{$view_subaction['id']}}";
																							}
																						});
																					}
																				});
																			});
																		</script>
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
						$('[line="btn_level"]').on('click', function(){
							var menu = $(this).attr("menu");
							if($('[datamenu="'+menu+'"]').is(":hidden")){
								$('[datamenu="'+menu+'"]').slideDown();
							}else{
								$('[datamenu="'+menu+'"]').slideUp();
							}
						});
                    });
				</script>
            @endsection
@endsection