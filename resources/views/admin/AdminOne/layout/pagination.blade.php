                                    <div class="col-xl-12 col_act_page_main text-right">
										<input type="text" id="app_load" style="display:none;" value="{{ $url_active }}" />
										<input type="text" class="search" name="key-search" placeholder="Cari data..." style="display:none;" value="{{ $keysearch }}" />

										<button type="button" btn="openSearch" class="btn btn-default btn_nav" onclick="openSearch()" title="Cari Data"><i class="fa fa-search"></i></button>

                                        <input type="text" class="in_btn" id="countvd" value="{{ $count_vd }}" title="Jumlah Data Perpage"/> / {{ $results['total'] }}

										<a load="true" href="{{ $url_active }}?page=1&vd={{ $count_vd }}&keysearch={{ $keysearch }}{{ $searchdate ?? '' }}" title="Page Awal" line="btn_page_awal"><button type="button" class="btn btn-default btn_nav @if($results['current_page'] != '1') active @endif" @if($results['current_page'] == '1') disabled="true" @endif> <i class="fa fa-angle-double-left"></i> </button></a>

										@if($results['current_page'] != '1')<a load="true" href="{{ $url_active }}?page={{ $results['current_page'] - 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}{{ $searchdate ?? '' }}" title="Page {{ $results['current_page'] - 1 }}" line="btn_page_min"><button type="button" class="btn btn-default btn_nav active"> {{ $results['current_page'] - 1 }}  </button></a>@endif

										<button type="button" class="btn btn-default btn_nav" title="Page {{ $results['current_page'] }}">{{ $results['current_page'] }}</button>

										@if($results['current_page'] != $results['last_page'])<a load="true" href="{{ $url_active }}?page={{ $results['current_page'] + 1 }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}{{ $searchdate ?? '' }}" title="Page {{ $results['current_page'] + 1 }}" line="btn_page_plus"><button type="button" class="btn btn-default btn_nav active"> {{ $results['current_page'] + 1 }} </button></a>@endif

										<a load="true" href="{{ $url_active }}?page={{ $results['last_page'] }}&vd={{ $count_vd }}&keysearch={{ $keysearch }}{{ $searchdate ?? '' }}" title="Page Akhir" line="btn_page_akhir"><button type="button" class="btn btn-default btn_nav @if($results['current_page'] != $results['last_page'] ) active @endif" @if($results['current_page'] == $results['last_page'] ) disabled="true" @endif> <i class="fa fa-angle-double-right"></i> </button></a>
                                        
									</div>