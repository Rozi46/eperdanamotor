@extends('admin.AdminOne.layout.assets')
@section('title', 'Tambah Data Kategori')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Tambah Data Kategori
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
										@if($level_user['newkategori'] == 'Yes')<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>@endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/newkategori">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<div class="col-md-12 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama" class="col-sm-2 col-form-label">Nama Kategori <span>*</span></label>
												<div class="col-sm-10 input">
													<input type="text" name="nama" placeholder="Nama Kategori" value="{{ old('nama') }}" autofocus/>
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