@extends('admin.AdminOne.layout.assets')
@section('title', 'Tambah Data Jasa')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Tambah Data Jasa
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
										@if($level_user['newjasa'] == 'Yes')<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>@endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/newjasa">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="kode_barang" class="col-sm-4 col-form-label">Kode Jasa <span>*</span></label>
												<div class="col-sm-8 input">
													<div class="input-group-append">
														<label name = generate>
														<div class="input-group-text">Generate</div></label>
													</div>
													<input type="text" name="kode_barang" placeholder="Kode Jasa" value="" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama" class="col-sm-4 col-form-label">Nama Jasa <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama" placeholder="Nama Jasa" value="{{ old('nama') }}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="satuan" class="col-sm-4 col-form-label">Satuan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="satuan" placeholder="Satuan">
                                                        <option value="">Pilih Satuan</option>
                                                        @foreach ($list_satuan as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="harga_beli" class="col-sm-4 col-form-label">Modal Jasa <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="harga_beli" placeholder="Modal Jasa" value="0"  onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="harga_jual" class="col-sm-4 col-form-label">Biaya Jasa <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="harga_jual" placeholder="Biaya Jasa" value="0"   onKeyPress="return goodchars(event,'0123456789,',this)"/>
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
						$('label[name="generate"]').click(function(){
							if($('label[name="generate"]').click){
								$('input[name="kode_barang"]').val('{{$results['results']}}');
							}
						});
					});
                </script>
            @endsection
@endsection