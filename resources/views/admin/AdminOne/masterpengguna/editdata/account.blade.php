@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Akun')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd" line="hd_action">
                            <div class="col-md-12 hd_page_main">Ubah Akun</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')"> Simpan Data</button>
										<button type="button" class="btn btn-info" onclick="EditPassword()">Ubah Kata Sandi</button>
									</div>

								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editaccount">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $res_user['id'] }}" readonly="true" style="display: none;" />
										<div class="col-md-6 bg_form_page">
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Nama Lengkap <span>*</span></div>
													<input type="text" name="full_name" placeholder="Nama Lengkap" value="{{$res_user['full_name']}}" autofocus/>
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Nomor Handphone Pengguna <span>*</span></div>
													<input type="text" name="phone_number" placeholder="Nomor Handphone Pengguna" value="{{$res_user['phone_number']}}"/>
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Alamat Email <span>*</span></div>
													<input type="email" name="email" placeholder="Alamat Email" value="{{$res_user['email']}}"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Foto Profil</div>
                                                <img src="<?php if( $res_user['image'] == 'no_img'){echo asset('/themes/admin/AdminOne/image/no_image.jpg'); }else{echo asset('/themes/admin/AdminOne/image/upload/'.$res_user['image'].'');}?>" alt="User" srcimg="image_admin" onclick="OpenFile('form_data','image_admin')">

												<input type="file" accept="image/*" name="image_admin" placeholder="Foto Profil"/>
												<div class="btn_200">
													<button type="button" class="btn btn-default" onclick="OpenFile('form_data','image_admin')">Upload Foto</button>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" role="dialog" data-model="EditPassword">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body">
                        	<div line="hd_model" class="hd_model">Ubah Kata Sandi</div>
							<div class="col-md-12 bg_page_main">
								<div class="col-md-12 data_page">
									<form method="post" name="form_edit_pass" enctype="multipart/form-data" action="/admin/editpassaccount">
										{{csrf_field()}}
										<div class="row bg_data_page form_page content">
											<input type="text" name="id_data" value="{{ $res_user['id'] }}" readonly="true" style="display: none;" />
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Kata Sandi Lama <span>*</span></div>
													<input type="password" name="old_password" placeholder="Kata Sandi Lama" value="{{ old('old_password') }}" />
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Kata Sandi Baru <span>*</span></div>
													<input type="password" name="new_password" placeholder="Kata Sandi Baru" value="{{ old('new_password') }}" />
												</div>
											</div>
										</div> 
									</form>
								</div>
							</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn-action="close-confirmasi" style="padding: 5px 10px 7px 10px;">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

			@section('script')

				<script>
					function EditPassword() {
						$('div[data-model="EditPassword"]').modal({backdrop: false});
						$('button[btn-action="aciton-confirmasi"]').remove();
						$('[name="old_password"]').focus();
						$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi" style="padding: 5px 10px 7px 10px;">Simpan Data</button>');
						$('button[btn-action="aciton-confirmasi"]').click(function(){
							if($('button[btn-action="aciton-confirmasi"]').click){
								loadingpage(20000);
								$('form[name="form_edit_pass"]').submit();
							}
						});
						$('form[name="form_edit_pass"] input').keyup(function(e){if(e.keyCode == 13) {loadingpage(20000);$('form[name="form_edit_pass"]').submit();};});
					}
				</script>

			@endsection

@endsection