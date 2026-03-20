@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Customer')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Customer
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editcustomer'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0)  @if($level_user['deletecustomer'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif @endif
                                        @endif
									</div>
								</div>
							</div>
						</div>
                        <div class="col-md-12 bg_page_main" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editcustomer">
                                    {{csrf_field()}}
                                    <div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['customer']['id'] }}" readonly="true" style="display: none;" />
										<div class="col-md-6 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama" class="col-sm-4 col-form-label">Nama Customer <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama" placeholder="Nama Customer" value="{{$results['results']['customer']['nama']}}" autofocus/>
												</div>
											</div>
										</div>                          
                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="no_hp" class="col-sm-4 col-form-label">No Handphone <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="no_hp" placeholder="No Handphone" value="{{$results['results']['customer']['no_telp']}}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
                                                </div>
                                            </div>
                                        </div> 
										<div class="col-md-12 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="alamat" class="col-sm-2 col-form-label">Alamat <span>*</span></label>
												<div class="col-sm-10 input">
													<input type="text" name="alamat" placeholder="Alanat" value="{{$results['results']['customer']['alamat']}}" autofocus/>
												</div>
											</div>
										</div>
										<div class="col-md-12 bg_form_page">
											<div class="form_input text-left">
												<div class="bg_checkboxlios">
													<div class="tag_title">Status Customer </div>
													<div class="checkboxlios" title="{{$results['results']['customer']['status_data']}}">
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
                        @if($level_user['editcustomer'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif

						$('input[name="status_data"]').val('{{$results['results']['customer']['status_data']}}');
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
                    });

					function DeleteData() {
						$('div[data-model="confirmasi"]').modal({backdrop: false});
						$('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['customer']['nama'] }}.</div>');
						$('button[btn-action="aciton-confirmasi"]').remove();
						$('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
						$('button[btn-action="aciton-confirmasi"]').click(function(){
							if($('button[btn-action="aciton-confirmasi"]').click){
								loadingpage(20000);
								window.location.href = "/admin/deletecustomer?d={{ $results['results']['customer']['id'] }}";
							}
						});
					}
                </script>
            @endsection
@endsection