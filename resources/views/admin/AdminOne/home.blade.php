@extends('admin.AdminOne.layout.assets')
@section('title', 'Dashboard Administrasi')

@section('content')

            <div class="page_main">
                <div class="container-fluid text-left">
                    <div class="row">
                        <div class="col-md-12 bg_con_dash">
                            <div class="col-md-12 hd_page_main">DashboardRR</div>
                            <!-- <div class="col-md-12 _con_dash"> -->
                                <!-- <div class="row bg_con_dash">  -->
                                    <!-- @if($level_user['historypembelianbarang'] == 'Yes')
                                        <div class="col-md-6 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">History Pembelian</div>
                                                    <div class="val_con_dash _data">{{$listdata['count_pembelian']}}</div>
                                                    <div class="next_con_dash"><a href="historypembelianbarang">Lihat Semua <i class="fa fa-caret-right"></i></a></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($level_user['historypenjualanbarang'] == 'Yes')                               
                                        <div class="col-md-6 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">History Penjualan</div>
                                                    <div class="val_con_dash _data">{{$listdata['count_penjualan']}}</div>
                                                    <div class="next_con_dash"><a href="historypenjualanbarang">Lihat Semua <i class="fa fa-caret-right"></i></a></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif -->
                               <!-- </div> -->
                            <!-- </div> -->
                        </div>

                        @if($level_user['historypembelianbarang'] == 'Yes')
                            <div class="col-md-12 bg_con_dash">
                                <!-- <div class="col-md-12 hd_page_main">Pembelian</div> -->
                                <div class="col-md-12 _con_dash">
                                    <div class="row bg_con_dash">
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Pembelian Hari Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_pembelian_hari'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_pembelian_nilai_hari'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Pembelian Bulan Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_pembelian_bln'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_pembelian_nilai_bln'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Pembelian Tahun Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_pembelian_thn'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_pembelian_nilai_thn'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Total Hutang</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">{{ number_format($listdata['total_hutang_count'],0,",",".") }} Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.{{ number_format($listdata['total_hutang'],2,",",".") }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($level_user['historypenjualanbarang'] == 'Yes')
                            <div class="col-md-12 bg_con_dash">
                                <!-- <div class="col-md-12 hd_page_main">Penjualan</div> -->
                                <div class="col-md-12 _con_dash">
                                    <div class="row bg_con_dash">
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Penjualan Hari Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_penjualan_hari'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_penjualan_nilai_hari'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Penjualan Bulan Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_penjualan_bln'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_penjualan_nilai_bln'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Penjualan Tahun Ini</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;"><?php echo number_format($listdata['summary_penjualan_thn'],0,",",".") ?> Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.<?php echo number_format($listdata['summary_penjualan_nilai_thn'],2,",",".") ?></div>
                                                </div>
                                            </div>
                                        </div>                              
                                        <div class="col-md-3 _con_dash">
                                            <div class="col-md-12 bg_con_dash">
                                                <div class="col-md-12 con_dash">
                                                    <div class="hd_con_dash">Total Tagihan</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">{{ number_format($listdata['total_piutang_count'],0,",",".") }} Transaksi</div>
                                                    <div class="val_con_dash _data" style="font-size: 16px; font-weight: 600;">Rp.{{ number_format($listdata['total_piutang'],2,",",".") }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                      
                        <!-- <div class="col-md-12 bg_con_dash">
                            <div class="col-md-12 hd_page_main">Grafik Jumlah Pembelian & Penjualan Bulan Ini</div>
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart1"></div>
                                                <div class="next_con_dash"><a href="app?load=laporanpembelian&page=1&vd=10&keysearch=&searchdate={{$listdata['thn_now']}}-{{$listdata['bln_now']}}-1sd{{$listdata['thn_now']}}-{{$listdata['bln_now']}}-31&sp=">Lihat Semua <i class="fa fa-caret-right"></i></a></div>                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <!-- <div class="col-md-12 bg_con_dash">
                            <div class="col-md-12 hd_page_main">Grafik Summary Pembelian & Penjualan Bulan Ini</div>
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart2"></div>
                                                <div class="next_con_dash"><a href="app?load=laporanpenjualan&page=1&vd=10&keysearch=&searchdate={{$listdata['thn_now']}}-{{$listdata['bln_now']}}-1sd{{$listdata['thn_now']}}-{{$listdata['bln_now']}}-31&tpso=&pl=">Lihat Semua <i class="fa fa-caret-right"></i></a></div>                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="col-md-6 bg_con_dash">
                            <div class="col-md-12 hd_page_main">Grafik Jumlah Pembelian & Penjualan Tahun Ini </div>
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart3"></div>                                             
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 bg_con_dash">
                            <div class="col-md-12 hd_page_main">Grafik Summary Pembelian & Penjualan Tahun Ini </div>
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart4"></div>                                             
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="col-md-8 bg_con_dash">
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart3"></div>                                             
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 bg_con_dash">
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart4"></div>                                             
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 bg_con_dash">
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart5"></div>                                             
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 bg_con_dash">
                            <div class="col-md-12 _con_dash">
                                <div class="row bg_con_dash">
                                    <div class="col-md-12 _con_dash">
                                        <div class="col-md-12 bg_con_dash">
                                            <div class="col-md-12 con_dash">
                                                <div class="chart_con_dash" id="chart6"></div>                                             
                                            </div>
                                        </div>
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
						// var chart1;
						// chart1 = Highcharts.chart('chart1',{
						// 	chart: {pointPadding: 0,borderWidth: 0,groupPadding: 0,type: 'column'},
						// 	title: {text: ''},
						// 	subtitle: {text: ''},
						// 	xAxis: [{
                        //         categories: [
                        //             'Tgl : 1','Tgl : 2','Tgl : 3','Tgl : 4','Tgl : 5','Tgl : 6','Tgl : 7','Tgl : 8','Tgl : 9','Tgl : 10','Tgl : 11','Tgl : 12','Tgl : 13','Tgl : 14','Tgl : 15','Tgl : 16','Tgl : 17','Tgl : 18','Tgl : 19','Tgl : 20','Tgl : 21','Tgl : 22','Tgl : 23','Tgl : 24','Tgl : 25','Tgl : 26','Tgl : 27','Tgl : 28','Tgl : 29','Tgl : 30','Tgl : 31'
                        //         ],
                        //         crosshair: true
                        //     }],
						// 	yAxis: [{title: {text: ''}}],
						// 	tooltip: {shared: true},
						// 	legend: {enabled: false},
						// 	credits: {enabled: false},
						// 	series: [{
						// 		name: 'Jumlah Pembelian ',
						// 		type: 'column',
						// 		data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 31; $x++) {
                        //                     echo $listdata['po_'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
						// 	    colors: ['#932C25'],
						// 		colorByPoint: true,
						// 	},
                        //     {
						// 		name: 'Jumlah Penjualan ',
						// 		type: 'column',
						// 		data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 31; $x++) {
                        //                     echo $listdata['so_'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
						// 	    colors: ['#3789C1'],
						// 		colorByPoint: true,
						// 	}]
						// });

						// var chart2;
						// chart2 = Highcharts.chart('chart2',{
						// 	chart: {pointPadding: 0,borderWidth: 0,groupPadding: 0,type: 'column'},
						// 	title: {text: ''},
						// 	subtitle: {text: ''},
						// 	xAxis: [{
                        //         categories: [
                        //             'Tgl : 1','Tgl : 2','Tgl : 3','Tgl : 4','Tgl : 5','Tgl : 6','Tgl : 7','Tgl : 8','Tgl : 9','Tgl : 10','Tgl : 11','Tgl : 12','Tgl : 13','Tgl : 14','Tgl : 15','Tgl : 16','Tgl : 17','Tgl : 18','Tgl : 19','Tgl : 20','Tgl : 21','Tgl : 22','Tgl : 23','Tgl : 24','Tgl : 25','Tgl : 26','Tgl : 27','Tgl : 28','Tgl : 29','Tgl : 30','Tgl : 31'
                        //         ],
                        //         crosshair: true
                        //     }],
						// 	yAxis: [{title: {text: ''}}],
						// 	tooltip: {shared: true},
						// 	legend: {enabled: false},
						// 	credits: {enabled: false},
						// 	series: [{
						// 		name: 'Jumlah Pembelian ',
						// 		type: 'column',
						// 		data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 31; $x++) {
                        //                     echo $listdata['summary_po_'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
						// 	    colors: ['#932C25'],
						// 		colorByPoint: true,
						// 	},
                        //     {
						// 		name: 'Jumlah Penjualan ',
						// 		type: 'column',
						// 		data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 31; $x++) {
                        //                     echo $listdata['summary_so_'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
						// 	    colors: ['#3789C1'],
						// 		colorByPoint: true,
						// 	}]
						// });
Highcharts.setOptions({

    chart: {
        backgroundColor: 'transparent',
        style: {
            fontFamily: 'Poppins, Segoe UI, sans-serif'
        },
        animation: {
            duration: 800,
            easing: 'easeOutQuart'
        }
    },

    title: {
        style: {
            fontSize: '16px',
            fontWeight: '600'
        }
    },

    subtitle: {
        style: {
            color: '#6c757d'
        }
    },

    xAxis: {
        lineColor: '#e9ecef',
        tickColor: '#e9ecef',
        labels: {
            style: {
                color: '#6c757d',
                fontSize: '12px'
            }
        }
    },

    yAxis: {
        gridLineColor: '#f1f3f5',
        labels: {
            style: {
                color: '#6c757d'
            }
        }
    },

    legend: {
        itemStyle: {
            fontWeight: '500'
        }
    },

    tooltip: {
        backgroundColor: '#1e293b',
        borderWidth: 0,
        borderRadius: 8,
        style: {
            color: '#fff'
        }
    },

    credits: {
        enabled: false
    }

});


// chart3 = Highcharts.chart('chart3',{

//     chart:{
//         type:'column'
//     },

//     title:{
//         text:'Data Jumlah Pembelian & Penjualan'
//     },

//     xAxis:{
//         categories:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
//     },

//     yAxis:{
//         title:{text:null}
//     },

//     plotOptions:{
//         column:{
//             borderRadius:6,
//             pointPadding:0.2,
//             groupPadding:0.1,
//             borderWidth:0
//         }
//     },

//     series:[

//         {
//             name:'Pembelian',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['po_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:{
//                 linearGradient:[0,0,0,300],
//                 stops:[
//                     [0,'#ef4444'],
//                     [1,'#b91c1c']
//                 ]
//             }
//         },

//         {
//             name:'Penjualan',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['so_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:{
//                 linearGradient:[0,0,0,300],
//                 stops:[
//                     [0,'#3b82f6'],
//                     [1,'#1d4ed8']
//                 ]
//             }
//         }

//     ]

// });

chart3 = Highcharts.chart('chart3',{

    chart:{
        type:'column',
        animation:true,
        style:{
            fontFamily:'Poppins, sans-serif'
        }
    },

    title:{
        text:'Data Jumlah Pembelian & Penjualan',
        style:{
            fontSize:'16px',
            fontWeight:'600'
        }
    },

    xAxis:{
        categories:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        crosshair:true
    },

    yAxis:{
        title:{text:null},
        labels:{
            formatter:function(){
                return Highcharts.numberFormat(this.value,0,',','.');
            }
        }
    },

    // tooltip:{
    //     shared:true,
    //     // backgroundColor:'#ffffff',
    //     backgroundColor:'#060606',
    //     borderRadius:10,
    //     borderWidth:0,
    //     shadow:true,
    //     formatter:function(){

    //         let s = '<b>'+this.x+'</b>';

    //         this.points.forEach(function(p){
    //             s += '<br/>'+p.series.name+
    //                  ': <b>'+Highcharts.numberFormat(p.y,0,',','.')+'</b>';
    //         });

    //         return s;
    //     }
    // },

// tooltip:{
//     shared:true,
//     useHTML:true,
//     backgroundColor:'#ffffff',
//     borderRadius:10,
//     borderWidth:1,
//     borderColor:'#e5e7eb',
//     shadow:true,

//     formatter:function(){

//         let text = '<b>'+ this.x +'</b>';

//         if(this.points){

//             this.points.forEach(function(point){

//                 text += '<br/><span style="color:'+point.color+'">●</span> '
//                       + point.series.name
//                       + ' : <b>'
//                       + Highcharts.numberFormat(point.y,0,',','.')
//                       + '</b>';

//             });

//         }else{

//             text += '<br/><b>'+Highcharts.numberFormat(this.y,0,',','.')+'</b>';

//         }

//         return text;
//     }
// },

tooltip:{
    useHTML:true,
    backgroundColor:'#060606',
    borderRadius:10,
    borderWidth:1,
    borderColor:'#e5e7eb',
    shadow:true,

    headerFormat:'<b>{point.key}</b><br/>',

    pointFormatter:function(){

        return '<span style="color:'+this.color+'">●</span> '
            + this.series.name
            + ' : <b>'
            + Highcharts.numberFormat(this.y,0,',','.')
            + '</b><br/>';

    }
},

    credits:{enabled:false},

    plotOptions:{
        column:{
            borderRadius:8,
            pointPadding:0.15,
            groupPadding:0.1,
            borderWidth:0,

            dataLabels:{
                enabled:true,
                crop:false,
                overflow:'none',
                style:{
                    fontSize:'11px',
                    fontWeight:'500',
                    color:'#374151'
                },
                formatter:function(){
                    return Highcharts.numberFormat(this.y,0,',','.');
                }
            }
        }
    },
    

    series:[

        {
            name:'Pembelian',
            data:[
                <?php
                for ($x = 1; $x <= 12; $x++) {
                    echo $listdata['po_thn'.$x].',';
                }
                ?>
            ],
            color:{
                linearGradient:[0,0,0,300],
                stops:[
                    [0,'#ef4444'],
                    [1,'#b91c1c']
                ]
            }
        },

        {
            name:'Penjualan',
            data:[
                <?php
                for ($x = 1; $x <= 12; $x++) {
                    echo $listdata['so_thn'.$x].',';
                }
                ?>
            ],
            color:{
                linearGradient:[0,0,0,300],
                stops:[
                    [0,'#3b82f6'],
                    [1,'#1d4ed8']
                ]
            }
        }

    ]

});


chart4 = Highcharts.chart('chart4',{

    chart:{
        type:'pie'
    },

    title:{
        text:'Total Pembelian vs Penjualan'
    },

    plotOptions:{
        pie:{
            borderWidth:0,
            innerSize:'60%',
            dataLabels:{
                enabled:true,
                distance:20,
                style:{
                    fontWeight:'500'
                }
            }
        }
    },

    series:[
        {
            name:'Jumlah',
            data:[

                {
                    name:'Pembelian',
                    y:<?php echo $listdata['total_po_thn']; ?>,
                    color:'#ef4444'
                },

                {
                    name:'Penjualan',
                    y:<?php echo $listdata['total_so_thn']; ?>,
                    color:'#3b82f6'
                }

            ]
        }
    ]

});





// chart5 = Highcharts.chart('chart5',{

//     chart:{type:'column'},

//     title:{text:'Data Summary Pembelian & Penjualan'},

//     xAxis:{
//         categories:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
//     },

//     plotOptions:{
//         column:{
//             borderRadius:6,
//             borderWidth:0
//         }
//     },

//     series:[

//         {
//             name:'Summary Pembelian',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['summary_po_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:'#f97316'
//         },

//         {
//             name:'Summary Penjualan',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['summary_so_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:'#10b981'
//         }

//     ]

// });

// chart5 = Highcharts.chart('chart5',{

//     chart:{
//         type:'column'
//     },

//     title:{
//         text:'Data Summary Pembelian & Penjualan'
//     },

//     xAxis:{
//         categories:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
//     },

//     yAxis:{
//         title:{text:null},
//         labels:{
//             formatter:function(){

//                 if(this.value >= 1000000000){
//                     return 'Rp ' + (this.value/1000000000).toFixed(1) + 'B';
//                 }

//                 if(this.value >= 1000000){
//                     return 'Rp ' + (this.value/1000000).toFixed(1) + 'M';
//                 }

//                 if(this.value >= 1000){
//                     return 'Rp ' + (this.value/1000).toFixed(1) + 'K';
//                 }

//                 return 'Rp ' + this.value;

//             }
//         }
//     },

//     tooltip:{
//         shared:true,
//         formatter:function(){

//             let s = '<b>'+this.x+'</b>';

//             this.points.forEach(function(point){

//                 s += '<br/>'+point.series.name+
//                 ': <b>Rp '+Highcharts.numberFormat(point.y,0,',','.')+'</b>';

//             });

//             return s;

//         }
//     },

//     plotOptions:{
//         column:{
//             borderRadius:6,
//             borderWidth:0,
//             pointPadding:0.15,
//             groupPadding:0.08
//         }
//     },

//     series:[

//         {
//             name:'Summary Pembelian',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['summary_po_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:'#f97316'
//         },

//         {
//             name:'Summary Penjualan',
//             data:[
//                 <?php
//                 for ($x = 1; $x <= 12; $x++) {
//                     echo $listdata['summary_so_thn'.$x].',';
//                 }
//                 ?>
//             ],
//             color:'#10b981'
//         }

//     ]

// });

chart5 = Highcharts.chart('chart5',{

    chart:{
        type:'column',
        spacing:[10,10,15,10]
    },

    title:{
        text:'Summary Nilai Pembelian & Penjualan'
    },

    subtitle:{
        text:'Akumulasi Nilai Transaksi Per Bulan'
    },

    xAxis:{
        categories:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        crosshair:true
    },

    yAxis:{
        title:{ text:null },
        labels:{
            formatter:function(){
                return Highcharts.numberFormat(this.value,0,',','.');
            }
        }
    },

    legend:{
        align:'center',
        verticalAlign:'bottom'
    },

    tooltip:{
        shared:true,
        useHTML:true,
        formatter:function(){

            let s = '<b>'+this.x+'</b><br>';

            this.points.forEach(function(point){

                s += '<span style="color:'+point.color+'">●</span> ' +
                     point.series.name +
                     ' : <b>Rp '+Highcharts.numberFormat(point.y,0,',','.')+'</b><br>';

            });

            return s;
        }
    },

    plotOptions:{
        column:{
            borderRadius:8,
            borderWidth:0,
            pointPadding:0.25,
            groupPadding:0.12
        }
    },

    series:[

        {
            name:'Summary Pembelian',
            data:[
                <?php
                for ($x = 1; $x <= 12; $x++) {
                    echo $listdata['summary_po_thn'.$x].',';
                }
                ?>
            ],
            color:{
                linearGradient:[0,0,0,300],
                stops:[
                    [0,'#fb923c'],
                    [1,'#ea580c']
                ]
            }
        },

        {
            name:'Summary Penjualan',
            data:[
                <?php
                for ($x = 1; $x <= 12; $x++) {
                    echo $listdata['summary_so_thn'.$x].',';
                }
                ?>
            ],
            color:{
                linearGradient:[0,0,0,300],
                stops:[
                    [0,'#34d399'],
                    [1,'#059669']
                ]
            }
        }

    ],

    responsive:{
        rules:[{
            condition:{
                maxWidth:600
            },
            chartOptions:{
                legend:{
                    layout:'horizontal',
                    align:'center',
                    verticalAlign:'bottom'
                }
            }
        }]
    }

});



chart6 = Highcharts.chart('chart6',{

    chart:{
        type:'pie'
    },

    title:{
        text:'Total Summary'
    },

    plotOptions:{
        pie:{
            innerSize:'65%',
            borderWidth:0
        }
    },

    series:[
        {
            name:'Jumlah',
            data:[

                {
                    name:'Pembelian',
                    y:<?php echo $listdata['total_summary_po']; ?>,
                    color:'#f97316'
                },

                {
                    name:'Penjualan',
                    y:<?php echo $listdata['total_summary_so']; ?>,
                    color:'#10b981'
                }

            ]
        }
    ]

});

                        // var chart3;
                        // chart3 = Highcharts.chart('chart3',{
                        //     chart: {type: 'column'},
                        //     title: {text: 'Data Jumlah Pembelian & Penjualan'},
                        //     subtitle: {text: ''},
                        //     xAxis: [{
                        //         categories: [
                        //             // 'Bln : 1','Bln : 2','Bln : 3','Bln : 4','Bln : 5', 'Bln : 6', 'Bln : 7', 'Bln : 8', 'Bln : 9', 'Bln : 10', 'Bln : 11', 'Bln : 12'
                        //             // 'Bln : Jan','Bln : Feb','Bln : Mar','Bln : Apr','Bln : Mei', 'Bln : Jun', 'Bln : Jul', 'Bln : Agu', 'Bln : Sep', 'Bln : Okt', 'Bln : Nov', 'Bln : Des'
                        //             'Jan','Feb','Mar','Apr','Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                        //         ],
                        //         crosshair: true
                        //     }],
                        //     yAxis: [{title: {text: ''}}],
                        //     tooltip: {shared: true},
                        //     credits: {enabled: false},
                        //     series: [
                        //         {
                        //             name: 'Jumlah Pembelian ',
                        //             type: 'column',
                        //             data: [
                        //                 <?php
                        //                     for ($x = 1; $x <= 12; $x++) {
                        //                         echo $listdata['po_thn'.$x].',';
                        //                     }
                        //                 ?>
                        //             ],
                        //             color: '#932C25',
                        //         },
                        //         {
                        //             name: 'Jumlah Penjualan ',
                        //             type: 'column',
                        //             data: [
                        //                 <?php
                        //                     for ($x = 1; $x <= 12; $x++) {
                        //                         echo $listdata['so_thn'.$x].',';
                        //                     }
                        //                 ?>
                        //             ],
                        //             color: '#3789C1',
                        //         },
                        //     ]
                        // });

                        // var chart4;
                        // chart4 = Highcharts.chart('chart4',{
                        //     chart: {pointPadding: 0,borderWidth: 0,groupPadding: 0,type: 'pie'},
                        //     title: {text: 'Total Jumlah Pembelian & Penjualan'},
                        //     subtitle: {text: ''},
                        //     tooltip: {shared: true},
                        //     legend: {enabled: true},
                        //     credits: {enabled: false},
                        //     plotOptions: {
                        //         pie: {
                        //             allowPointSelect: true,
                        //             cursor: 'pointer',
                        //             dataLabels: {
                        //                 enabled: true
                        //             },
                        //             showInLegend: true
                        //         }
                        //     },
                        //     series: [
                        //         {
                        //             name: 'Jumlah ',
                        //             colorByPoint: true,
                        //             data: [
                        //                 {
                        //                     name: '<?php echo $listdata['total_po_thn']; ?> Pembelian',
                        //                     y : <?php echo $listdata['total_po_thn']; ?>,
                        //                     color: '#932C25',
                        //                 },
                        //                 {
                        //                     name: '<?php echo $listdata['total_so_thn']; ?> Penjualan',
                        //                     y : <?php echo $listdata['total_so_thn']; ?>,
                        //                     color: '#3789C1',
                        //                 }
                        //             ],
                        //         }
                        //     ]
                        // });

                        // var chart5;
                        // // chart5 = Highcharts.chart('chart5',{
                        //     chart: {type: 'column'},
                        //     title: {text: 'Data Summary Pembelian & Penjualan'},
                        //     subtitle: {text: ''},
                        //     xAxis: [{
                        //         categories: [
                        //             'Jan','Feb','Mar','Apr','Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                        //         ],
                        //         crosshair: true
                        //     }],
                        //     yAxis: [{title: {text: ''}}],
                        //     tooltip: {shared: true},
                        //     // legend: {enabled: false},
                        //     credits: {enabled: false},
                        //     series: [{
                        //         name: 'Summary Pembelian ',
                        //         type: 'column',
                        //         data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 12; $x++) {
                        //                     echo $listdata['summary_po_thn'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
                        //         color: '#932C25',
                        //         // colorByPoint: true,
                        //     },
                        //     {
                        //         name: 'Summary Penjualan ',
                        //         type: 'column',
                        //         data: [
                        //             <?php
                        //                 for ($x = 1; $x <= 12; $x++) {
                        //                     echo $listdata['summary_so_thn'.$x].',';
                        //                 }
                        //             ?>
                        //         ],
                        //         color: '#3789C1',
                        //         // colorByPoint: true,
                        //     }]
                        // });

                        // var chart6;
                        // chart6 = Highcharts.chart('chart6',{
                        //     chart: {pointPadding: 0,borderWidth: 0,groupPadding: 0,type: 'pie'},
                        //     title: {text: 'Total Summary Pembelian & Penjualan'},
                        //     subtitle: {text: ''},
                        //     tooltip: {shared: true},
                        //     legend: {enabled: true},
                        //     credits: {enabled: false},
                        //     plotOptions: {
                        //         pie: {
                        //             allowPointSelect: true,
                        //             cursor: 'pointer',
                        //             dataLabels: {
                        //                 enabled: true
                        //             },
                        //             showInLegend: true
                        //         }
                        //     },
                        //     series: [
                        //         {
                        //             name: 'Jumlah ',
                        //             colorByPoint: true,
                        //             data: [
                        //                 {
                        //                     name: 'Pembelian',
                        //                     y : <?php echo $listdata['total_summary_po']; ?>,
                        //                     color: '#932C25',
                        //                 },
                        //                 {
                        //                     name: 'Penjualan',
                        //                     y : <?php echo $listdata['total_summary_so']; ?>,
                        //                     color: '#3789C1',
                        //                 }
                        //             ],
                        //         }
                        //     ]
                        // });
                    });
                </script>
            @endsection


@endsection