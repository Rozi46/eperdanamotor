<?php $no = 0; ?> 
@forelse($results['results']['list_produk'] as $view_data) 
    <?php 
        $no++; 
        $id = str_replace('-', '', $view_data['id']);
    ?>
    <tr class="list_data_prod_transaksi" line="data_produk_{{$view_data['id']}}">
        <td style="text-align:center;" id="hg_td">{{ $no }}</td>
        <td style="text-align:left;">{{ $results['results']['detail_produk'][$view_data['kode_barang']]['nama'] }}</td>
        <td style="text-align:center;">
            @if($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'))
                <select name="new_satuan_{{$view_data['id']}}" style="width:60%; padding-top: 3px;" {{ $results['results']['detail']['kode_user'] != $res_user['id'] ? 'disabled' : '' }}>
                    <option value="{{$view_data['kode_satuan']}}">{{$results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama']}}</option>

                    @if($view_data['kode_satuan'] != $results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan'])
                        <option value="{{$results['results']['detail_produk'][$view_data['kode_barang']]['kode_satuan']}}">{{$results['results']['satuan_produk'][$view_data['kode_barang']]['nama']}}</option>
                    @endif
                    
                    @if(!empty($results['results']['detail_produk'][$view_data['kode_barang']]['nama']))
                        @foreach ($results['results']['satuan_barang_pecahan'][$view_data['kode_barang']] as $view_data_satuan)
                            @if($view_data['kode_satuan'] != $view_data_satuan['id'])
                                <option value="{{ $view_data_satuan['id'] }}">{{ $view_data_satuan['nama'] }}</option>
                            @endif
                        @endforeach
                    @endif
                </select>
            @else
                {{ $results['results']['satuan_barang_produk'][$view_data['kode_barang']]['nama'] }}
            @endif
        </td>
        <td style="text-align:center;">
            @if($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'))
                <input type="text" name="new_qty_{{$view_data['id']}}" value="{{ number_format($view_data['qty'], 0, '', '.') }}" style="width: 50px; text-align:center;" onKeyPress="return goodchars(event, '0123456789', this)" />
            @else
                {{ number_format($view_data['qty'], 0, '', '.') }}
            @endif
        </td>
        @if($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'))
            <td style="text-align:center;">
                <button type="button" class="btn btn-danger btn_del" data-id="{{$view_data['id']}}" title="Hapus Data"><i class="fa fa-trash-o"></i></button>
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20">
            <i class="fa fa-shopping-bag"></i> Tidak ada produk
        </td>
    </tr>
@endforelse

@if($no > 0)
    <tr>
        <td class="blank_list" style="text-align:center;" colspan="5"></td>
    </tr>
@endif

<script type="text/javascript">
    $(document).ready(function() {
        // Tinggi kolom penyesuaian
        var hg_td = $('#hg_td').height();
        $('.blank_list').css({ "height": hg_td + "px", "padding": "18px" });

        // Event listener untuk tombol hapus produk
        $('.btn_del').click(function() {
            var id = $(this).data('id');
            $('input[name="data_produk"]').prop("disabled", true);
            $('[line="data_produk_' + id + '"]').remove();
            $.ajax({
                type: "GET",
                url: `deleteprodmutasikirim?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}`,
                data: { id: id, code_data: '{{$view_data['code_data']}}' },
                cache: false,
                success: function(response) {
                    if(response.status_message === 'success') {
                        refreshProductList();
                    } else {
                        showErrorModal('Data gagal dihapus');
                        refreshProductList();
                    }
                }
            });
        });

        // Event listener untuk perubahan qty
        $('input[name^="new_qty_"]').change(function() {
            var id = $(this).attr('name').split('_')[2];
            var qty = $(this).val();
            if (qty <= 0) {
                $(this).val('{{ number_format($view_data['qty'], 0, "", ".") }}').focus();
            } else {
                updateQuantity(id, qty);
            }
        });

        // Event listener untuk perubahan satuan
        $('select[name^="new_satuan_"]').change(function() {
            var id = $(this).attr('name').split('_')[2];
            updateSatuan(id);
        });
    });

    function updateQuantity(id, qty) {
        var data = {
            id: id,
            code_data: '{{$view_data['code_data']}}',
            qty: qty
        };
        $.ajax({
            type: "POST",
            url: `upqtymutasikirim?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}`,
            data: data,
            cache: false,
            success: function(response) {
                if(response.status_message === 'success') {
                    refreshProductList();
                } else {
                    showErrorModal('Data gagal disimpan');
                    refreshProductList();
                }
            }
        });
    }

    function updateSatuan(id) {
        var data = {
            id: id,
            code_data: '{{$view_data['code_data']}}',
            satuan: $('select[name="new_satuan_' + id + '"]').val()
        };
        $.ajax({
            type: "GET",
            url: `listsatuanmutasikirim?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}`,
            data: data,
            cache: false,
            success: function(response) {
                if(response.status_message === 'success') {
                    refreshProductList();
                } else {
                    showErrorModal('Data gagal disimpan');
                    refreshProductList();
                }
            }
        });
    }

    function refreshProductList() {
        $.get("/admin/listprodmutasikirim", { code_data: '{{$view_data['nomor']}}' }, function(listproduk) {
            $('[line="list_produk_transakasi"]').html(listproduk);
            $('input[name="data_produk"]').prop("disabled", false).focus();
        });
    }

    function showErrorModal(message) {
        $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
        $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">' + message + '</div>');
    }
</script>
