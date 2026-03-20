      
            <div class="modal fade" role="dialog" data-model="listproduk">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div line="hd_model" class="hd_model">Data Barang</div>
						    <div class="col-md-12 bg_page_main" style="padding: 0px; margin-bottom: 10px; border:0px;">
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px; padding-left: 0px; padding-right: 0px; padding-top: 0px; margin-bottom: 0px;">
                                            <div class="col-md-12 col_act_page_main text-right">
                                                <input type="text" class="form_group search" name="cari_produk_popup" placeholder="Scan atau cari data barang" value="" style="padding:10px 5px;" autofocus/>
                                                <div class="btn_group">
                                                    <button class="btn text_group" btn="cari_produk_popup">Cari</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col_act_page_main text-left text_note">*Silakan klik data untuk menambah ke transaksi.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 row_list_popup">
                                <div class="row">
                                    <div class="col-md-12 bg_hd_list_popup">
                                        <div class="row">
                                            <div class="col-md-2 hd_list_popup text-center">SKU Barang</div>
                                            <?php if($request->stock_prod_popup == 'yes'){?>
                                                <div class="col-md-8 hd_list_popup">Nama Barang</div>
                                                <div class="col-md-2 hd_list_popup last text-center" data="view_stock">Stock</div>
                                            <?php }else{ ?>
                                                <div class="col-md-10 hd_list_popup">Nama Barang</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="list_data_popup" line="list_data_produk" style="width:100%;">
                                        <div class="col-md-12 load_data_i text-center">
                                            <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                            <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                            <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn="closelistproduk">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" role="dialog" data-model="listsnproduk">
                <div class="modal-dialog modal-ls">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div line="hd_model" class="hd_model">Data Serial Number</div>
						    <div class="col-md-12 bg_page_main" style="padding: 0px; margin-bottom: 10px; border:0px;">
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px; padding-left: 0px; padding-right: 0px; padding-top: 0px; margin-bottom: 0px;">
                                            <div class="col-md-12 col_act_page_main text-right">
                                                <input type="text" class="form_group search" name="sn_popup_code_gudang" value="" style="display:none;"/>
                                                <input type="text" class="form_group search" name="sn_popup_code_prod" value="" style="display:none;"/>
                                                <input type="text" class="form_group search" name="cari_sn_produk_popup" placeholder="Scan atau cari serial number" value="" style="padding:10px 5px;" autofocus/>
                                                <div class="btn_group">
                                                    <button class="btn text_group" btn="cari_sn_produk_popup">Cari</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col_act_page_main text-left text_note">*Silakan klik data untuk menambah ke transaksi.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 row_list_popup">
                                <div class="row">
                                    <div class="col-md-12 bg_hd_list_popup">
                                        <div class="row">
                                            <div class="col-md-12 hd_list_popup last">Serial Number</div>
                                        </div>
                                    </div>
                                    <div class="list_data_popup" line="list_data_sn_produk" style="width:100%;">
                                        <div class="col-md-12 load_data_i text-center">
                                            <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                            <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                            <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn="closelistsnproduk">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" role="dialog" data-model="listproduksewa">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div line="hd_model" class="hd_model">Data Barang Sewa</div>
						    <div class="col-md-12 bg_page_main" style="padding: 0px; margin-bottom: 10px; border:0px;">
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px; padding-left: 0px; padding-right: 0px; padding-top: 0px; margin-bottom: 0px;">
                                            <div class="col-md-12 col_act_page_main text-right">
                                                <input type="text" class="form_group search" name="cari_produk_sewa_popup" placeholder="Scan atau cari data barang" value="" style="padding:10px 5px;" autofocus/>
                                                <div class="btn_group">
                                                    <button class="btn text_group" btn="cari_produk_sewa_popup">Cari</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col_act_page_main text-left text_note">*Silakan klik data untuk menambah ke transaksi.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 row_list_popup">
                                <div class="row">
                                    <div class="col-md-12 bg_hd_list_popup">
                                        <div class="row">
                                            <div class="col-md-2 hd_list_popup text-center">SKU Barang</div>
                                            <div class="col-md-7 hd_list_popup">Nama Barang</div>
                                            <div class="col-md-3 hd_list_popup text-center">Serial Number</div>
                                        </div>
                                    </div>
                                    <div class="list_data_popup" line="list_data_produk" style="width:100%;">
                                        <div class="col-md-12 load_data_i text-center">
                                            <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                            <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                            <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn="closelistproduk">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" role="dialog" data-model="listpermintaan">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div line="hd_model" class="hd_model">Data Permintaan</div>
						    <div class="col-md-12 bg_page_main" style="padding: 0px; margin-bottom: 10px; border:0px;">
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px; padding-left: 0px; padding-right: 0px; padding-top: 0px; margin-bottom: 0px;">
                                            <div class="col-md-12 col_act_page_main text-right">
                                                <input type="text" class="form_group search" name="cari_permintaan_popup" placeholder="Cari data permintaan" value="" style="padding:10px 5px;" autofocus/>
                                                <div class="btn_group">
                                                    <button class="btn text_group" btn="cari_permintaan_popup">Cari</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col_act_page_main text-left text_note">*Silakan klik data untuk menambah ke transaksi.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 row_list_popup">
                                <div class="row">
                                    <div class="col-md-12 bg_hd_list_popup">
                                        <div class="row">
                                            <div class="col-md-2 hd_list_popup text-center">No. Permintaan</div>
                                            <div class="col-md-2 hd_list_popup text-center">SKU Barang</div>
                                            <div class="col-md-6 hd_list_popup">Nama Barang</div>
                                            <div class="col-md-2 hd_list_popup last text-center">Qty Permintaan</div>
                                        </div>
                                    </div>
                                    <div class="list_data_popup" line="list_data_permintaan" style="width:100%;">
                                        <div class="col-md-12 load_data_i text-center">
                                            <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                            <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                            <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" btn="closelistpermintaan">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" role="dialog" data-model="newserialnumber">
                <div class="modal-dialog modal-ls">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div line="hd_model" class="hd_model">Input Serial Number</div>
						    <div class="col-md-12 bg_page_main" style="padding: 0px; margin-bottom: 10px; border:0px;">
                                <div class="col-md-12 data_page">
                                    <div class="row bg_data_page form_page content">
                                        <div class="col-md-12 bg_act_page_main cari" style="padding: 5px; padding-bottom: 0px; padding-left: 0px; padding-right: 0px; padding-top: 0px; margin-bottom: 0px;">
                                            <div class="col-md-12 col_act_page_main text-right">
                                                <input type="text" class="form_group search" name="qty_sn_rdo" value="" style="display:none;"/>
                                                <input type="text" class="form_group search" line="save_new_sn" name="save_new_sn" placeholder="Scan atau input serial number" value="" style="padding:10px 5px;" autofocus/>
                                                <div class="btn_group">
                                                    <button class="btn text_group" btn="save_new_sn">Input & Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col_act_page_main text-left text_note">*Untuk menghapus data silakan klik data serial number.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 row_list_popup">
                                <div class="row">
                                    <div class="col-md-12 bg_hd_list_popup">
                                        <div class="row">
                                            <div class="col-md-12 hd_list_popup last">Serial Number</div>
                                        </div>
                                    </div>
                                    <div class="list_data_popup" line="list_serial_number_rdo" style="width:100%;">
                                        <div class="col-md-12 load_data_i text-center">
                                            <div class="spinner-grow spinner-grow-sm text-muted"></div>
                                            <div class="spinner-grow spinner-grow-sm text-secondary"></div>
                                            <div class="spinner-grow spinner-grow-sm text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-sm" btn="closesnrdo">Tutup & Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" role="dialog" data-model="confirmasi_data">
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