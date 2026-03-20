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

    $response = app(\App\Services\ApiServicePengeluarankas::class)->viewpengeluarankas($request);
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

        function __construct($data)
        {
            parent::__construct('P','mm','A4');
            $this->data = $data;
            $this->SetMargins(10,10,10);
            $this->SetAutoPageBreak(true,20);
        }

        function Header()
        {
            $d = $this->data;

            /* LOGO */
            $logo = $d['detail_perusahaan']['foto'] ?? 'icon.png';
            $this->Image('../themes/admin/AdminOne/image/public/'.$logo,10,8,22);

            /* COMPANY */
            $this->SetFont('Arial','B',12);
            $this->SetXY(35,8);
            $this->Cell(100,6,strtoupper($d['detail_perusahaan']['kantor']),0,1);

            $this->SetFont('Arial','',9);
            $this->SetX(35);
            $this->Cell(100,5,ucwords(strtolower($d['detail_perusahaan']['jenis'])),0,1);

            $this->SetX(35);
            $this->Cell(100,5,ucwords(strtolower($d['detail_perusahaan']['alamat'])),0,1);

            $this->SetX(35);
            $this->Cell(100,5,'Email : '.$d['detail_perusahaan']['email'],0,1);

            /* TITLE */
            $this->SetFont('Arial','B',12);
            $this->SetXY(120,8);
            $this->Cell(80,6,'DETAIL TRANSAKSI KAS',0,1,'L');

            /* INFO KANAN */
            $this->SetFont('Arial','',9);

            $this->SetXY(120,16);
            $this->Cell(30,5,'Date',0);
            $this->Cell(5,5,':');
            $this->Cell(40,5,isset($d['detail']['tanggal']) ? format_tgl_only(strtotime($d['detail']['tanggal'])): 'Belum ditentukan');

            $this->SetXY(120,21);
            $this->Cell(30,5,'Transaction',0);
            $this->Cell(5,5,':');
            $this->Cell(40,5,'Kas Keluar');

            $this->SetXY(120,26);
            $this->Cell(30,5,'No Voucher',0);
            $this->Cell(5,5,':');
            $this->Cell(40,5,$d['detail']['nomor']);

            $this->Ln(20);

            /* TABLE HEADER */
            $this->SetFont('Arial','B',9);
            $this->SetWidths([10,100,50,30]);
            $this->SetHeights([7]);
            $this->Cell(6);
            $this->Row([
                ['No','C'],
                ['Keterangan','C'],
                ['Nama Akun','C'],
                ['Nilai Transaksi','C']
            ]);
        }

        function AddContent()
        {
            $d = $this->data;

            $this->SetFont('Arial','',9);
            $this->SetHeights([8]);
            $this->Cell(6);
            $this->Row([
                [1,'C'],
                [$d['detail']['keterangan'] ?? '-', 'L'],
                [$d['detail']['jenis'] ?? '-', 'L'],
                [number_format($d['detail']['nilai'] ?? 0,2,",","."),'R']
            ]);

            $this->DrawTotal();
            $this->DrawSignature();
        }

        /* TOTAL TRANSAKSI */
        function DrawTotal()
        {
            $d = $this->data;

            $this->SetFont('Arial','B',10);
            $this->Cell(6);
            $this->Cell(110,8,'Total Transaksi :',1,0,'R');
            $this->Cell(80,8,number_format($d['detail']['nilai'] ?? 0,2,",","."),1,1,'R');
        }

        /* SIGNATURE */
        function DrawSignature()
        {
            $this->Ln(10);
            $this->SetFont('Arial','',10);

            $this->Cell(40);
            $this->Cell(50,6,'Payment By',0,0,'C');

            $this->Cell(20);
            $this->Cell(50,6,'Approved By',0,1,'C');

            $this->Ln(15);
            
            $this->Cell(40);
            $this->Cell(50,6,'( _______________ )',0,0,'C');

            $this->Cell(20);
            $this->Cell(50,6,'( _______________ )',0,1,'C');
        }

        /* FOOTER */
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','',8);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C' );
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