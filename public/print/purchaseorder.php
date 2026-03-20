<?php

    require __DIR__.'/../../vendor/autoload.php';
    $app = require_once __DIR__.'/../../bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    require __DIR__.'/../fpdf/MultiCellTable.php';
    require resource_path('views/admin/AdminOne/layout/function.blade.php');

    use Illuminate\Http\Request;

    if(!isset($_REQUEST['token'])){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $admin_login = $_REQUEST['u'];
    $key_token   = $_REQUEST['token'];
    $print_code  = $_REQUEST['print_code'];

    $request = new Request([
        'u' => $admin_login,
        'token' => $key_token,
        'code_data' => $print_code
    ]);
    

    $get_user = app('App\Http\Controllers\ApiController')->getadmin($request);
    $get_user = is_array($get_user) ? $get_user : $get_user->getData(true);
    
    if($get_user['status_message'] == 'failed'){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $response = app(\App\Services\ApiServicePembelian::class)->viewpembelian($request);
    $data = is_array($response) ? $response : $response->getData(true);

    if(($data['status_message'] ?? '') == 'failed'){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $getdata = $data['results'] ?? [];

    if(empty($getdata)){
        die('Data tidak ditemukan');
    }

    class PDF extends MultiCellTable
    {
        protected $data;
        public $isFinished = false;
        public $tableEndY = 0; // simpan posisi akhir tabel

        function __construct($data)
        {
            parent::__construct('P','mm','A4');
            $this->data = $data;
        }

        function Header()
        {
            $getdata = $this->data;

            if($getdata['detail_perusahaan']['foto'] == NULL){
                $this->Image('../themes/admin/AdminOne/image/public/icon.png',10,5.5,30);
            }else{
                $this->Image('../themes/admin/AdminOne/image/public/'.$getdata['detail_perusahaan']['foto'],10,5.5,30);
            }

            $this->SetFont('Arial','B',14);
            $this->Ln(3);
            $this->Cell(45);
            $this->Cell(0,5,strtoupper($getdata['detail_perusahaan']['kantor'] ?? ''),0,0,'L');

            $this->Ln(6);
            $this->SetFont('Arial','',9);
            $this->Cell(45);
            $this->MultiCell(210,5,ucwords(strtolower($getdata['detail_perusahaan']['jenis'] ?? '')),0,'L');

            $this->Cell(45);
            $this->Cell(0,5,ucwords(strtolower($getdata['detail_perusahaan']['alamat'] ?? '')),0,0,'L');

            $this->Ln();
            $this->Cell(45.5);
            $this->Cell(0,5,'Email : '.($getdata['detail_perusahaan']['email'] ?? ''),0,0,'L');

            $gety = $this->GetY();

            $this->SetLineWidth(0.6);
            $this->Line(10,$gety+7,197,$gety+7);

            $this->SetLineWidth(0.3);
            $this->Line(10,$gety+8,197,$gety+8);

            $this->Ln(10);

            $this->SetFont('Arial','B',14);
            $this->Cell(0,5,'PURCHASE ORDER',0,0,'C');

            $this->Ln(7);

            $mtx = $this->GetX();
            $gety = $this->GetY();
            $getx = $this->GetX();

            $setw5 = 5;
            $setw30 = 30;
            $setw40 = 40;
            $setw45 = 45;
            $setw70 = 70;
            $sethfull = 148;
            $seth = 4;
            $setmulti_h = 0;

            $this->setFont('Arial','B',10);
            $this->Cell(5);
            $this->MultiCell($setw40,$seth,'Supplier :',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,'',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw40,$seth,'',0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw70 + 7.5;
            $this->SetXY($getx, $gety);

            $this->setFont('Arial','',9);
            $this->Cell(1);
            $this->MultiCell($setw30,$seth,'Date',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw45, $seth, isset($getdata['detail']['tanggal']) ? date('j F Y', strtotime($getdata['detail']['tanggal'])) : 'Belum ditentukan', 0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            
            $this->Ln();
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);

            $this->setFont('Arial','B',9);
            $this->Cell(5);
            $this->MultiCell($setw70 + $setw40,$seth,($getdata['detail_supplier']['nama'] ?? ''),0);
            $this->setFont('Arial','',9);
            $this->Cell(5);
            $this->MultiCell($setw70 + 8,$seth,($getdata['detail_supplier']['alamat'] ?? ''),0,'L');
            $this->Cell(5);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $getx+=$setw70 + 7.5;
            $this->SetXY($getx, $gety);

            $this->Cell(1);
            $this->MultiCell($setw30,$seth,'Purchase No',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw45 + 10,$seth,($getdata['detail']['nomor'] ?? ''),0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            
            $this->Ln(7);
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);

            $this->setFont('Arial','',9);
            $this->Cell(5);
            $this->MultiCell($setw40,$seth,'Phone No',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);

            $this->Ln($setmulti_h-4);
            $this->Cell($setw40);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);

            $this->Ln($setmulti_h-4);
            $this->Cell($setw40 + $setw5);
            $this->MultiCell($setw70,$seth,($getdata['detail_supplier']['no_telp'] ?? ''),0);
            $getx+=$setw70 + 7.5;
            $this->SetXY($getx, $gety);
            
            $this->Ln(7);
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);

            $this->Cell(1);
            $this->MultiCell($setw30,$seth,'',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,'',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw45 + 10,$seth,'',0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);

            $this->Ln($setmulti_h); 
            $this->Ln(1); 

            $this->SetLineWidth(0.3);
            $this->SetWidths([7,47.5,28,17,28,28,30]);
            $this->SetHeights([7]);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);

            $this->SetFont('Arial','B',9);

            $this->Cell(6.5);
            $this->Row([
                ['No','C'],
                ['Product Name','C'],
                ['Price','C'],
                ['Qty','C'],
                ['Discount','C'],
                ['Netto','C'],
                ['Total','C'],
            ]);
        }

        function Footer()
        {
            if(!$this->isFinished) return;

            $getdata = $this->data;

            $this->SetY(-86.5);
                            
            $this->Ln(-1.9);
            $this->setFont('Arial','',8);

            $mtx = $this->GetX();
            $gety = $this->GetY();
            $getx = $this->GetX();

            $setw5 = 5;
            $setw30 = 30;
            $setw40 = 40;
            $setw45 = 45;
            $setw70 = 70;
            $sethfull = 148;
            $seth = 4;
            $setmulti_h = 0;

            $this->setFont('Arial','B',9);
            $this->Ln(1);
            $this->Cell(7);
            $this->MultiCell($setw40,$seth,'Note :',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,'',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw40,$seth,'',0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw70 - 7.5;
            $this->SetXY($getx, $gety);
            
            $this->Ln();
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);
            
            $this->setFont('Arial','',9);
            $this->Ln(1);
            $this->Cell(12);
            $this->MultiCell($setw70 + 30,$seth,($getdata['detail']['ket'] ?? ''),0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $getx+=$setw70 - 7.5;
            $this->SetXY($getx, $gety);

            $this->Ln(-4);

            $this->SetX(122);
            $this->Cell(40,5,'Total',0,0,'R');
            $this->Cell(34,5,number_format($getdata['detail']['total'] ?? 0,2,",","."),0,0,'R');

            $this->Ln();

            $this->SetX(122);
            $this->Cell(40,5,'Discount (-)',0,0,'R');
            $this->Cell(34,5,number_format($getdata['detail']['diskon_harga'] ?? 0,2,",","."),0,0,'R');

            $this->Ln();

            $this->SetX(122);
            $this->SetFont('Arial','',9);
            $this->Cell(40,5,'Cash Diskon (-)',0,0,'R');
            $this->Cell(34,5,number_format($getdata['detail']['diskonCash_persen'] ?? 0,2,",","."),0,0,'R'); 

            $this->Ln();

            $this->SetX(122);
            $this->SetFont('Arial','B',9);
            $this->Cell(40,5,'Grand Total',0,0,'R');
            $this->Cell(34,5,number_format($getdata['detail']['grand_total'] ?? 0,2,",","."),0,0,'R');           

            $this->Ln();

            $this->setX(122);
            $this->setFont('Arial','',9);
            $this->cell(40,5,'DPP',0,0,'R');
            $this->cell(34,5,number_format($getdata['detail']['sub_total'] ?? 0,2,",","."),0,0,'R');
            $this->Ln();
            $this->setX(122);
            $this->cell(40,5,'PPN',0,0,'R');
            $this->cell(34,5,number_format($getdata['detail']['ppn'] ?? 0,2,",","."),0,0,'R');
            $this->Ln();
                            
            $this->Ln();

            $this->SetLineWidth(0.3);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);
            
            // Garis kotak grand total
            $this->Line(11.5,$gety-5,197,$gety-5);
            $this->Line(11.5,$gety+26,197,$gety+26);
            $this->Line(11.5,$gety+38,197,$gety+38);
            $this->Line(11.5,$gety-5,11.5,263.2);
            $this->Line(139,$gety-5,139,238.5);
            $this->Line(197,$gety-5,197,263.2);
            $this->Line(11.5,$gety+50.5,197,$gety+50.5); // Garis dibawah shipping adress            

            $topTable = $this->tableEndY;
            $this->Line(11.5,$topTable,11.5,$gety-5);
            $this->Line(18.5,$topTable,18.5,$gety-5);
            $this->Line(66,$topTable,66,$gety-5);
            $this->Line(94,$topTable,94,$gety-5);
            $this->Line(111,$topTable,111,$gety-5);
            $this->Line(139,$topTable,139,$gety-5);
            $this->Line(167,$topTable,167,$gety-5);
            $this->Line(197,$topTable,197,$gety-5);
            
            // $this->Ln(0.85);
            $this->Ln(-4.5);
            $this->setX(11.5);
            $this->setFont('Arial','IB',9);
            $this->MultiCell(185.5,7,terbilang(number_format($getdata['detail']['grand_total'] ?? 0,2,",","")).' rupiah',0,'L');

            $hmt_add = 3.5;
            $this->Ln(7.35);
            $this->setX(11.5);
            $this->setFont('Arial','B',9);
            $this->MultiCell(185.5,$hmt_add,'Shipping Address :',0);
            $this->setX(14.5);
            $this->setFont('Arial','',9);
            $this->MultiCell(178.5,$hmt_add,($getdata['detail_gudang']['alamat'] ?? ''),0,'L');
            $this->Ln(5.5);

            $getyln = $this->GetY();
            $this->SetLineWidth(0.3);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);
            $this->Line(11.5,$getyln-5,11.5,287);
            $this->Line(69,$getyln-3,69,287);
            $this->Line(197,$getyln-5,197,287);
            $this->Line(134,$getyln-3,134,287);
            $this->Line(11.5,$getyln+21,197,$getyln+21);
                        
            $this->setFont('Arial','',10);
            $this->Cell(25);
            $this->Cell(20,5,'',0,0,'C');
            $this->Ln(16);
            $this->Cell(25);
            $this->Cell(20,5,'',0,0,'C');
                        
            $this->Ln(-18);
            $this->setFont('Arial','',10);
            $this->Cell(85);
            $this->Cell(20,5,'Approved By',0,0,'C');
            $this->Ln(16);
            $this->Cell(85);
            $this->Cell(20,5,'( _______________ )',0,0,'C');
            // $this->Cell(20,5,'( '.$getdata['user_transaksi']['full_name'].' )',0,0,'C');
                        
            $this->Ln(-16);
            $this->setFont('Arial','',10);
            $this->Cell(150);
            $this->Cell(20,5,'Purchase By',0,0,'C');

            $this->Ln(16);
            $this->Cell(150);
            $this->Cell(20,5,'( '.($getdata['user_transaksi']['full_name'] ?? '').' )',0,0,'C');

            $this->SetY(-10);
            $this->SetFont('Arial','B',7);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }

        function AddContent()
        {
            $getdata = $this->data;
            $this->SetFont('Arial','',8);

            $no = 1;

            $widths = [7,47.5,28,17,28,28,30];
            $align  = ['C','L','R','C','C','R','R'];

            $startTableX = 11.5; // posisi kiri tabel

            foreach ($getdata['list_produk'] as $view_data) {
                $kode_barang = $view_data['kode_barang'];
                $nama_barang = $getdata['detail_produk'][$kode_barang]['nama'] ?? 'Belum ditentukan';
                $harga = $view_data['harga'] ?? 0;
                $qty = number_format($view_data['jumlah_beli'] ?? 0,0,"",".").' '.($getdata['satuan_produk'][$kode_barang]['nama'] ?? '');

                $diskonList = [
                    $view_data['diskon_persen'] ?? 0,
                    $view_data['diskon_persen2'] ?? 0,
                    $view_data['diskon_persen3'] ?? 0
                ];

                $filteredDiskon = array_filter($diskonList, fn($d)=>$d>0);

                if(empty($filteredDiskon)){
                    $filteredDiskon=[0];
                }

                $discText = implode('+',array_map(
                    fn($d)=>number_format($d,2,",","."),
                    $filteredDiskon
                ));

                $row = [
                    $no,
                    $nama_barang,
                    number_format($harga,2,",","."),
                    $qty,
                    $discText,
                    number_format($view_data['harga_netto'] ?? 0,2,",","."),
                    number_format($view_data['total_harga'] ?? 0,2,",",".")
                ];

                /* Hitung tinggi row */
                $nb = 0;

                foreach($row as $key=>$txt){
                    $nb = max($nb,$this->NbLines($widths[$key],$txt));
                }

                $h = 5 * $nb;

                $this->CheckPageBreak($h);

                $startX = $this->GetX() + 6.5;
                $startY = $this->GetY();

                $this->Cell(6.5);

                /* Cetak isi row */
                foreach($row as $i=>$txt){
                    $w = $widths[$i];
                    $a = $align[$i];

                    $x = $this->GetX();
                    $y = $this->GetY();

                    $this->MultiCell($w,5,$txt,0,$a);
                    $this->SetXY($x+$w,$y);
                }

                $this->Ln($h);

                /* Garis vertikal */
                $x = $startX;

                foreach($widths as $w){
                    $this->Line($x,$startY,$x,$startY+$h);
                    $x += $w;
                }

                /* garis kanan */
                $this->Line($x,$startY,$x,$startY+$h);
                $no++;
            }

            // simpan posisi akhir tabel
            $this->tableEndY = $this->GetY();
        }
    }

    

    $pdf = new PDF($getdata);
    $pdf->SetMargins(5,5,10);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->AddContent();
    $pdf->SetAutoPageBreak(false);

    $pdf->isFinished = true;

    $pdf->Output('I','Print-'.$getdata['detail']['nomor'].'.pdf');