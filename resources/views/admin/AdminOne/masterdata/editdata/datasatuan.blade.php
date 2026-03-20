@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Satuan')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Satuan
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editsatuan'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0)  @if($level_user['deletesatuan'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif @endif
                                        @endif
									</div>
								</div>
							</div>
						</div>
                        <div class="col-md-12 bg_page_main" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editsatuan">
                                    {{csrf_field()}}
                                    <div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['satuan']['id'] }}" readonly="true" style="display: none;" />
                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nama" class="col-sm-4 col-form-label">Nama Satuan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="nama" placeholder="Nama Satuan"  value="{{$results['results']['satuan']['nama']}}" autofocus/>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="isi_satuan" class="col-sm-4 col-form-label">Isi Satuan <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="isi_satuan" placeholder="Isi Satuan"  disabled="true" value="{{$results['results']['satuan']['isi']}}" autofocus/>
                                                </div>
                                            </div>
                                        </div> 
										<div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
												<label for="status_pecahan" class="col-sm-4 col-form-label">Status Pecahan <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="status_pecahan" placeholder="Status Pecahan"  disabled="true" value="{{$results['results']['satuan']['isi']}}">		
                                                            <option value="Tidak aktif">Tidak Aktif</option>	
                                                            <option value="Aktif">Aktif</option>
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
                        $('select[name="status_pecahan"] option[value="{{$results['results']['satuan']['status_pecahan']}}"]').prop("selected", true);

                        @if($level_user['editgudang'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif

                        @if($results['results']['satuan']['status_pecahan'] == 'Aktif')
                            $('select[name="status_pecahan"] option[value="Aktif"]').prop("selected", true);
                            $('.content').append('<div class="col-md-6 bg_form_page" line="satuan_pecahan"><div class="form-group row form_input text-left"><label for="satuan_pecahan" class="col-sm-4 col-form-label">Satuan Pecahan <span>*</span></label><div class="col-sm-8 input"><select name="satuan_pecahan" placeholder="Satuan Pecahan"></select></div></div></div>');
                            $.getJSON("/admin/getsatuanpecahan", function(results){
                                $.each( results.results.detail, function(key,val ) {
                                    $('select[name="satuan_pecahan"]').append('<option value=' + val.id + '>' + val.nama + '</option>');
                                    $('select[name="satuan_pecahan"] option[value="{{$results['results']['satuan']['kode_pecahan']}}"]').prop("selected", true);
                                });
                            });                            
                        @elseif($results['results']['satuan']['status_pecahan'] == 'Tidak Aktif' )
                            $('select[name="status_pecahan"] option[value="Tidak Aktif"]').prop("selected", true);
                        @endif
                    });

                    function DeleteData() {
                        $('div[data-model="confirmasi"]').modal({backdrop: false});
                        $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['satuan']['nama'] }}.</div>');
                        $('button[btn-action="aciton-confirmasi"]').remove();
                        $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                        $('button[btn-action="aciton-confirmasi"]').click(function(){
                            if($('button[btn-action="aciton-confirmasi"]').click){
                                loadingpage(20000);
                                window.location.href = "/admin/deletesatuan?d={{ $results['results']['satuan']['id'] }}";
                            }
                        });
                    }
                </script>
            @endsection
@endsection