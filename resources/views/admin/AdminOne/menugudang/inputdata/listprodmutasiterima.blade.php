<?php $no = 0; ?>
@forelse($results['results']['list_produk'] as $view_data)
    <?php  
        $id = str_replace('-', '', $view_data['id']);
        $qty_mutasi = $results['results']['qty_mutasi_mk'][$id];
        $qty_terima = 0;
        $kode_satuan[$id] = $results['results']['satuan_produk'][$id]['id'];
    ?>
    
    <?php $no++ ;?>
    <tr>
        <td style="text-align:center;" id="hg_td">{{ $no }}</td>
        <td style="text-align:left;">{{ $results['results']['detail_produk'][$id]['nama'] }}</td>
        <td style="text-align:center;">
            {{ number_format($qty_mutasi, 0, "", ".") }} {{ $results['results']['satuan_produk'][$id]['nama'] }}
        </td>

        <td style="text-align:center;">
            <input type="text" name="new_qty_{{$id}}" 
                   value="{{ number_format($qty_terima, 0, "", "") }}" 
                   style="width: 95px; text-align:center;" 
                   onKeyPress="return goodchars(event, '0123456789', this)" />
        </td>
    </tr>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.bg_act_page_main button').prop('disabled', false);

            $('input[name="new_qty_{{$id}}"]').change(function() {
                var code_data = $('input[name="code_data"]').val();
                var code_transaksi = $('input[name="in_code_transaksi"]').val();
                var tgl_transaksi = $('input[name="in_tgl_transaksi"]').val();
                var no_mutasi_kirim = $('input[name="no_mutasi_kirim"]').val();
                var keterangan = $('input[name="keterangan"]').val();
                var qty_up = $('input[name="new_qty_{{$id}}"]').val();

                if (qty_up > {{ $qty_mutasi }}) {
                    showAlert('Qty yang diterima melebihi qty kirim. Silakan update data mutasi terlebih dahulu.');
                    resetQty();
                } else if (!tgl_transaksi) {
                    showAlert('Bidang tanggal transaksi harus diisi.');
                    resetQty();
                } else if (!code_transaksi) {
                    showAlert('Bidang nomor mutasi harus diisi.');
                    resetQty();
                } else if (!no_mutasi_kirim) {
                    showAlert('Bidang nomor mutasi kirim harus diisi.');
                    resetQty();
                } else {
                    loadingpage(2000);
                    $.ajax({
                        type: "POST",
                        url: "/admin/savemutasiterima?_token={{csrf_token()}}&token={{$request['token']}}&u={{$request['u']}}",
                        data: {
                            id: "{{ $view_data['id'] }}",
                            code_data: "{{ $view_data['code_data'] }}",
                            code_transaksi: code_transaksi,
                            tgl_transaksi: tgl_transaksi,
                            no_mutasi_kirim: no_mutasi_kirim,
                            keterangan: encodeURIComponent(keterangan),
                            code_produk: "{{ $view_data['kode_barang'] }}",
                            qty_mutasi: "{{ $qty_mutasi }}",
                            kode_satuan: "{{ $kode_satuan[$id] }}",
                            qty: qty_up
                        },
                        cache: false,
                        success: function(response) {
                            loadingpage(0);
                            if (response.status_message === 'failed') {
                                showAlert(response.note.code_transaksi ? 'No. Mutasi Terima sudah terdaftar.' : 'Data gagal disimpan.');
                                resetQty();
                            } else if (response.status_message === 'failed_proses') {
                                showAlert('Data telah diproses.');
                                window.location.reload();
                            } else {
                                lockFormFields();
                                window.location.href = "/admin/viewmutasiterima?d=" + code_transaksi;
                            }
                        }
                    });
                }
            });

            function resetQty() {
                $('input[name="new_qty_{{$id}}"]').val('{{ number_format($qty_terima, 0, "", "") }}');
            }

            function showAlert(message) {
                $('div[data-model="confirmasi_data"]').modal({ backdrop: false });
                $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-danger">' + message + '</div>');
                $('button[btn-action="aciton-confirmasi"]').remove();
            }

            function lockFormFields() {
                $('div[data-model="listproduk"]').modal('hide');
                $('input[name="code_transaksi"], input[name="tgl_transaksi"], select[name="gudang_asal"], select[name="gudang_tujuan"], input[name="no_mutasi_kirim"]').prop('disabled', true).removeClass('pointer');
                $('.bg_act_page_main button, [line="list_produk_transakasi"] button, [line="list_produk_transakasi"] input').prop('disabled', true);
            }
        });
    </script>
@empty
    <tr>
        <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 250px; font-size: 14px;" colspan="20">
            <i class="fa fa-shopping-bag"></i>
        </td>
    </tr>
    <script>
        $(document).ready(function() {
            $('.bg_act_page_main button').prop('disabled', true);
            $('[name="btn_cancel"], [onclick="BackPage()"]').prop('disabled', false);
        });
    </script>
@endforelse

@if($no > 0)
    <tr>
        <td class="blank_list" style="text-align:center;" colspan="5"></td>
    </tr>
@endif

<script type="text/javascript">
    $(document).ready(function() {
        var hg_td = $('#hg_td').height();
        $('.blank_list').css({ "height": hg_td + "px", "padding": "18px" });
    });
</script>