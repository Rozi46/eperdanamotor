@extends('admin.AdminOne.layout.assets')
@section('title', 'Manual Book')

@section('content')

			<div class="page_main">
				<div class="container text-left">
					<div class="row">
						<div class="col-md-12 bg_page_main hd" line="hd_action">
							<div class="col-md-12 hd_page_main">Manual Book</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
                                        @if($res_user['level'] == 'LV5677001' && $results['results']['setting']['manual_book'] != Null)
                                           <button type="button" class="btn btn-primary" onclick="viewmanualbook()">View Data</button> 
                                           <button type="button" class="btn btn-info back" onclick="downloadData()"><i class="fa fa-download"></i> Download Data</button>
                                        @endif
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12 bg_page_main dt" line="form_action">
							<div class="col-md-12 data_page">
								<form id="uploadForm" method="post" name="form_data" enctype="multipart/form-data" action="/admin/uploadmanualbook">
									{{csrf_field()}}
									<div class="row bg_data_page form_page content">
										@if($res_user['level'] == 'LV5677001') 
                                            <input type="file" name="manual_book" id="manual_book" class="d-none" accept="application/pdf" onchange="submitForm()">                                           
                                            <div class="col-md-12 bg_form_page">
                                                <div class="form-group row form_input text-left">
                                                    <label for="NamaManualBook" class="col-sm-2 col-form-label">Upload Manual Book (.pdf)</label>
                                                    <div class="col-sm-10 input">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success" style="margin-top: 0px;" onclick="UploadData()">Upload</button>
                                                        </div>
                                                        <input type="text" name="NamaManualBook" placeholder="Manual Book" value="{{ $results['results']['setting']['manual_book'] ?? 'Belum ditentukan' }}" readonly="true" />
                                                    </div>
                                                </div>
                                            </div>
										@endif
									</div>  
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <iframe 
                                                src="{{ isset($results['results']['setting']['manual_book']) ? asset('themes/admin/AdminOne/ManualBook/' . $results['results']['setting']['manual_book'] . '?token=' . $request['token']) : '#' }}" 
                                                style="width: 100%; min-height: 80vh; border: 1px solid #ddd;" 
                                                title="Manual Book">
                                            </iframe>
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
                        $('form :input').prop('disabled', true);       
                        @if($res_user['level'] == 'LV5677001')
                            $('form :input').prop('disabled', false);
                        @endif
                    });

                    function UploadData() {
                        document.getElementById('manual_book').click(); 
                    } 

                    function submitForm() {
                        const fileInput = document.getElementById('manual_book');
                        const file = fileInput.files[0];

                        if (file && file.type === 'application/pdf') {
                            document.getElementById('uploadForm').submit();
                        } else {
                            alert('Harap unggah file PDF yang valid.');
                            fileInput.value = ''; 
                        }
                    }

                    function viewmanualbook() {
                        window.open("/admin/viewmanualbook?d={{ $results['results']['setting']['manual_book'] }}");
                    }

                    function downloadData() {
                        window.location.href =("/admin/downloadmanualbook?d={{ $results['results']['setting']['manual_book'] }}");
                    }
                </script>
            @endsection
@endsection