@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Pengguna')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Pengguna
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>

										@if($level_user['editusers'] == 'Yes')
											<button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

											@if($level_user['deleteusers'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif
										@endif
                                        
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main" line="form_action">
							<div class="col-md-12 data_page">
								<form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editusers">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $detailadmin['id'] }}" readonly="true" style="display: none;" />
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Nama Lengkap <span>*</span></div>
												<input type="text" name="full_name" placeholder="Nama Lengkap" value="{{$detailadmin['full_name']}}" autofocus/>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Nomor Handphone <span>*</span></div>
												<input type="text" name="phone_number" placeholder="Nomor Handphone" value="{{$detailadmin['phone_number']}}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Alamat Email <span>*</span></div>
												<input type="email" name="email" placeholder="Alamat Email" value="{{$detailadmin['email']}}" autofocus/>
											</div>
										</div>
										<div class="col-md-6 bg_form_page">
											<div class="form_input text-left">
												<div class="tag_title">Level Pengguna <span>*</span></div>
												<select name="level" placeholder="Level Pengguna">
													@foreach ($list_level as $view_data)
														<option value="{{$view_data['code_data']}}" @if($detailadmin['level'] == $view_data['code_data']) selected="true" @endif>{{$view_data['level_name']}} </option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
											<div class="form_input text-left">
												<div class="bg_checkboxlios">
													<div class="tag_title">Status Pengguna </div>

													<div class="checkboxlios" title="{{$detailadmin['status_data']}}">
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
                        @if($detailadmin['id'] == 'bd050931-d837-11eb-8038-204747ab6caa')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif
                        @if($level_user['editusers'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif
                        @if($detailadmin['id'] == session('admin_login'))
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif
						$('input[name="status_data"]').val('{{$detailadmin['status_data']}}');
						if($('input[name="status_data"]').val() == 'Aktif' ){
							$('input[name="btncheckbox"]').prop('checked', true);
						}	

                        // $('select[name="level"] option[value="{{$detailadmin['level']}}"]').prop("selected", true);

						$(".ios").iosCheckbox();
						$('input[btn="btncheckbox"]').on('click', function(){
							if($(this).is(':checked')){
								$('input[name="status_data"]').val('Tidak Aktif');
							}else{
								$('input[name="status_data"]').val('Aktif');
							}
						});
                    });
                    function DeleteData() {
                        $('div[data-model="confirmasi"]').modal({backdrop: false});
                        $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{$detailadmin['full_name']}}.</div>');
                        $('button[btn-action="aciton-confirmasi"]').remove();
                        $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                        $('button[btn-action="aciton-confirmasi"]').click(function(){
                            if($('button[btn-action="aciton-confirmasi"]').click){
                                loadingpage(20000);
                                window.location.href = "/admin/deleteusers?d={{$detailadmin['id']}}";
                            }
                        });
                    }
                </script>
            @endsection
@endsection