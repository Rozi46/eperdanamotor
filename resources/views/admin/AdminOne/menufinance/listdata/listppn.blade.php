@extends('admin.AdminOne.layout.assets')
@section('title', 'Data PPN')

@include('admin.AdminOne.layout.function')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_page_main hd">
                            <div class="col-md-12 hd_page_main">Data PPN</div>
							<div class="col-md-12 bg_act_page_main">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left">
										<button type="button" class="btn btn-default back" onclick="BackPage()"><i class="fa fa-chevron-left"></i> Kembali</button>
										@if($level_user['exportppn'] == 'Yes')<button type="button" class="btn btn-info back" onclick="exportdata('ppn')"><i class="fa fa-download"></i> Export Data</button>@endif
									</div>
								</div>
							</div>
                        </div>
						<div class="col-md-12 bg_page_main dt">
							<div class="col-md-12 bg_act_page_main page">
								<div class="row">
									<div class="col-xl-12 col_act_page_main text-left" style="margin-bottom:6px;">
										Filter Data
                                        <select name="nama_perusahaan" placeholder="Nama Perusahaan" style="width: 240px; padding-top: 8px; padding-bottom: 6px;">
                                            <option value="" {{ empty($nama_perusahaan) ? 'selected' : '' }}>Semua Perusahaan</option>
                                            @foreach ($list_cabang as $view_data)
                                                <option value="{{ $view_data['id'] }}" {{ $nama_perusahaan == $view_data['id'] ? 'selected' : '' }}>
                                                    {{ $view_data['nama_cabang'] }}
                                                </option>
                                            @endforeach
                                        </select>
										<input type="text" name="tahun" placeholder="Tahun" style="width: 90px; text-align: center; padding-left: 0px; cursor: pointer;"  value="<?php echo $tahun; ?>" />
										<button type="button" class="btn btn-default filter" onclick="datafilter()">Filter</button>
									</div>
								</div>
							</div>
							<div class="col-md-12 data_page">
								<div class="row bg_data_page">
									<div class="table_data freezeHead freezeCol">
										<table class="table_view table-striped table-hover">
											<thead>
												<tr>
													<th style="width:30px; text-align: center;">No</th>
													<th style="width:200px; text-align: center;">Bulan</th>
													<th style="min-width:100px; text-align: center;">Pembelian</th>
                                                    <th style="min-width:100px; text-align: center;">Penjualan</th>
												</tr>
											</thead>
											<tbody>  
                                                <?php
                                                    $css_in = 'background-color: #932C25; color: #FFFFFF;';
                                                    $css_out = 'background-color: #3789C1; color: #FFFFFF;';
                                                ?>
                                                @for ($x = 1; $x <= 12; $x++)                                                           
                                                    <tr>
                                                        <td style="text-align:center;">{{$x}}</td>
                                                        <td>{{ $listdata['months'.$x] ?? '' }}</td>
                                                        <td style="width:200px; text-align: right;">{{ number_format($listdata['pembelian'.$x] ?? 0,2,",",".") }}</td>
                                                        <td style="width:200px; text-align: right;">{{ number_format($listdata['penjualan'.$x] ?? 0,2,",",".") }}</td>
                                                    </tr>
                                                @endfor 

                                                <tr>
                                                    <td style="text-align: right; cursor: default; font-size:15px; font-weight: 600;" colspan="2"><strong>Total :</strong></td>
                                                    <td style="text-align: right; cursor: default; font-size:15px; font-weight: 600;" ><strong>{{ number_format($listdata['sum_pembelian'] ?? 0,2,",",".") }}</strong></td>
                                                    <td style="text-align: right; cursor: default; font-size:15px; font-weight: 600;" ><strong>{{ number_format($listdata['sum_penjualan'] ?? 0,2,",",".") }}</strong></td>
                                                </tr> 
                                                  
                                                <tr>
                                                    <td style="text-align: right; cursor: default; font-size:15px; font-weight: 600;" colspan="2"><strong>Terbilang :</strong></td>
                                                    <td style="text-align: left; cursor: default; font-size:15px; font-weight: 600; font-style: italic;" >{{ ($listdata['sum_pembelian'] ?? 0) == 0 ? 'Nol rupiah' : terbilang($listdata['sum_pembelian']) . ' rupiah' }}
                                                    </td>
                                                    <td style="text-align: left; cursor: default; font-size:15px; font-weight: 600; font-style: italic;" >{{ ($listdata['sum_penjualan'] ?? 0) == 0 ? 'Nol rupiah' : terbilang($listdata['sum_penjualan']) . ' rupiah' }}</td>                                                
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
                    </div>
                </div>
            </div>


            @section('script')
                <script type="text/javascript">
                    $(document).ready(function(){
                        // $('input[name="tahun"]').val('<?php echo date('Y');?>'); 
                        // $('select[name="nama_perusahaan"] option[value=""]').prop("selected", true); 
                        // $('select[name="nama_perusahaan"] option[value="2adaa199-aa81-11ed-8a36-0045e27a3ed8"]').prop("selected", true);
                    });

                    function datafilter() {                        
						loadingpage(2000);
                        var nama_perusahaan = $('select[name="nama_perusahaan"]').val();
                        var tahun = $('input[name="tahun"]').val();
                        
                        window.location.href = "/admin/menuppn?d={{$request['d']}}&nama_perusahaan="+nama_perusahaan+"&tahun="+tahun;
                    }

                    function exportdata() {
                        var nama_perusahaan = $('select[name="nama_perusahaan"]').val();
                        var tahun = $('input[name="tahun"]').val();
                        
                        window.location.href = "/admin/exportppn?d={{$request['d']}}&nama_perusahaan="+nama_perusahaan+"&tahun="+tahun;
                    }
                </script>
            @endsection
@endsection