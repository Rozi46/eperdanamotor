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

    $response = app(\App\Services\ApiServiceMutasikirim::class)->viewmutasikirim($request);
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

        public $tableStartX = 11.5;
        public $tableBottom = 275;
        public $tableEndY   = 0;
        public $isFinished  = false;

        function __construct($data)
        {
            parent::__construct('P','mm','A4');
            $this->data = $data;
        }

        private function drawColumnLines($startX,$startY,$widths,$h)
        {
            $x = $startX;
            foreach($widths as $w){
                $this->Line($x,$startY,$x,$startY+$h);
                $x += $w;
            }
            $this->Line($x,$startY,$x,$startY+$h);
        }
        
        function rowHeaderDetail($label1,$value1,$label2,$value2)
        {
            $seth = 4;

            $x = $this->GetX();
            $y = $this->GetY();

            // kiri
            $this->Cell(5);
            $this->MultiCell(40,$seth,$label1,0);
            $this->SetXY($x+35,$y);
            $this->MultiCell(5,$seth,':',0);
            $this->SetXY($x+40,$y);
            $this->MultiCell(53,$seth,$value1,0);

            $yLeft = $this->GetY();

            // kanan
            $this->SetXY($x+120,$y);
            $this->MultiCell(30,$seth,$label2,0);
            $this->SetXY($x+150,$y);
            $this->MultiCell(5,$seth,':',0);
            $this->SetXY($x+155,$y);
            $this->MultiCell(40,$seth,$value2,0);

            $yRight = $this->GetY();

            // ambil tinggi terbesar
            $newY = max($yLeft,$yRight);
            $this->SetXY($x,$newY);
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
            $this->Cell(0,5,'MUTASI KIRIM BARANG',0,0,'C');

            $this->Ln(10);

            $this->SetFont('Arial','',9);
            $this->rowHeaderDetail(
                'No. Mutasi',$getdata['detail']['nomor'] ?? 'Belum ditentukan',
                'Gudang Asal',$getdata['detail_gudang_asal']['nama'] ?? 'Belum ditentukan'
            );

            $this->rowHeaderDetail(
                'Tanggal',isset($getdata['detail']['tanggal']) ? date('j F Y', strtotime($getdata['detail']['tanggal'])) : 'Belum ditentukan',
                'Gudang Tujuan',$getdata['detail_gudang_tujuan']['nama'] ?? 'Belum ditentukan'
            );

            $this->Ln(5);

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
            $data = $this->data;
            $this->SetFont('Arial','B',7);

            /* PAGE NUMBER (SEMUA HALAMAN) */
            $this->SetY(-10);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');   

            /* GARIS PENUTUP TABEL HALAMAN 1 */
            if(!$this->isFinished && $this->PageNo() >= 1){
                $this->Line(11.5,$this->tableBottom,197,$this->tableBottom);
            }

            /* FOOTER DETAIL HANYA HALAMAN AKHIR*/
            if(!$this->isFinished) return;
            $this->SetY(-70);
            $yFooter  = $this->GetY();
            $topTable = $this->tableEndY;
            $this->SetLineWidth(0.3);
            $this->SetDrawColor(0,0,0);

            /* SAMBUNG TABEL KE FOOTER */
            $this->Line(11.5,$topTable,11.5,$yFooter-2);
            $this->Line(18.5,$topTable,18.5,$yFooter-2);
            $this->Line(169,$topTable,169,$yFooter-2);
            $this->Line(197,$topTable,197,$yFooter-2);

            /* BORDER KIRI KANAN */
            $this->Line(11.5,$yFooter-3,11.5,284);
            $this->Line(197,$yFooter-3,197,284);

            /* GARIS FOOTER */
            $this->Line(11.5,$yFooter-2,197,$yFooter-2);
            $this->Line(11.5,$yFooter+25,197,$yFooter+25);
            $this->Line(11.5,$yFooter+57,197,$yFooter+57);

            /* NOTE */
            $this->SetFont('Arial','B',9);
            $this->Cell(7);
            $this->Cell(40,4,'Note :',0,1);
            $this->SetFont('Arial','',9);
            $this->Cell(12);
            $this->MultiCell(148,4,$data['detail']['ket'] ?? 'Belum ditentukan');

            /* DELIVERY ADDRESS */
            $this->Ln(4);
            $this->SetFont('Arial','B',9);
            $this->Cell(7);
            $this->Cell(0,4,'Delivery Address :',0,1);
            $this->SetFont('Arial','',9);
            $this->Cell(12);
            $this->MultiCell(178,4,$data['detail_gudang_tujuan']['alamat'] ?? 'Belum ditentukan');

            /* SIGNATURE */
            $this->Ln(8);
            $this->SetFont('Arial','',9);
            $this->Cell(95,5,'Delivered By',0,0,'C');
            $this->Cell(95,5,'Received By',0,1,'C');
            $this->Ln(18);
            $this->Cell(95,5,'( '.($data['user_transaksi']['full_name'] ?? '').' )',0,0,'C');
            $this->Cell(95,5,'( _______________ )',0,1,'C');
        }

        function AddContent()
        {
            $data = $this->data;
            $this->SetFont('Arial','',8);
            $widths=[7,150.5,28];
            $align=['C','L','C'];
            $no=1;

            foreach($data['list_produk'] as $item){
                $kode=$item['kode_barang'];
                $nama=$data['detail_produk'][$kode]['nama'] ?? 'Belum ditentukan';
                $qty=number_format($item['qty'] ?? 0,2,",",".").' '.($data['satuan_produk'][$kode]['nama'] ?? '');

                $row=[$no,$nama,$qty];

                /* hitung tinggi row */
                $nb=0;
                foreach($row as $k=>$txt){
                    $nb=max($nb,$this->NbLines($widths[$k],$txt));
                }

                $h=5*$nb;

                $this->CheckPageBreak($h);

                $startX=11.5;
                $startY=$this->GetY();

                $this->SetX($startX);

                foreach($row as $i=>$txt){
                    $w=$widths[$i];
                    $a=$align[$i];

                    $x=$this->GetX();
                    $y=$this->GetY();

                    $this->MultiCell($w,5,$txt,0,$a);
                    $this->SetXY($x+$w,$y);
                }

                $this->Ln($h);

                /* garis vertikal */
                $this->drawColumnLines($startX,$startY,$widths,$h);
                $no++;
            }

            /* simpan posisi akhir tabel */
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