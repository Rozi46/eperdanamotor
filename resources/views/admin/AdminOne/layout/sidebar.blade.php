
            <div data-page="assets-page" class="assets-page">
                <div class="container-fluid text-left">
                    <div class="row content-page">
                        <div class="btnSidebarMenu" id="btnSidebarMenu" onclick="closeSidebar()">
                            <i class="fa fa-bars"></i>
                        </div>
                        <div class="sidebar" id="sidebar">
                            <div class="bg_logo_sidebar">
                                <a load="true" class="logo_sidebar" href="dash">
                                    <!-- <img src="{{ asset('/themes/admin/AdminOne/image/public/logo.png') }}" alt="Logo"> -->
                                </a>
                            </div>
                            <div class="bg_sidebar" id="bg_sidebar">
                                <!-- <div class="bg_logo_sidebar" data-foto="{{ $request['data_company']['foto'] ?? '' }}"> -->
                                    <!-- <img src="{{ asset('/themes/admin/AdminOne/image/public/logo.png') }}" alt="Logo"> -->
                                <!-- </div> -->
                                
                                <a load="true" href="dash" menu="dash">
                                    <div class="list_sidebar @if($app == 'dash') active @endif">
                                        <div class="txt"><i class="fa fa-tachometer"></i> Dashboard</div>
                                    </div>
                                </a>

                                @foreach ($list_akses['menu'] as $view_menu)
                                    <?php if($level_user[''.$view_menu['nama_akses'].''] == 'Yes'){?>
                                        <?php if($list_akses['count_used'][$view_menu['id']] >= 1){?>
                                            <?php if($view_menu['status_data'] == 'Aktif'){?>
                                                <div class="menu_induk list_sidebar @if($app == $view_menu['nama_akses']) active @endif" data-toggle="collapse" data-target="#{{$view_menu['nama_akses']}}" id="menu_induk">
                                                    <div class="txt"><i class="{{$view_menu['icon_menu']}}"></i> {{$view_menu['nama_menu']}}<span class="fa fa-chevron-down"></span></div>
                                                </div>
                                            <?php } ?>
                                        <?php }else{?>
                                            <?php if($view_menu['status_data'] == 'Aktif'){?>
                                                <a load="true" href="{{$view_menu['nama_akses']}}" menu="{{$view_menu['nama_akses']}}" id="menu_induk">
                                                    <div class="menu_induk list_sidebar @if($url_active == $view_menu['nama_akses']) active @endif">
                                                        <div class="txt"><i class="{{$view_menu['icon_menu']}}"></i> {{$view_menu['nama_menu']}}</div>
                                                    </div>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>

                                        <div class="sub_sidebar collapse @if($app == $view_menu['nama_akses']) show @endif" id="{{$view_menu['nama_akses']}}">
                                            @foreach ($list_akses['submenu'][$view_menu['id']] as $view_submenu)
                                                <?php if($level_user[''.$view_submenu['nama_akses'].''] == 'Yes'){?>
                                                    <?php if($view_submenu['status_data'] == 'Aktif'){?>
                                                        <a href="{{$view_submenu['nama_akses']}}" menu="{{$view_menu['nama_akses']}}">
                                                            <div class="list_sidebar pointer subsidebar @if($url_active == $view_submenu['nama_akses']) active @endif">
                                                                <div class="txt"><i class="{{$view_submenu['icon_menu']}}"></i> {{$view_submenu['nama_menu']}}</div>
                                                            </div>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            @endforeach
                                        </div>
                                    <?php } ?>
                                @endforeach
                                
                                @if($res_user['level'] == 'LV5677001')
                                    <div class="list_sidebar @if($app == 'setting') active @endif" data-toggle="collapse" data-target="#setting">
                                        <div class="txt"><i class="fa fa-cog"></i> Pengaturan <span class="fa fa-chevron-down"></span></div>
                                    </div>
                                    <div class="sub_sidebar pointer collapse @if($app == 'setting') show @endif" id="setting">
                                            <a load="true" href="settingmenu" menu="setting">
                                                <div class="list_sidebar pointer subsidebar @if($url_active == 'settingmenu') active @endif">
                                                    <div class="txt"><i class="fa fa-align-right"></i> Pengaturan Menu & Akses</div>
                                                </div>
                                            </a>
                                        <a href="listcompany" menu="listcompany">
                                            <div class="list_sidebar pointer subsidebar @if($url_active == 'listcompany') active @endif">
                                                <div class="txt"><i class="fa fa-align-right"></i> Perusahaan</div>
                                            </div>
                                        </a>
                                        <a href="manualbook" menu="manualbook">
                                            <div class="list_sidebar pointer subsidebar @if($url_active == 'manualbook') active @endif">
                                                <div class="txt"><i class="fa fa-align-right"></i> Manual Book</div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @yield('content')
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function(){
                    var menu = $('.menu_induk').length;
                    if ($('.menu_induk').length == 1) {
                        $('.menu_induk').addClass('active');
                        $(''+$('.menu_induk').attr("data-target")+'').addClass('show');
                    }
                });
            </script>