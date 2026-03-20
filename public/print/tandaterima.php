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
        'nomor_penerimaan' => $print_code
    ]);
    

    $get_user = app('App\Http\Controllers\ApiController')->getadmin($request);
    $get_user = is_array($get_user) ? $get_user : $get_user->getData(true);
    
    if($get_user['status_message'] == 'failed'){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $response = app(\App\Services\ApiServicePenerimaanbarang::class)->viewpenerimaan($request);
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
            $this->Cell(0,5,'PENERIMAAN BARANG',0,0,'C');

            $this->Ln(10);

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
            $this->Cell(5);
            $this->MultiCell($setw40,$seth,'Receipt No :',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,'',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw40,$seth,($getdata['detail']['nomor_penerimaan'] ?? 'Belum ditentukan'),0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw70 - 7.5;
            $this->SetXY($getx, $gety);

            $this->setFont('Arial','',9);
            $this->Cell(17.5);
            $this->MultiCell($setw30,$seth,'Purchase No',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->Cell(17.5);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->Cell(16.5);
            $this->MultiCell($setw45,$seth, ($getdata['detail_pembelian']['nomor'] ?? 'Belum ditentukan'),0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            
            $this->Ln();
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);

            $this->setFont('Arial','',9);
            $this->Cell(5);
            $this->MultiCell($setw40,$seth,'Date',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw45 + 8,$seth,isset($getdata['detail']['tanggal']) ? date('j F Y',strtotime($getdata['detail']['tanggal'])) : 'Belum ditentukan',0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            $getx+=$setw5 + 12.5;
            $this->SetXY($getx, $gety);

            $this->Cell(17.5);
            $this->MultiCell($setw30,$seth,'Date',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->Cell(17.5);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->Cell(16.5);
            $this->MultiCell($setw30 + 10,$seth,isset($getdata['detail_pembelian']['tanggal']) ? date('j F Y',strtotime($getdata['detail_pembelian']['tanggal'])) : 'Belum ditentukan',0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            
            $this->Ln();
            $getx=$mtx; 
            $gety+=$seth;
            $this->SetXY($getx, $gety);
            
            $this->Ln($setmulti_h-4);

            $this->setFont('Arial','',9);
            $this->Cell(5);
            $this->MultiCell($setw40,$seth,'Supplier',0);
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->MultiCell($setw45 + 8,$seth,($getdata['detail_supplier']['nama'] ?? 'Belum ditentukan'),0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw45;
            $this->SetXY($getx, $gety);
            $getx+=$setw5 + 12.5;
            $this->SetXY($getx, $gety);

            $this->Cell(17.5);
            $this->MultiCell($setw30,$seth,'Warehouse',0);
            $getx+=$setw30;
            $this->SetXY($getx, $gety);
            $this->Cell(17.5);
            $this->MultiCell($setw5,$seth,':',0);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $this->Cell(16.5);
            $this->MultiCell($setw30 + 10,$seth,($getdata['detail_gudang']['nama'] ?? 'Belum ditentukan'),0);
            $getx+=$setw45;
            $this->SetXY($getx, $gety);

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
            $this->Ln(3); 

            $this->SetLineWidth(0.3);
            $this->SetWidths([7,150.5,28]);
            $this->SetHeights([7]);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);

            $this->SetFont('Arial','B',9);
            $this->Ln(1);
            $this->Cell(6.5);
            $this->Row([
                ['No','C'],
                ['Product Name','C'],
                ['Qty','C'],
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
            $this->MultiCell($sethfull,$seth,($getdata['detail']['ket'] ?? 'Belum ditentukan'),0);
            $setmulti_h = $this->GetY();
            $setmulti_h= $setmulti_h-$gety;
            $getx+=$setw40;
            $this->SetXY($getx, $gety);
            $getx+=$setw5;
            $this->SetXY($getx, $gety);
            $getx+=$setw70 - 7.5;
            $this->SetXY($getx, $gety);

            $this->Ln(4); 

            $this->SetLineWidth(0.3);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);
            
            // Garis kotak grand total
            $this->Line(11.5,$gety-5,197,$gety-5);
            $this->Line(11.5,$gety+19,197,$gety+19);
            $this->Line(11.5,$gety-5,11.5,259.2);
            $this->Line(197,$gety-5,197,259.2);
            $this->Line(11.5,$gety+46.5,197,$gety+46.5);

            $this->Line(11.5,$getx,11.5,$gety-5);
            $this->Line(18.5,$getx,18.5,$gety-5);
            $this->Line(169,$getx,169,$gety-5);
            $this->Line(197,$getx,197,$gety-5);

            $hmt_add = 3.5;
            $this->Ln(3.35);
            $this->setX(11.5);
            $this->setFont('Arial','B',9);
            $this->MultiCell(185.5,$hmt_add,'Reception Address :',0);
            $this->setX(14.5);
            $this->setFont('Arial','',9);
            $this->MultiCell(178.5,$hmt_add,($getdata['detail_gudang']['alamat'] ?? 'Belum ditentukan'),0,'L');
            $this->Ln(5.5);

            $getyln = $this->GetY();
            $this->SetLineWidth(0.3);
            $this->setDrawColor(0,0,0);
            $this->setTextColor(0,0,0);
            $this->setFillColor(255,255,255);
                        
            $this->setFont('Arial','',10);
            $this->Cell(25);
            $this->Cell(20,5,'',0,0,'C');
            $this->Ln(20);
            $this->Cell(25);
            $this->Cell(20,5,'',0,0,'C');
                        
            $this->Ln(-20);
            $this->setFont('Arial','',10);
            $this->Cell(85);
            $this->Cell(20,5,'Received By',0,0,'C');
            $this->Ln(20);
            $this->Cell(85);
            $this->Cell(20,5,'( '.($getdata['user_transaksi']['full_name'] ?? 'Belum ditentukan').' )',0,0,'C');
                        
            $this->Ln(-20);
            $this->setFont('Arial','',10);
            $this->Cell(150);
            $this->Cell(20,5,'Delivered By',0,0,'C');
            $this->Ln(20);
            $this->Cell(150);
            $this->Cell(20,5,'( _______________ )',0,0,'C');

            $this->SetY(-10);
            $this->SetFont('Arial','B',7);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }

        function AddContent()
        {
            $getdata = $this->data;

            $gety = $this->GetY();
            $getx = $this->GetX();

            $height_of_cell = 60;
            $page_height = 327.5;
            $bottom_margin = 80;

            $getx_ln = $page_height - $gety;
            $getx_ln = $getx_ln - 55.5;

            $getx_isf = $page_height - $getx;
            $getx_isf = $getx_isf - 45.5;

            $this->SetFont('Arial','',8);

            $no = 1;

            foreach ($getdata['list_produk_group'] as $view_data) {
                $id = $view_data['id'];
                $id = str_replace('-','',$id);
                $nm_prod = ($getdata['detail_produk'][$id]['nama'] ?? 'Belum ditentukan');
                $qty_terima = ($view_data['jumlah_terima'] ?? 0);

                $this->SetHeights([5]);

                $this->SetDrawColor(255,255,255);
                $this->SetTextColor(0,0,0);
                $this->SetFillColor(255,255,255);

                $this->Cell(6.5);

                $this->Row([
                    [$no,'C'],
                    [$nm_prod,'L'],
                    [number_format($qty_terima ?? 0,2,",",".").' '.($getdata['satuan_produk'][$id]['nama'] ?? 'Belum ditentukan'),'C'],
                ]);

                $this->CheckPageBreak(6.5);

                $this->SetDrawColor(0,0,0);

                $this->Line(11.5,$gety,11.5,$getx_ln);
                $this->Line(18.5,$gety,18.5,$getx_ln);
                $this->Line(169,$gety,169,$getx_ln);
                $this->Line(197,$gety,197,$getx_ln);

                $space_left = $page_height - ($this->GetY() + $bottom_margin);

                if ($height_of_cell > $space_left) {
                    $this->Line(11.5,$getx_isf,11.5,$gety);
                    $this->Line(18.5,$getx_isf,18.5,$gety-5);
                    $this->Line(169,$getx_isf,169,$gety-5);
                    $this->Line(197,$getx_isf,197,$gety-5);

                    $this->Line(11.5,$getx_isf,197,$getx_isf);

                    $this->AddPage();

                    $this->Ln(1);

                    $this->isFinished=false;
                }

                $no++;
            }
        }
    }

    $pdf = new PDF($getdata);
    $pdf->SetMargins(5,5,10);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->AddContent();
    $pdf->SetAutoPageBreak(false);

    $pdf->isFinished = true;

    $pdf->Output('I','Print-'.$getdata['detail']['nomor_penerimaan'].'.pdf');