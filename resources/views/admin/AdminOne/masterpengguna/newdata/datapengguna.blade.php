@extends('admin.AdminOne.layout.assets')
@section('title', 'Tambah Data Pengguna')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Tambah Data Pengguna
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
										@if($level_user['newusers'] == 'Yes')<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>@endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/newusers">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="full_name" class="col-sm-4 col-form-label">Nama Lengkap <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="full_name" placeholder="Nama Lengkap" value="{{ old('full_name') }}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="phone_number" class="col-sm-4 col-form-label">Nomor Handphone <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="phone_number" placeholder="Nomor Handphone" value="{{ old('phone_number') }}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="email" class="col-sm-4 col-form-label">Alamat Email <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="password"  class="col-sm-4 col-form-label">Kata Sandi <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="password" name="password" placeholder="Kata Sandi" value="{{ old('password') }}"/>
												</div>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="level" class="col-sm-2 col-form-label">Level Pengguna <span>*</span></label>
												<div class="col-sm-10 input">
													<select name="level" placeholder="Level Pengguna">
														@foreach ($list_level as $view_data)
															<option value="{{$view_data['code_data']}}">{{$view_data['level_name']}} </option>
														@endforeach
													</select>
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

			@section('script')
                <script type="text/javascript">
                    $(document).ready(function(){
					});
                </script>
            @endsection
@endsection