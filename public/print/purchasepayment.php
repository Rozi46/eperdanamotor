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
        'code_transaksi' => $print_code
    ]);    

    $get_user = app('App\Http\Controllers\ApiController')->getadmin($request);
    $get_user = is_array($get_user) ? $get_user : $get_user->getData(true);
    
    if($get_user['status_message'] == 'failed'){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $response = app(\App\Services\ApiServicePembayaranpembelian::class)->viewpurchasepayment($request);
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
            $d = $this->data;

            // Logo
            $logo = $d['detail_perusahaan']['foto'] ?? 'icon.png';
            $this->Image('../themes/admin/AdminOne/image/public/'.$logo,10,6,22);

            // Company Name
            $this->SetFont('Arial','B',12);
            $this->SetXY(35,8);
            $this->Cell(100,5,strtoupper($d['detail_perusahaan']['kantor']),0,0,'L');

            // Title
            $this->SetFont('Arial','B',12);
            $this->SetXY(120,8);
            $this->Cell(80,5,'PAYMENT RECEIPT - BILL',0,0,'L');

            // Company Info
            $this->SetFont('Arial','',9);
            $this->SetXY(35,13);
            $this->Cell(100,4,ucwords(strtolower($d['detail_perusahaan']['jenis'])),0,1);

            $this->SetX(35);
            $this->Cell(100,4,ucwords(strtolower($d['detail_perusahaan']['alamat'])),0,1);

            $this->SetX(35);
            $this->Cell(100,4,'Email : '.$d['detail_perusahaan']['email'],0,1);


            // Info kanan
            $this->SetFont('Arial','',9);

            $this->SetXY(120,15);
            $this->Cell(30,4,'Date',0);
            $this->Cell(5,4,':');
            $this->Cell(50,4,format_tgl_tree(strtotime($d['detail']['tanggal'])),0);

            $this->SetXY(120,20);
            $this->Cell(30,4,'Voucher No',0);
            $this->Cell(5,4,':');
            $this->Cell(50,4,$d['detail']['nomor'],0);

            $this->SetXY(120,25);
            $this->Cell(30,4,'Purchase Order',0);
            $this->Cell(5,4,':');
            $this->Cell(50,4,$d['detail']['nomor_hutang'],0);

            $this->Ln(15);


            // Payment To
            $this->SetFont('Arial','',9);
            $this->Cell(5);
            $this->Cell(40,5,'Payment To :',0,1);

            $this->SetFont('Arial','B',9);
            $this->Cell(12);
            $this->Cell(0,5,$d['detail_supplier']['nama'],0,1);

            $this->SetFont('Arial','',9);
            $this->Cell(12);
            $this->Cell(0,5,$d['detail_supplier']['alamat'],0,1);

            $this->Cell(5);
            $this->Cell(40,5,'Phone No',0,0);
            $this->Cell(5,5,':');
            $this->Cell(0,5,$d['detail_supplier']['no_telp'],0,1);

            $this->Ln(3);
        }

        function AddContent()
        {
            $d = $this->data;

            $amount = number_format($d['detail']['jumlah'],2,",",".");
            $terbilang = ucfirst(terbilang($d['detail']['jumlah']));

            $x = 11.5;
            $y = $this->GetY();

            $this->Rect($x,$y,185,32);

            // Amount
            $this->SetXY($x+5,$y+6);

            $this->SetFont('Arial','B',10);
            $this->Cell(35,6,'Amount');

            $this->SetFont('Arial','',10);
            $this->Cell(5,6,':');

            $textX = $this->GetX();

            $this->SetFont('Arial','B',10);
            $this->MultiCell(120,6,$amount);

            $lineY = $this->GetY();
            $this->Line($textX,$lineY,$x+180,$lineY);


            // Terbilang
            $this->SetX($x+40);

            $this->SetFont('Arial','IB',10);
            $this->Cell(5,6,':');

            $textX = $this->GetX();

            $this->MultiCell(120,6,$terbilang);

            $lineY = $this->GetY();
            $this->Line($textX,$lineY,$x+180,$lineY);


            $this->DrawSignature();
        }

        function DrawSignature()
        {
            $this->Ln(10);

            $this->SetFont('Arial','',10);

            $this->Cell(60);
            $this->Cell(40,5,'Receiver By',0,0,'C');

            $this->Cell(40);
            $this->Cell(40,5,'Approved By',0,1,'C');

            $this->Ln(20);

            $this->Cell(60);
            $this->Cell(40,5,'( _______________ )',0,0,'C');

            $this->Cell(40);
            $this->Cell(40,5,'( _______________ )',0,1,'C');
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