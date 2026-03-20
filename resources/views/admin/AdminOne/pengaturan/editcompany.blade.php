@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Perusahaan')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Ubah Data Perusahaan</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0)<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif 
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editcompany">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['kantor']['id'] }}" readonly="true" style="display: none;" />
										<div class="col-md-6 bg_form_page">
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Kode <span>*</span></div>
													<input type="text" name="kode_company" placeholder="Kode Company" value="{{ $results['results']['kantor']['kode'] }}" @if($results['count_used'] > 0) readonly="true" @endif />
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Nama <span>*</span></div>
													<input type="text" name="nama_company" placeholder="Nama Company" value="{{ $results['results']['kantor']['kantor'] }}" autofocus/>
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Jenis <span>*</span></div>
													<input type="text" name="jenis_company" placeholder="Jenis Company" value="{{ $results['results']['kantor']['jenis'] }}"/>
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Alamat <span>*</span></div>
													<input type="text" name="alamat_company" placeholder="Alamat Company" value="{{ $results['results']['kantor']['alamat'] }}"/>
												</div>
											</div>
											<div class="col-md-12 bg_form_page">
												<div class="form_input text-left">
													<div class="tag_title">Email <span>*</span></div>
													<input type="email" name="email_company" placeholder="Email Company" value="{{ $results['results']['kantor']['email'] }}"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Logo Perusahaan</div>
                                                    <img 
                                                        src="{{ $results['results']['kantor']['foto'] ? asset('/themes/admin/AdminOne/image/public/'.$results['results']['kantor']['foto']) : asset('/themes/admin/AdminOne/image/public/icon.png') }}"
                                                        alt="Logo" srcimg="logo_company" onclick="OpenFile('form_data','logo_company')">

												<input type="file" accept="image/*" name="logo_company" placeholder="Logo Company"/>
												<div class="btn_200">
													<button type="button" class="btn btn-default" onclick="OpenFile('form_data','logo_company')">Upload Logo</button>
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
                    function DeleteData() {
                        $('div[data-model="confirmasi"]').modal({backdrop: false});
                        $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['kantor']['kantor'] }}.</div>');
                        $('button[btn-action="aciton-confirmasi"]').remove();
                        $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                        $('button[btn-action="aciton-confirmasi"]').click(function(){
                            if($('button[btn-action="aciton-confirmasi"]').click){
                                loadingpage(20000);
                                window.location.href = "/admin/deletecompany?d={{ $results['results']['kantor']['id'] }}";
                            }
                        });
                    }
                </script>
            @endsection
@endsection