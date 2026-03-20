@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Karyawan')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Karyawan
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editkaryawan'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($level_user['deletekaryawan'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif
                                        @endif
									</div>
								</div>
							</div>
						</div>
                        <div class="col-md-12 bg_page_main" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editkaryawan">
                                    {{csrf_field()}}
                                    <div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['karyawan']['id'] }}" readonly="true" style="display: none;" />
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama_karyawan" class="col-sm-6 col-form-label">Nama Karyawan <span>*</span></label>
												<div class="col-sm-6 input">
													<input type="text" name="nama_karyawan" placeholder="Nama Karyawan" value="{{$results['results']['karyawan']['nama']}}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="tempat_lahir" class="col-sm-5 col-form-label">Tempat Lahir <span>*</span></label>
												<div class="col-sm-7 input">
													<input type="text" name="tempat_lahir" placeholder="Tempat Lahir" value="{{$results['results']['karyawan']['tempat_lahir']}}" autofocus/>
												</div>
											</div>
										</div>
                                        <div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tanggal_lahir"  class="col-sm-5 col-form-label">Tanggal Lahir <span>*</span></label>
                                                <div class="col-sm-7 input">
                                                <input class="pointer" type="text" name="tanggal_lahir" placeholder="Tanggal Lahir" value="<?php echo Date::parse($results['results']['karyawan']['tanggal_lahir'])->format('d F Y'); ?>" readonly="true"/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="jenis_kelamin" class="col-sm-6 col-form-label">Jenis Kelamin <span>*</span></label>
                                                <div class="col-sm-6 input">
                                                    <select name="jenis_kelamin" placeholder="Pilih Jenis Kelamin"  value="{{$results['results']['karyawan']['jenis_kelamin']}}">								
                                                            <option value="" style="display:none;">Jenis Kelamin</option>
                                                            <option value="L">Laki-Laki</option>	
                                                            <option value="P">Perempuan</option>
                                                    </select>
                                                </div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="jabatan" class="col-sm-5 col-form-label">Jabatan <span>*</span></label>
												<div class="col-sm-7 input">
													<input type="text" name="jabatan" placeholder="Jabatan" value="{{$results['results']['karyawan']['jabatan']}}" autofocus/>
												</div>
											</div>
										</div>                           
                                        <div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="no_hp" class="col-sm-5 col-form-label">No Handphone <span>*</span></label>
                                                <div class="col-sm-7 input">
                                                    <input type="text" name="no_hp" placeholder="No Handphone" value="{{$results['results']['karyawan']['no_hp']}}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
                                                </div>
                                            </div>
                                        </div> 
										<div class="col-md-12 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="alamat" class="col-sm-2 col-form-label">Alamat <span>*</span></label>
												<div class="col-sm-10 input">
													<input type="text" name="alamat" placeholder="Alanat" value="{{$results['results']['karyawan']['alamat']}}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
											<div class="form_input text-left">
												<div class="bg_checkboxlios">
													<div class="tag_title">Status Karyawan </div>
													<div class="checkboxlios" title="{{$results['results']['karyawan']['status_data']}}">
														<input type="text" name="status_data" value="" style="display:none;" />
														<input type="checkbox" class="ios" name="btncheckbox" btn="btncheckbox"/>
													</div>
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
						$('input[name="status_data"]').val('{{$results['results']['karyawan']['status_data']}}');
						if($('input[name="status_data"]').val() == 'Aktif' ){
							$('input[name="btncheckbox"]').prop('checked', true);
						}	

						$(".ios").iosCheckbox();
						$('input[btn="btncheckbox"]').on('click', function(){
							if($(this).is(':checked')){
								$('input[name="status_data"]').val('Tidak Aktif');
							}else{
								$('input[name="status_data"]').val('Aktif');
							}
						});

                        $('input[name="tanggal_lahir"]').datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-65y',
                            endDate: '65y',
                            autoclose : true,
                            language: "id",
                            orientation: "bottom"
                        });

						$('select[name="jenis_kelamin"] option[value="{{$results['results']['karyawan']['jenis_kelamin']}}"]').prop("selected", true);
                    });

					function DeleteData() {
						$('div[data-model="confirmasi"]').modal({backdrop: false});
						$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['karyawan']['nama'] }}.</div>');
						$('button[btn-action="aciton-confirmasi"]').remove();
						$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
						$('button[btn-action="aciton-confirmasi"]').click(function(){
							if($('button[btn-action="aciton-confirmasi"]').click){
								loadingpage(20000);
								window.location.href = "/admin/deletekaryawan?d={{ $results['results']['karyawan']['id'] }}";
							}
						});
					}
                </script>
            @endsection
@endsection