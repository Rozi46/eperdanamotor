<?php $no = 0;?> @forelse($results['results']['list_produk'] as $view_data) <?php $no++ ;?>
    <?php
        $id = $view_data['id'];
        $id = str_replace('-','',$id);
        $mata_uang = 'Rp';
        $diskon = number_format($view_data['diskon_harga'],2,",",".");
    ?>

    @php
    $detail = $results['results']['detail'];
    $isOwner = ($detail['kode_user'] == $res_user['id']);
    $isAdmin = ($res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa');
    $isEditable = (
        $results['results']['counttransaksi'] == 0 &&
        $detail['status_transaksi'] == 'Proses' &&
        ($isOwner || $isAdmin)
    );

    $disabledGlobal =
        $view_data['jumlah_terima'] > 0 ||
        ($detail['kode_user'] && $detail['kode_user'] != $res_user['id']) ||
        in_array($detail['status_transaksi'], ['Finish','Dibatalkan']);
    @endphp


    <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
        <td style="text-align:center;" id="hg_td">{{$no}}</td>
        <td style="text-align:left;">{{ $results['results']['detail_produk'][$view_data['kode_barang']]['nama'] ?? 'Belum ditentukan' }}</td>

        {{-- SATUAN --}}
        @if($isEditable)
            <td style="text-align:center;">
                <select name="new_satuan_{{$view_data['id']}}" style="width:60%; padding-top:3px;"
                    @if($disabledGlobal || $detail['status_transaksi']=='Finish') disabled @endif>

                    <option value="{{$view_data['kode_satuan']}}">
                        {{$results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama']}}
                    </option>

                    @if($view_data['kode_satuan'] != $results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan'])
                        <option value="{{$results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']}}">
                            {{$results['results']['satuan_produk'][$view_data['kode_barang']]['nama']}}
                        </option>
                    @endif

                    @foreach ($results['results']['satuan_barang_pecahan'][$view_data['kode_barang']] as $satuan)
                        @if($view_data['kode_satuan'] != $satuan['id'])
                            <option value="{{$satuan['id']}}">{{$satuan['nama']}}</option>
                        @endif
                    @endforeach
                </select>
            </td>
        @else
            <td style="text-align:center;">{{$results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama']}}</td>
        @endif


        {{-- HARGA --}}
        @if($isEditable)
            <td style="text-align:center;">
                <input type="text"
                    name="new_price_{{$view_data['id']}}"
                    value="{{ number_format($view_data['harga'],2,',','') }}"
                    style="width:70%; text-align:right;"
                    onkeypress="return goodchars(event,'0123456789,',this)"
                    @if($disabledGlobal) disabled @endif>
            </td>
        @else
            <td style="text-align:center;">{{ number_format($view_data['harga'],2,',','.') }}</td>
        @endif


        {{-- QTY --}}
        @if($isEditable)
            <td style="text-align:center;">
                <input type="text"
                    name="new_qty_{{$view_data['id']}}"
                    value="{{ number_format($view_data['jumlah_beli'],0,'.','') }}"
                    style="width:50px; text-align:center;"
                    onkeypress="return goodchars(event,'0123456789',this)"
                    @if($disabledGlobal) disabled @endif>
            </td>
        @else
            <td style="text-align:center;">{{ number_format($view_data['jumlah_beli'],0,'.','.') }}</td>
        @endif


        {{-- DISKON --}}
        @if($isEditable)
            <td style="text-align:right;">
                <div style="display:flex; gap:4px; margin-bottom:2px;">
                    <span style="width:14px;">1 </span>
                    <input type="text"
                        name="new_disc_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_persen'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        @if($disabledGlobal) disabled @endif>

                    <input type="text"
                        name="new_disc_harga_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_harga'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        disabled>
                </div>

                <div style="display:flex; gap:4px;">
                    <span style="width:14px;">2 </span>
                    <input type="text"
                        name="new_disc2_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_persen2'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        @if($disabledGlobal || (float)$detail['diskon_persen']==0) disabled @endif>

                    <input type="text"
                        name="new_disc2_harga_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_harga2'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        disabled>
                </div>

                <div style="display:flex; gap:4px;">
                    <span style="width:14px;">3 </span>
                    <input type="text"
                        name="new_disc3_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_persen3'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        @if($disabledGlobal || (float)$detail['diskon_persen']==0) disabled @endif>

                    <input type="text"
                        name="new_disc3_harga_{{$view_data['id']}}"
                        value="{{ number_format($view_data['diskon_harga3'],2,',','') }}"
                        style="flex:1; text-align:right;"
                        onkeypress="return goodchars(event,'0123456789,',this)"
                        disabled>
                </div>
            </td>
        @else
            <!-- <td style="text-align:center;">
                @if($view_data['diskon_persen'] > 0)
                    {{ number_format($view_data['diskon_persen'],2,',','.') }}
                    - {{ number_format($view_data['diskon_harga'],2,',','.') }}<br>
                @endif

                @if($view_data['diskon_persen2'] > 0)
                    {{ number_format($view_data['diskon_persen2'],2,',','.') }}
                    - {{ number_format($view_data['diskon_harga2'],2,',','.') }}<br>
                @endif

                @if($view_data['diskon_persen3'] > 0)
                    {{ number_format($view_data['diskon_persen3'],2,',','.') }}
                    - {{ number_format($view_data['diskon_harga3'],2,',','.') }}
                @endif
            </td> -->
            <td style="text-align:center;">
                @php
                    $diskonList = [
                        [
                            'persen' => $view_data['diskon_persen'] ?? 0,
                            'harga'  => $view_data['diskon_harga'] ?? 0,
                        ],
                        [
                            'persen' => $view_data['diskon_persen2'] ?? 0,
                            'harga'  => $view_data['diskon_harga2'] ?? 0,
                        ],
                        [
                            'persen' => $view_data['diskon_persen3'] ?? 0,
                            'harga'  => $view_data['diskon_harga3'] ?? 0,
                        ],
                    ];

                    $filtered = array_filter($diskonList, fn($d) => $d['persen'] > 0);

                    if (empty($filtered)) {
                        $filtered = [
                            ['persen'=>0,'harga'=>0]
                        ];
                    }
                @endphp

                @foreach($filtered as $d)
                    {{ number_format($d['persen'],2,',','.') }} - {{ number_format($d['harga'],2,',','.') }}
                    @if(!$loop->last)<br>@endif
                @endforeach
            </td>
        @endif


        {{-- NETTO --}}
        <td style="text-align:right;">{{ number_format($view_data['harga_netto'],2,',','.') }}</td>

        {{-- TOTAL --}}
        <td style="text-align:right;">{{ number_format($view_data['total_harga'],2,',','.') }}</td>

        {{-- DELETE --}}
        @if($isEditable)
            <td style="text-align:center;">
                <button type="button"
                    class="btn btn-danger btn_del"
                    btn="del_produk_{{$view_data['id']}}"
                    title="Hapus Data"
                    @if($detail['status_transaksi']=='Finish') disabled @endif>
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        @endif
    </tr>

    <script>
        $(document).ready(function(){      
            $('.bg_act_page_main button').prop({disabled:false});
            $('input[name="data_produk"]').prop({disabled:false}).focus().val('');

            $('[btn="del_produk_{{$view_data['id']}}"]').click(function(){
                if($('[btn="del_produk_{{$view_data['id']}}"]').click){
                    $('.bg_act_page_main button').prop({disabled:true});
                    $('input[name="data_produk"]').prop({disabled:true});
                    $('[line="data_produk_{{$view_data['code_data']}}"]').remove();
                    $.ajax({
                        type: "GET",
                        url: "/admin/deleteprodpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                        data:"id={{$view_data['id']}}&code_data={{$view_data['code_data']}}",
                        cache: false,
                        success: function(data){
                            if(data.status_message == 'success'){
                                $('.bg_act_page_main button').prop({disabled:false});
                                $('input[name="data_produk"]').prop({disabled:false}).focus().val('');
                                var menu = $('.list_data_prod_transaksi').length;
                                if ($('.list_data_prod_transaksi').length == 0) {
                                    $('.bg_act_page_main button').prop({disabled:true});
                                    $('[name="btn_cancel"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('[btn="history_data"]').prop({disabled:false});
                                    $('[onclick="BackPage()"]').prop({disabled:false});
                                }
                                $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('[btn="cari_produk"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                    // $(".ios").iosCheckbox();
                                });
                                $.get("/admin/summarypembelian",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
                                    $('[line="summary_transaksi"]').html(listsummary);
                                });
                            }else{
                                $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                                $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal dihapus</div>');
                                $('button[btn-action="aciton-confirmasi"]').remove();
                                $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                                    $('[line="list_produk_transakasi"]').html(listproduk);
                                    $('[btn="cari_produk"]').prop({disabled:false});
                                    $('[btn="data_permintaan"]').prop({disabled:false});
                                    $('input[name="data_produk"]').prop({disabled:false}).focus();
                                    // $(".ios").iosCheckbox();
                                });
                            }
                        }
                    });
                }
            });	
            
            $('select[name="new_satuan_{{$view_data['id']}}"]').change(function(){
                listsatuanharga_<?php echo $id;?>();  
            });

            $('input[name="new_price_{{$view_data['id']}}"]').change(function(){
                var price_up = $('input[name="new_price_{{$view_data['id']}}"]');
                var price_up_val = price_up.val();
                var price_up_val = price_up_val.replace(",", ".");
                if(price_up_val == ''){
                    price_up.val('<?php echo number_format($view_data['harga'],2,",","") ?>').focus();
                }else{
                    saveprice_<?php echo $id;?>();
                }
            });

            $('input[name="new_qty_{{$view_data['id']}}"]').change(function(){
                var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]');
                if(qty_up.val() != ''){
                    if(qty_up.val() <= 0){
                        qty_up.val('<?php echo number_format($view_data['jumlah_beli'],0,"",".") ?>').focus();
                    }else{
                        saveqty_<?php echo $id;?>();
                    }
                }else{
                    qty_up.val('<?php echo number_format($view_data['jumlah_beli'],0,"",".") ?>').focus();
                }
            });
           
            $('input[name="new_disc_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['id']}}"]').val();
                var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]');
                var disc_up_val = disc_up.val();
                if(disc_up_val != ''){
                    savedisc_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    disc_up.val('<?php echo number_format($view_data['diskon_persen'],2,",","") ?>').focus();
                } 
            });
           
           $('input[name="new_disc2_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]');
                var disc_up2_val = disc_up2.val();
                if(disc_up2_val != ''){
                    savedisc2_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    disc_up2.val('<?php echo number_format($view_data['diskon_persen2'],2,",","") ?>').focus();
                } 
           });
           
           $('input[name="new_disc3_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var disc_up3 = $('input[name="new_disc3_{{$view_data['id']}}"]');
                var disc_up3_val = disc_up3.val();
                if(disc_up3_val != ''){
                    savedisc3_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    disc_up3.val('<?php echo number_format($view_data['diskon_persen3'],2,",","") ?>').focus();
                } 
           });
           
           $('input[name="new_disc_harga_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var discharga_up = $('input[name="new_disc_harga_{{$view_data['id']}}"]');
                var discharga_up_val = discharga_up.val();
                if(discharga_up_val != ''){
                    savedischarga_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    discharga_up.val('<?php echo number_format($view_data['diskon_harga'],2,",","") ?>').focus();
                } 
           });
           
           $('input[name="new_disc2_harga_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var discharga2_up = $('input[name="new_disc2_harga_{{$view_data['id']}}"]');
                var discharga2_up_val = discharga2_up.val();
                if(discharga2_up_val != ''){
                    savedischarga2_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    discharga2_up.val('<?php echo number_format($view_data['diskon_harga2'],2,",","") ?>').focus();
                } 
           });
           
           $('input[name="new_disc3_harga_{{$view_data['id']}}"]').change(function(){
                // var type_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
                var discharga3_up = $('input[name="new_disc3_harga_{{$view_data['id']}}"]');
                var discharga3_up_val = discharga3_up.val();
                if(discharga3_up_val != ''){
                    savedischarga3_<?php echo $id;?>();
                }else{
                    // $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                    // $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Nilai diskon harus diisi jika tidak ada diskon silakan mengisi dengan angka 0.</div>');
                    // $('button[btn-action="aciton-confirmasi"]').remove();
                    discharga3_up.val('<?php echo number_format($view_data['diskon_harga2'],2,",","") ?>').focus();
                } 
           });
        });
        
        function listsatuanharga_<?php echo $id;?>(){       
            var satuan_harga = $('select[name="new_satuan_{{$view_data['id']}}"]').val();      
            $.ajax({
                type: "GET",
                url: "/admin/listsatuanharga?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&harga_satuan="+satuan_harga,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('select[name="new_satuan_{{$view_data['id']}}"] option[value="123"]').prop("selected", true); 
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }
                }
            }); 
        }
        
        function saveprice_<?php echo $id;?>(){
            var price_up = $('input[name="new_price_{{$view_data['id']}}"]').val();
            var price_up = price_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/uphargapembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&harga="+price_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }
                }
            }); 
        }
        
        function saveqty_<?php echo $id;?>(){
            var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]').val();
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/upqtypembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&qty="+qty_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            // $(".ios").iosCheckbox();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                            $(".ios").iosCheckbox();
                        });
                    }
                }
            }); 
        }
        
        function savedisc_<?php echo $id;?>(){
            var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]').val();
            var disc_up = disc_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelian?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&nilai_diskon="+disc_up,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function savedisc2_<?php echo $id;?>(){
            var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]').val();
            var disc_up2 = disc_up2.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelian2?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:"id=<?php echo $view_data['id'];?>&code_data=<?php echo $view_data['code_data'];?>&nilai_diskon2="+disc_up2,
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            });  
        }
        
        function savedisc3_<?php echo $id;?>(){
            var disc_up3 = $('input[name="new_disc3_{{$view_data['id']}}"]').val();
            var disc_up3 = disc_up3.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelian3?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                data:{
                    id: "{{$view_data['id']}}",
                    code_data: "{{$view_data['code_data']}}",
                    nilai_diskon3: disc_up3
                },
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            });  
        }
        
        function savedischarga_<?php echo $id;?>(){
            var discharga_up = $('input[name="new_disc_harga_{{$view_data['id']}}"]').val();
            var discharga_up = discharga_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelianharga?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",                
                data:{
                    id: "{{$view_data['id']}}",
                    code_data: "{{$view_data['code_data']}}",
                    nilai_diskonharga: discharga_up
                },
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function savedischarga2_<?php echo $id;?>(){
            var discharga2_up = $('input[name="new_disc2_harga_{{$view_data['id']}}"]').val();
            var discharga2_up = discharga2_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelianharga2?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",                
                data:{
                    id: "{{$view_data['id']}}",
                    code_data: "{{$view_data['code_data']}}",
                    nilai_diskonharga2: discharga2_up
                },
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function savedischarga3_<?php echo $id;?>(){
            var discharga3_up = $('input[name="new_disc3_harga_{{$view_data['id']}}"]').val();
            var discharga3_up = discharga3_up.replace(".", "");
            
            $('.bg_act_page_main button').prop({disabled:true});
            $('input[name="data_produk"]').prop({disabled:true});
            
            $.ajax({
                type: "POST",
                url: "/admin/updiscpembelianharga3?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",                
                data:{
                    id: "{{$view_data['id']}}",
                    code_data: "{{$view_data['code_data']}}",
                    nilai_diskonharga3: discharga3_up
                },
                cache: false,
                success: function(data){
                    if(data.status_message == 'success'){
                        $('.bg_act_page_main button').prop({disabled:false});
                        $('input[name="data_produk"]').prop({disabled:false});
                        hitung_total_<?php echo $id;?>();
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }else{
                        $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">Data gagal disimpan</div>');
                        $.get("/admin/listprodpembelian",{code_data:"{{$view_data['nomor']}}"},function(listproduk){
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('[btn="cari_produk"]').prop({disabled:false});
                            $('[btn="data_permintaan"]').prop({disabled:false});
                            $('input[name="data_produk"]').prop({disabled:false}).focus();
                        });
                    }
                }
            }); 
        }
        
        function hitung_total_<?php echo $id;?>(){
            var price_up = $('input[name="new_price_{{$view_data['id']}}"]').val();
            var price_up = price_up.replace(",", ".");

            var qty_up = $('input[name="new_qty_{{$view_data['id']}}"]').val(); 
            var qty_up = parseInt(qty_up);

            // var total_up = (price_up*qty_up);

            // var tipe_disc_up = $('select[name="type_disc_{{$view_data['code_data']}}"]').val();
            // var disc_up = $('input[name="new_disc_{{$view_data['code_data']}}"]').val();
            // if(tipe_disc_up == 'Persen'){
                // var disc_up = disc_up.replace(",", ".");
                // var nilai_diskon = disc_up/100;
                // var total_up_disc = (total_up*nilai_diskon);
                // var total_up = (total_up-total_up_disc);
            // }else if(tipe_disc_up == 'Jumlah'){
            //     var disc_up = disc_up.replace(",", ".");
            //     var total_up = total_up - disc_up;
            // }else{
            //     var disc_up = disc_up.replace(",", ".");
            //     var total_up = total_up;
            // }
            
            var disc_up = $('input[name="new_disc_{{$view_data['id']}}"]').val();
            var disc_up = disc_up.replace(",", ".");
            var nilai_diskon = disc_up/100;
            var nilai_diskon = (price_up*nilai_diskon);

            var disc_up2 = $('input[name="new_disc2_{{$view_data['id']}}"]').val();
            var disc_up2 = disc_up2.replace(",", ".");
            var nilai_diskon2 = disc_up2/100;
            var nilai_diskon2 = ((price_up-nilai_diskon)*nilai_diskon2);

            var netto_up = (price_up-nilai_diskon-nilai_diskon2);

            // var total_up_disc2 = (total_up*nilai_diskon);
            // var total_up = (total_up-total_up_disc);

            var total_up = (qty_up*netto_up);
            
            var netto_up = netto_up.toFixed(2);
            var netto_up = netto_up.replace(".", ",");
            $('[line="netto_{{$view_data['id']}}"]').html(format_angka(netto_up));

            var total_up = total_up.toFixed(2);
            var total_up = total_up.replace(".", ",");
            $('[line="total_harga_{{$view_data['id']}}"]').html(format_angka(total_up));

            $.get("/admin/summarypembelian",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
                $('[line="summary_transaksi"]').html(listsummary);
            });
        }

    </script>
@empty
    <tr>
        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20" >
            <i class="fa fa-shopping-bag"></i>
        </td>
    </tr>
    <script>
        $(document).ready(function(){
            $('.bg_act_page_main button').prop({disabled:true});
            $('[name="btn_cancel"]').prop({disabled:false});
            $('[btn="history_data"]').prop({disabled:false});
            $('[onclick="BackPage()"]').prop({disabled:false});
        });
    </script>
@endforelse



<script type="text/javascript">
    $(document).ready(function(){
        
            $('[line="list_produk_transakasi"] input').prop({disabled:true});
            $('[line="list_produk_transakasi"] select').prop({disabled:true});
            $('[line="list_produk_transakasi"] button').prop({disabled:true});
            $('input[type="checkbox"]').prop({disabled:true});
        
        var hg_td = $('#hg_td').height();
        $('.blank_list').css({"height":""+hg_td+"","padding":"18px"});

        $.get("/admin/summarypembelian",{code_data:'{{$results['results']['detail']['nomor']}}'},function(listsummary){
            $('[line="summary_transaksi"]').html(listsummary);
            <?php if($request['focus_line'] == 'summary'){?>
                $("html, body").animate({ scrollTop: $('.page_main').height()}, 600);
            <?php } ?>            
            <?php if($results['results']['detail']['kode_user'] != $res_user['id'] ){?>
                $('[line="list_produk_transakasi"] input').prop({disabled:true});
                $('[line="list_produk_transakasi"] select').prop({disabled:true});
                $('[line="list_produk_transakasi"] button').prop({disabled:true});
                $('[line="summary_transaksi"] input').prop({disabled:true});
                $('[line="summary_transaksi"] select').prop({disabled:true});
                $('[line="summary_transaksi"] button').prop({disabled:true});
                $('input[type="checkbox"]').prop({disabled:true});
            <?php } ?>   
        });
    });
</script>