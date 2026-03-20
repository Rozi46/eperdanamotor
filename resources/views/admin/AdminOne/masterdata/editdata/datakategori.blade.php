@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Kategori')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Kategori
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editkategori'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0)  @if($level_user['deletekategori'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif @endif
                                        @endif
									</div>
                                </div>
							</div>
						</div>
                        <div class="col-md-12 bg_page_main" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editkategori">
                                    {{csrf_field()}}
                                    <div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['kategori']['id'] }}" readonly="true" style="display: none;" />
                                        <div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="nama" class="col-sm-2 col-form-label">Nama Kategori <span>*</span></label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="nama" placeholder="Nama Kategori"  value="{{$results['results']['kategori']['nama']}}" autofocus/>
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
                        @if($level_user['editkategori'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif	
                    });

                    function DeleteData() {
                        $('div[data-model="confirmasi"]').modal({backdrop: false});
                        $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['kategori']['nama'] }}.</div>');
                        $('button[btn-action="aciton-confirmasi"]').remove();
                        $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                        $('button[btn-action="aciton-confirmasi"]').click(function(){
                            if($('button[btn-action="aciton-confirmasi"]').click){
                                loadingpage(20000);
                                window.location.href = "/admin/deletekategori?d={{ $results['results']['kategori']['id'] }}";
                            }
                        });
                    }
                </script>
            @endsection
@endsection