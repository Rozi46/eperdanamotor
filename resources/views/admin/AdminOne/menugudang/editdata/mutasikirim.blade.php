@extends('admin.AdminOne.layout.assets')
@section('title', 'Mutasi Kirim')

@section('content')

			<div class="page_main">
				<div class="container-fluid text-left">
					<div class="row">
                        <div class="col-md-12 bg_page_main hd" line="hd_action">
                            <div class="col-md-12 hd_page_main">Mutasi Kirim</div>
                            <div class="col-md-12 bg_act_page_main">
                                <div class="row">
                                    <div class="col-xl-12 col_act_page_main text-left">
                                        <button type="button" class="btn btn-default back" onclick="BackPage()">
                                            <i class="fa fa-arrow-left"></i> Kembali
                                        </button>
                                        @if($level_user['historymutasikirim'] == 'Yes')
                                            <a href="/admin/historymutasikirim">
                                                <button type="button" class="btn btn-success" btn="history_data">History Mutasi Kirim</button>
                                            </a>
                                        @endif

                                        @if($level_user['inputmutasikirim'] == 'Yes')
                                            @if($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa'))
                                                <button type="button" class="btn btn-primary" name="btn_save_data" btn="save_data">Simpan Data & Selesai</button>
                                                <button type="button" class="btn btn-danger" name="btn_cancel" btn="cancel_data">Batalkan Mutasi</button>
                                            @endif
                                        @endif

                                        @if($level_user['historymutasikirim'] == 'Yes')
                                            @if($results['results']['detail']['status_transaksi'] != 'Input')
                                                <a href="printmutasikirim?d={{$results['results']['detail']['nomor']}}" target="_blank">
                                                    <button type="button" class="btn btn-secondary" name="btn_print" btn="print_data">
                                                        <i class="fa fa-print"></i> Print Mutasi Kirim
                                                    </button>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 bg_page_main form_action" line="form_action">
                            <div class="col-md-12 data_page">
                                <form method="post" name="form_data" enctype="multipart/form-data" action="/admin/savemutasikirim">
                                    {{ csrf_field() }}
                                    <div class="row bg_data_page form_page content">
                                        <input type="hidden" name="code_data" value="{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}" />
                                        <input type="hidden" name="in_tgl_transaksi" value="" />
                                        <input type="hidden" name="in_code_transaksi" value="" />
                                        <input type="hidden" name="in_gudang_asal" value="" />
                                        <input type="hidden" name="in_gudang_tujuan" value="" />

                                        <div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="full_name" class="col-sm-2 col-form-label">Mutasi Kirim Oleh</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="full_name" placeholder="Full Name" value="{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum ditentukan'}}" readonly>
                                                </div>
                                            </div>
                                        </div>	

                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="tgl_transaksi" class="col-sm-4 col-form-label">Tanggal Mutasi <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <div class="input-group-append" btn="tgl_view" line="tgl_transaksi">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                    <input class="pointer" type="text" name="tgl_transaksi" placeholder="Tanggal Mutasi" value="{{ !empty($view_data['tanggal']) ? Date::parse($view_data['tanggal'])->format('d F Y') : 'Belum ditentukan' }}" readonly>
                                                    
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="code_transaksi" class="col-sm-4 col-form-label">No. Mutasi <span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="code_transaksi" placeholder="No. Mutasi" value="{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan'}}" disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_asal" class="col-sm-4 col-form-label">Gudang Asal<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="gudang_asal" placeholder="Gudang Asal" value="{{$results['results']['detail_gudang_asal']['nama']}}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="gudang_tujuan" class="col-sm-4 col-form-label">Gudang Tujuan<span>*</span></label>
                                                <div class="col-sm-8 input">
                                                    <input type="text" name="gudang_tujuan" placeholder="Gudang Tujuan" value="{{ $results['results']['detail_gudang_tujuan']['nama'] ?? 'Belum ditentukan' }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 bg_form_page">
                                            <div class="form-group row form_input text-left">
                                                <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                                                <div class="col-sm-10 input">
                                                    <input type="text" name="keterangan" placeholder="Keterangan" value="{{ old('keterangan') }}" {{ !($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa')) ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                        </div>

                                    </div> 
                                </form>
                            </div>
                        </div>

                        <div class="col-md-12 bg_page_main">
                            <?php if ($results['results']['detail']['status_transaksi'] == 'Input') { ?>
                                <div class="col-md-12 data_page" line="input_cari_data">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0;">
                                            <div class="row bg_data_page form_page content bg_form_group">
                                                <div class="col-md-12 col_act_page_main text-right">
                                                    <input type="text" class="form_group search" name="data_produk" id="data_produk" placeholder="Scan atau cari data barang" value="" style="padding:10px 5px;" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-12 data_page view">
                                <div class="row bg_data_page" style="padding: 5px;">
                                    <div class="table_data transaksi">
                                        <table class="table_view table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px; text-align: center;">No</th>
                                                    <th style="min-width:250px; text-align: center;">Nama Barang</th>
                                                    <th style="min-width:75px; text-align: center;">Satuan Barang</th>
                                                    <th style="width:100px; text-align: center;">Qty</th>
                                                    <?php if ($results['results']['detail']['status_transaksi'] == 'Input' && ($results['results']['detail']['kode_user'] == $res_user['id'] || $res_user['id'] == 'bd050931-d837-11eb-8038-204747ab6caa')) { ?>
                                                        <th style="width:25px; text-align: center;"></th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody line="list_produk_transakasi">
                                                <tr>
                                                    <td colspan="<?php echo ($results['results']['detail']['status_transaksi'] == 'Input') ? 5 : 4; ?>" style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;">
                                                        <i class="fa fa-shopping-bag"></i>
                                                    </td>
                                                </tr>
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
                        const $gudangAsal = $('select[name="gudang_asal"]');
                        const $gudangTujuan = $('select[name="gudang_tujuan"]');
                        const $dataProduk = $('input[name="data_produk"]');
                        const $tglTransaksi = $('input[name="tgl_transaksi"]');
                        const $inTglTransaksi = $('input[name="in_tgl_transaksi"]');
                        const $keterangan = $('input[name="keterangan"]');
                        const $fullName = $('input[name="full_name"]');
                        const $inGudangAsal = $('input[name="in_gudang_asal"]');
                        const $inGudangTujuan = $('input[name="in_gudang_tujuan"]');
                        const $codeTransaksi = $('input[name="code_transaksi"]');

                        // Mengatur nilai awal input dan dropdown
                        $gudangAsal.prop("selectedIndex", 0);
                        $gudangTujuan.prop("selectedIndex", 0);
                        $inGudangAsal.val('{{ $results['results']['detail']['kode_gudang_asal'] ?? '' }}');    
                        $inGudangTujuan.val('{{ $results['results']['detail']['kode_gudang_tujuan'] ?? '' }}');
                        $tglTransaksi.prop({disabled:true}).removeClass('pointer');
                        $inTglTransaksi.val('<?php echo Date::parse($results['results']['detail']['tanggal'])->format('d F Y') ?>');
                        $codeTransaksi.prop({disabled:true});
                        $fullName.val('{{ $results['results']['user_transaksi']['full_name'] ?? 'Belum ditentukan' }}');    
                        $codeTransaksi.val('{{ $results['results']['detail']['nomor'] ?? 'Belum ditentukan' }}');  
                        $keterangan.val('{{ $results['results']['detail']['ket'] ?? 'Belum ditentukan'}}');

                        // Inisialisasi datepicker
                        $tglTransaksi.datepicker({
                            format: 'dd MM yyyy',
                            startDate: '-1y',
                            endDate: '0d',
                            autoclose: true,
                            language: "id",
                            orientation: "bottom"
                        }).change(function(){
                            const value = $(this).val();
                            $('input[name="in_tgl_transaksi"]').val(value);
                            getcodemutasikirim();
                        });

                        // Disable/Enable data produk sesuai kondisi gudang
                        function toggleDataProduk() {
                            const isDisabled = !$inGudangAsal.val() || !$inGudangTujuan.val();
                            $dataProduk.prop('disabled', isDisabled).val(isDisabled ? '' : $dataProduk.val());
                        }

                        $gudangAsal.change(function() {
                            $inGudangAsal.val($(this).val());
                            toggleDataProduk();
                        });

                        $gudangTujuan.change(function() {
                            $inGudangTujuan.val($(this).val());
                            toggleDataProduk();
                        });

                        // Autocomplete untuk produk
                        $dataProduk.autocomplete({
                            minLength: 1,
                            source: "listbarangtransaksi?token={{ $request['token'] }}&u={{ $request['u'] }}",
                            autoFocus: true,
                            select: function(event, val){
                                if(val.item.code_data !== undefined){
                                    orderproduk(val.item.code_data);
                                }
                            }
                        });

                        // Event listener untuk tombol 'Save Data'
                        $('[btn="save_data"]').on('click', function () {
                            showConfirmationModal(
                                'Anda yakin untuk simpan dan selesaikan mutasi barang {{$results["results"]["detail"]["nomor"]}}. Setelah simpan dan selesai maka data tidak bisa diubah kembali.',
                                
                                function() {
                                    loadingpage(20000);
                                    const tgl_transaksi = $inTglTransaksi.val(); 
                                    const ket = $keterangan.val();
                                    window.location.href = "/admin/updatemutasikirim?d={{$results['results']['detail']['nomor']}}&tgl_transaksi=" + tgl_transaksi + "&ket=" + encodeURIComponent(ket);
                                }
                            );
                        });

                        // Event listener untuk tombol 'Cancel Data'
                        $('[btn="cancel_data"]').on('click', function () {
                            showConfirmationModal(
                                'Anda yakin untuk batalkan mutasi barang {{$results["results"]["detail"]["nomor"]}}.',
                                
                                function() {
                                    loadingpage(20000);
                                    window.location.href = "/admin/deletemutasikirim?d={{$results['results']['detail']['nomor']}}";  
                                }
                            );
                        });
                        

                        const loadingRow = `
                            <tr>
                                <td style="text-align:center; padding: 20px; background-color: #FFFFFF; cursor: default; font-weight: 600; height: 300px; font-size: 14px;" colspan="20">
                                    <div class="col-md-12 load_data_i text-center">
                                        <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                        <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                        <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('[line="list_produk_transakasi"]').html(loadingRow);

                        $.get("/admin/listprodmutasikirim", {
                            code_data: '{{$results["results"]["detail"]["nomor"]}}',
                            focus_line: '{{$request["fc"]}}'
                        }, function(listproduk) {
                            $('[line="list_produk_transakasi"]').html(listproduk);
                            $('input[name="data_produk"]').prop('disabled', false).focus();
                        });


                        function getcodemutasikirim() {     
                            const tgl_transaksi = $tglTransaksi.val();

                            $.getJSON(`getcodemutasikirim?token={{ $request['token'] }}&u={{ $request['u'] }}&tgl_transaksi=${tgl_transaksi}`, function(results) {
                                if(results.code_data) {
                                    $('input[name="in_code_transaksi"]').val(results.code_data);
                                    $codeTransaksi.val(results.code_data);
                                } else {
                                    console.error("Kode transaksi tidak ditemukan.");
                                }
                            }).fail(function(jqXHR, textStatus, errorThrown) {
                                console.error("Error saat mengambil kode transaksi: " + textStatus, errorThrown);
                            });
                        }

                        function orderproduk(produk) {
                            const data = {
                                code_data: $('input[name="code_data"]').val(),
                                code_transaksi: $codeTransaksi.val(),
                                tgl_transaksi: $inTglTransaksi.val(),
                                code_gudang_asal: $inGudangAsal.val(),
                                code_gudang_tujuan: $inGudangTujuan.val(),
                                keterangan: $keterangan.val(),
                                code_produk: produk,
                                qty: 1
                            };

                            // Nonaktifkan elemen terkait
                            $('.bg_act_page_main button, input[name="data_produk"]').prop('disabled', true);

                            $.ajax({
                                type: "POST",
                                url: "/admin/savemutasikirim?_token={{ csrf_token() }}&token={{ $request['token'] }}&u={{ $request['u'] }}",
                                data: data,
                                cache: false,
                                success: function(response) {
                                    if (response.status_message === 'failed') {
                                        showAlertModal('danger', response.note.code_transaksi ? 'No. Mutasi sudah terdaftar.' : 'Data gagal disimpan.');
                                    } else {
                                        $dataProduk.prop('readonly', true);
                                        $('[btn="save_data"], [btn="cancel_data"]').show();
                                        $tglTransaksi.prop('disabled', true).removeClass('pointer');
                                        $gudangAsal.prop('disabled', true);
                                        $gudangTujuan.prop('disabled', true);
                                        window.location.href = "/admin/viewmutasikirim?d=" + response.code;
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    console.error("Error saat menyimpan data: " + textStatus, errorThrown);
                                }
                            });
                        }

                        function showConfirmationModal(message, onConfirm) {
                            $('div[data-model="confirmasi"]').modal({ backdrop: false });
                            $('div[data-model="confirmasi"] .modal-body').html('<div class="alert alert-warning">' + message + '</div>');
                            $('button[btn-action="aciton-confirmasi"]').remove();
                            $('button[btn-action="close-confirmasi"]').before(
                                '<button type="button" class="btn btn-primary btn-sm" btn-action="aciton-confirmasi">Yakin</button>'
                            );
                            $(document).on('click', 'button[btn-action="aciton-confirmasi"]', function() {
                                onConfirm();
                                $('button[btn-action="aciton-confirmasi"], button[btn-action="close-confirmasi"]').remove();
                            });
                        }

                        function showAlertModal(type, message) {
                            $('div[data-model="confirmasi_data"]').modal({backdrop: false});
                            $('div[data-model="confirmasi_data"] .modal-body').html('<div class="alert alert-' + type + '">' + message + '</div>');
                        }
                    });
                </script>
            @endsection

@endsection