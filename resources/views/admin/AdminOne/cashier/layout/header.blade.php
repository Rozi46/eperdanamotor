
            <div class="bg_header">
                <nav class="navbar navbar-expand-sm bg-lightnavbar-dark fixed-top">
                    <div class="d-flex align-items-center">
                        <a load="true" class="navbar-brand" href="dash">
                            <img src="{{ $request['data_company']['foto'] == NULL ? asset('/themes/admin/AdminOne/image/public/logo.png') : asset('/themes/admin/AdminOne/image/public/'.$request['data_company']['foto']) }}" alt="Logo">
                        </a>
                        <div class="nm_company">{{ $request['data_company']['kantor'] }}</div>
                    </div>
                    <div class="collapse navbar-collapse" id="NavMenu">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a load="true" class="nav-link" href="/admin/downloadmanualbook?d={{ $request['manual_book'] }}"><i class="fa fa-book"></i>E-Book</a>
                            </li>
                            <li class="nav-item">
                                <a load="true" class="nav-link" href="/admin/editaccount"><img src="<?php if( $res_user['image'] == 'no_img'){echo asset('/themes/admin/AdminOne/image/no_image.jpg'); }else{echo asset('/themes/admin/AdminOne/image/upload/'.$res_user['image'].'');}?>" alt="User">{{ $request['nama_admin'] }}</a>
                            </li>
                            <li class="nav-item">
                                <a load="true" class="nav-link" href="/cash/logout"><i class="fa fa-power-off"></i> Keluar</a>
                            </li>
                        </ul>
                    </div>
                </nav> 
            </div>

            <div class="bg_loading" line="loadingpage">
                <div class="data_alert_page">
                    <div class="col-md-12 alert alert-info text-left" role="alert">
                        <i class="fa fa-refresh fa-spin"></i> Mohon menunggu...
                    </div>
                </div>
            </div>

            <div class="data_alert_page">
                @if (count($errors) > 0)
                    @foreach ($errors->all() as $error)
                        <div class="col-md-12 alert alert-danger text-left" role="alert">
                            {{ucfirst(strtolower($error))}}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endforeach
                @endif

                @if(session('success'))
                    <div class="col-md-12 alert alert-success" role="alert" style="padding-bottom:7px;">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="margin-top: -4px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="col-md-12 alert alert-danger" role="alert" style="padding-bottom:7px;">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="margin-top: -4px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <div line="alert_success" class="col-md-12 alert alert-success" role="alert" style="padding-bottom:7px; display:none;">
                    <span line="text_alert"></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="margin-top: -4px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div line="alert_danger" class="col-md-12 alert alert-danger" role="alert" style="padding-bottom:7px; display:none;">
                    <span line="text_alert"></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="margin-top: -4px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            
            <div class="modal fade" role="dialog" data-model="confirmasi">
                <div class="modal-dialog modal-ls">
                    <div class="modal-content">
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn-action="close-confirmasi">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- GLOBAL ALERT -->
            <div class="data_alert_page global-alert">
                <div line="alert_success" class="alert alert-success" style="display:none;"></div>
                <div line="alert_danger"  class="alert alert-danger"  style="display:none;"></div>
            </div>

            <!-- GLOBAL TOAST -->
            <div id="globalToast"></div>

            <!-- GLOBAL CONFIRM -->
            <div class="modal fade" id="globalConfirm" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body text-center" id="confirmText"></div>
                        <div class="modal-footer justify-content-center">
                            <button class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button class="btn btn-danger btn-sm" id="confirmOk">Ya</button>
                        </div>
                    </div>
                </div>
            </div>