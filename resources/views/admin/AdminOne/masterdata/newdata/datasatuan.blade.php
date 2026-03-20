@extends('admin.AdminOne.layout.assets')
@section('title', 'Tambah Data Satuan')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Tambah Data Satuan
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
										@if($level_user['newsatuan'] == 'Yes')<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>@endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/newsatuan">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama" class="col-sm-4 col-form-label">Nama Satuan <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama" placeholder="Nama Satuan" value="{{ old('nama') }}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="isi_satuan" class="col-sm-4 col-form-label">Isi Satuan <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="isi_satuan" placeholder="Isi Satuan" value="{{ old('isi_satuan') }}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="status_pecahan" class="col-sm-4 col-form-label">Status Pecahan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="status_pecahan" placeholder="Status Pecahan"  value=>		
                                                            <option value="Tidak aktif">Tidak Aktif</option>	
                                                            <option value="Aktif">Aktif</option>
                                                    </select>
                                                </div>
											</div>
										</div>
										<div class="col-md-6 bg_form_page" line="satuan_pecahan" style="display:none;">
                                            <div class="form-group row form_input text-left">
												<label for="satuan_pecahan" class="col-sm-4 col-form-label">Satuan Pecahan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="satuan_pecahan" placeholder="Satuan Pecahan">		
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
                        $('select[name="status_pecahan"]').change(function(){
                            $('[line="satuan_pecahan"]').show();
                            var tipe = $('select[name="status_pecahan"]').val();
                            if(tipe == 'Aktif'){
								$('[for="satuan_pecahan"]').html('Satuan Pecahan <span>*</span>');
								$('select[name="satuan_pecahan"]').html('<option value="">Pilih Satuan Pecahan</option>');
                                $.getJSON("/admin/getsatuanpecahan", function(results){
                                    $.each( results.results.detail, function(key,val ) {
                                        $('select[name="satuan_pecahan"]').append('<option value=' + val.id + '>' + val.nama + '</option>');

                                    });
                                });
                            }else{
                                $('[line="satuan_pecahan"]').hide();
								$('select[name="satuan_pecahan"]').html('');
                            }
                        });
					});
                </script>
			@endsection
@endsection