@extends('admin.AdminOne.layout.assets')
@section('title', 'Tambah Data Cabang')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Tambah Data Cabang
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
										@if($level_user['newcabang'] == 'Yes')<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>@endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/newcabang">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama_cabang" class="col-sm-4 col-form-label">Nama Cabang <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama_cabang" placeholder="Nama Cabang" value="{{ old('nama_cabang') }}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama_pic" class="col-sm-4 col-form-label">Nama PIC <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama_pic" placeholder="Nama PIC" value="{{ old('nama_pic') }}"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="no_hp_pic" class="col-sm-4 col-form-label">No Hp PIC <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="no_hp_pic" placeholder="No HP PIC" value="" onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="alamat" class="col-sm-4 col-form-label">Alamat <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="alamat" placeholder="Alanat" value="" autofocus/>
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