@extends('admin.AdminOne.layout.assets')
@section('title', 'Ubah Data Barang')

@section('content')

			<div class="page_main">
                <div class="container-fluid text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">
                            Ubah Data Barang
							</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-arrow-left"></i> Kembali</button>
                                        @if($level_user['editbarang'] == 'Yes')
                                            <button type="button" class="btn btn-primary" name="btn_save" onclick="loadingpage(20000),SaveData('form_data')">Simpan Data</button>

                                            @if($results['count_used'] == 0)  @if($level_user['deletebarang'] == 'Yes')<button type="button" class="btn btn-danger" name="btn_del" onclick="DeleteData()">Hapus Data</button>@endif @endif
                                        @endif
									</div>
								</div>
							</div>
						</div>
                        <div class="col-md-12 bg_page_main" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/editbarang">
                                    {{csrf_field()}}
                                    <div class="row bg_data_page form_page content">
										<input type="text" name="id_data" value="{{ $results['results']['barang']['id'] }}" readonly="true" style="display: none;" />
                                        <div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="kode_barang" class="col-sm-4 col-form-label">Kode Barang <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="kode_barang" placeholder="Kode Barang"  value="{{$results['results']['barang']['kode']}}" autofocus/>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="nama" class="col-sm-4 col-form-label">Nama Barang <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="nama" placeholder="Nama Barang" value="{{$results['results']['barang']['nama']}}" autofocus/>
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
                                                <label for="kategori" class="col-sm-4 col-form-label">Kategori <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="kategori" placeholder="Kategori">
                                                        <option value="">Pilih Kategori</option>
                                                        @foreach ($list_kategori as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="merk" class="col-sm-4 col-form-label">Merk <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="merk" placeholder="Merk">
                                                        <option value="">Pilih Merk</option>
                                                        @foreach ($list_merk as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="supplier" class="col-sm-4 col-form-label">Supplier <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <select name="supplier" placeholder="Supplier">
                                                        <option value="">Pilih Supplier</option>
                                                        @foreach ($list_supplier as $view_data)
                                                            <option value="{{$view_data['id']}}">{{$view_data['nama']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="harga_beli" class="col-sm-4 col-form-label">Harga Beli <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="harga_beli" placeholder="Harga Beli" value="{{$results['results']['barang']['harga_beli']}}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="harga_jual" class="col-sm-4 col-form-label">Harga Jual <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="harga_jual" placeholder="Harga Jual" value="{{$results['results']['barang']['harga_jual1']}}"  onKeyPress="return goodchars(event,'0123456789,',this)"/>
												</div>
											</div>
										</div>
										<div class="col-md-4 bg_form_page">
											<div class="form-group row form_input text-left">
												<label for="harga_khusus" class="col-sm-4 col-form-label">Harga Khusus <span>*</span></label>
												<div class="col-sm-8 input">
													<input type="text" name="harga_khusus" placeholder="Harga Khusus" value="{{$results['results']['barang']['harga_jual2']}}" onKeyPress="return goodchars(event,'0123456789,',this)"/>
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
                        @if($level_user['editbarang'] == 'No')
                            $('form[name="form_data"] input').prop({disabled:true});
                            $('form[name="form_data"] select').prop({disabled:true});
                            $('button[name="btn_save"]').remove();
                            $('button[name="btn_del"]').remove();
                        @endif

                        $('select[name="satuan"] option[value="{{ $results['results']['barang']['kode_satuan']}}"]').prop("selected", true); 
                        $('select[name="kategori"] option[value="{{ $results['results']['barang']['kode_jenis']}}"]').prop("selected", true);   
                        $('select[name="merk"] option[value="{{ $results['results']['barang']['kode_brand']}}"]').prop("selected", true);  
                        $('select[name="supplier"] option[value="{{ $results['results']['barang']['kode_supplier']}}"]').prop("selected", true); 
                    });

                    function DeleteData() {
                        $('div[data-model="confirmasi"]').modal({backdrop: false});
                        $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-danger">Anda yakin untuk menghapus data {{ $results['results']['barang']['nama'] }}.</div>');
                        $('button[btn-action="aciton-confirmasi"]').remove();
                        $('button[btn-action="close-confirmasi"]').before('<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>');
                        $('button[btn-action="aciton-confirmasi"]').click(function(){
                            if($('button[btn-action="aciton-confirmasi"]').click){
                                loadingpage(20000);
                                window.location.href = "/admin/deletebarang?d={{ $results['results']['barang']['id'] }}";
                            }
                        });
                    }
                </script>
            @endsection
@endsection