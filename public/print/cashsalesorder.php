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

    $response = app(\App\Services\ApiServiceCashier::class)->viewpenjualan($request);
    $data = is_array($response) ? $response : $response->getData(true);

    if(($data['status_message'] ?? '') == 'failed'){
        echo "<meta http-equiv='refresh' content='0;/'>";
        exit;
    }

    $getdata = $data['results'] ?? [];

    if(empty($getdata)){
        die('Data tidak ditemukan');
    }

    // class PDF extends MultiCellTable
    // {
    //     protected $data;

    //     function __construct($data, $height)
    //     {
    //         parent::__construct('P','mm',[80, $height]);
    //         $this->SetMargins(5,5,5);
    //         $this->data = $data;
    //     }

    //     function Header()
    //     {
    //         $getdata = $this->data;

    //         // Logo
    //         if(empty($getdata['detail_perusahaan']['foto'])){
    //             $this->Image('../themes/admin/AdminOne/image/public/icon.png',32,5,16);
    //         }else{
    //             $this->Image('../themes/admin/AdminOne/image/public/'.$getdata['detail_perusahaan']['foto'],32,5,16);
    //         }

    //         $this->Ln(14);

    //         // Nama Perusahaan
    //         $this->SetFont('Arial','B',10);
    //         $this->Cell(0,4,strtoupper($getdata['detail_perusahaan']['kantor'] ?? 'PERUSAHAAN'),0,1,'C');

    //         // // Alamat
    //         // $alamatFull = $getdata['detail_perusahaan']['alamat'] ?? '';
    //         // $alamat = $alamatFull;
    //         // $telp   = '';
    //         // if (stripos($alamatFull, 'telp') !== false) {
    //         //     preg_match('/^(.*?)(telp\.?\s*.*)$/i', $alamatFull, $match);
    //         //     $alamat = trim($match[1] ?? '');
    //         //     $telp   = trim($match[2] ?? '');
    //         // }
    //         // $textAlamat = $alamat . "\n" . $telp;

    //         // Alamat
    //         $alamatFull = $getdata['detail_perusahaan']['alamat'] ?? '';

    //         $alamat = $alamatFull;
    //         $kontak = '';

    //         if (preg_match('/^(.*?)(telp|tel|hp|wa|phone)\.?\s*:?\s*(.*)$/i', $alamatFull, $match)) {
    //             $alamat = trim($match[1] ?? '');
    //             $kontak = trim(($match[2] ?? '').' '.$match[3] ?? '');
    //         }

    //         $textAlamat = trim($alamat);
    //         if(!empty($kontak)){
    //             $textAlamat .= "\n".$kontak;
    //         }

    //         // Alamat
    //         $this->SetFont('Arial','',7);
    //         $this->MultiCell(0,3,$textAlamat ?? '',0,'C');

    //         $this->Ln(1);

    //         // Title
    //         $this->SetFont('Arial','B',9);
    //         $this->Cell(0,4,'SALES ORDER',0,1,'C');

    //         $this->Ln(2);
    //         $this->Line(5,$this->GetY(),75,$this->GetY());
    //         $this->Ln(2);
    //     }

    //     function Footer()
    //     {
    //         $this->SetY(-15);

    //         $this->Line(5,$this->GetY(),75,$this->GetY());

    //         $this->Ln(2);

    //         $this->SetFont('Arial','',8);
    //         $this->Cell(0,5,'Terima kasih telah berbelanja!',0,1,'C');
    //     }

    //     function AddContent()
    //     {
    //         $getdata = $this->data;

    //         $viewdata = $getdata['detail'] ?? [];
    //         $detail_customer = $getdata['detail_customer'] ?? [];

    //         // =========================
    //         // INFO TRANSAKSI
    //         // =========================
    //         $this->SetFont('Arial','',8);

    //         $this->Cell(35,4,'Customer : '.($detail_customer['nama'] ?? '-'),0,0,'L');
    //         $this->Cell(35,4,$viewdata['jenis_penjualan'] ?? '',0,1,'R');

    //         $this->Cell(35,4,'No : '.($viewdata['nomor'] ?? ''),0,0,'L');
    //         $this->Cell(35,4,Date::parse($viewdata['tanggal'])->format('d/m/Y'),0,1,'R');

    //         $this->Cell(35,4,'Kasir : '.($getdata['user_transaksi']['full_name'] ?? ''),0,0,'L');
    //         $this->Cell(35,4,Date::parse($viewdata['created_at'])->setTimezone('Asia/Jakarta')->format('H:i'),0,1,'R');

    //         $this->Ln(1);

    //         if(!empty($viewdata['ket'])){
    //             $this->MultiCell(0,4,'Note : '.$viewdata['ket'],0,'L');
    //         }

    //         if(!empty($getdata['detail_mekanik'])){
    //             $mekanik = implode(', ', array_column($getdata['detail_mekanik'],'nama'));
    //             $this->MultiCell(0,4,'Mekanik : '.$mekanik,0,'L');
    //         }

    //         $this->Ln(1);

    //         $this->Line(5,$this->GetY(),75,$this->GetY());
    //         $this->Ln(2);

    //         // =========================
    //         // HEADER PRODUK
    //         // =========================
    //         $this->SetFont('Arial','B',8);

    //         $this->Cell(5,4,'No',0,0);
    //         $this->Cell(33,4,'Item',0,0);
    //         $this->Cell(8,4,'Qty',0,0,'R');
    //         $this->Cell(10,4,'Harga',0,0,'R');
    //         $this->Cell(14,4,'Total',0,1,'R');

    //         $this->Line(5,$this->GetY(),75,$this->GetY());
    //         $this->Ln(1);

    //         // =========================
    //         // LIST PRODUK
    //         // =========================
    //         // $no = 1;

    //         // foreach ($getdata['list_produk'] as $item){

    //         //     $kode = $item['kode_barang'];

    //         //     $nama = $getdata['detail_produk'][$kode]['nama'] ?? 'Produk';
    //         //     $satuan = $getdata['satuan_barang_produk'][$kode]['nama'] ?? '';

    //         //     $this->SetFont('Arial','',8);

    //         //     // nomor
    //         //     $this->Cell(5,4,$no,0,0);

    //         //     // nama produk (wrap otomatis)
    //         //     $x = $this->GetX();
    //         //     $y = $this->GetY();

    //         //     $this->MultiCell(33,4,$nama,0,'L');

    //         //     $line = ($this->GetY() - $y) / 4;

    //         //     $this->SetXY($x+33,$y);

    //         //     $this->Cell(8,4*$line,number_format($item['jumlah_jual'],0),0,0,'R');
    //         //     $this->Cell(10,4*$line,number_format($item['harga'],0),0,0,'R');
    //         //     $this->Cell(14,4*$line,number_format($item['total_harga'],0),0,1,'R');

    //         //     $no++;
    //         // }

    //         $no = 1;

    //         foreach ($getdata['list_produk'] as $item){

    //             $kode = $item['kode_barang'];

    //             $nama = $getdata['detail_produk'][$kode]['nama'] ?? 'Produk';

    //             $qty = $item['jumlah_jual'] ?? 0;
    //             $harga = $item['harga'] ?? 0;
    //             $disc1 = $item['diskon_persen'] ?? 0;
    //             $disc2 = $item['diskon_persen2'] ?? 0;
    //             $total = $item['total_harga'] ?? 0;

    //             // =====================
    //             // NAMA PRODUK
    //             // =====================
    //             $this->SetFont('Arial','B',8);

    //             $this->MultiCell(70,4,$no.'. '.$nama,0,'L');

    //             // =====================
    //             // LOGIKA DISKON
    //             // =====================
    //             $discText = '';

    //             if($disc1 > 0){
    //                 $discText .= $disc1.'%';
    //             }

    //             if($disc2 > 0){
    //                 if($discText != ''){
    //                     $discText .= '+';
    //                 }
    //                 $discText .= $disc2.'%';
    //             }

    //             // jika dua2nya nol tampilkan disc1
    //             if($disc1 == 0 && $disc2 == 0){
    //                 $discText = '0%';
    //             }

    //             // =====================
    //             // DETAIL PRODUK
    //             // =====================
    //             $this->SetFont('Arial','',8);

    //             $this->Cell(20,4,number_format($qty,0),0,0,'L');
    //             $this->Cell(20,4,number_format($harga,0),0,0,'R');
    //             $this->Cell(10,4,$discText,0,0,'R');
    //             $this->Cell(20,4,number_format($total,0),0,1,'R');

    //             $this->Ln(1);

    //             $no++;
    //         }

    //         // =========================
    //         // TOTAL
    //         // =========================
    //         $detail = $getdata['detail'] ?? [];

    //         $this->Ln(2);
    //         $this->Line(5,$this->GetY(),75,$this->GetY());
    //         $this->Ln(2);

    //         $this->SetFont('Arial','B',9);

    //         $this->Cell(40,5,'Total',0,0,'L');
    //         $this->Cell(30,5,number_format($detail['total'] ?? 0,0,',','.'),0,1,'R');

    //         $this->Cell(40,5,'Discount',0,0,'L');
    //         $this->Cell(30,5,number_format($detail['diskon_harga'] ?? 0,0,',','.'),0,1,'R');

    //         $this->Cell(40,5,'Grand Total',0,0,'L');
    //         $this->Cell(30,5,number_format($detail['grand_total'] ?? 0,0,',','.'),0,1,'R');
    //     }
    // }

    // $productCount = count($getdata['list_produk'] ?? []);
    // // tinggi estimasi
    // $headerHeight = 60;     // 55
    // $footerHeight = 20;
    // $productHeight = 20;    // 10
    // $paperHeight = $headerHeight + $footerHeight + ($productCount * $productHeight);

    // // minimal tinggi
    // if($paperHeight < 120){
    //     $paperHeight = 120;
    // }

    // $pdf = new PDF($getdata, $paperHeight);
    // $pdf->AliasNbPages();
    // $pdf->AddPage();
    // $pdf->AddContent();
    // $pdf->Output('I','Print-'.$getdata['detail']['nomor'].'.pdf');



// class PDF extends MultiCellTable
// {
//     protected $data;

//     function __construct($data, $height)
//     {
//         parent::__construct('P','mm',[80,$height]);
//         $this->SetMargins(5,5,5);
//         $this->SetAutoPageBreak(false);
//         $this->data = $data;
//     }

//     function Header()
//     {
//         $getdata = $this->data;

//         // Logo
//         if(empty($getdata['detail_perusahaan']['foto'])){
//             $this->Image('../themes/admin/AdminOne/image/public/icon.png',32,5,16);
//         }else{
//             $this->Image('../themes/admin/AdminOne/image/public/'.$getdata['detail_perusahaan']['foto'],32,5,16);
//         }

//         $this->Ln(14);

//         // Nama perusahaan
//         $this->SetFont('Arial','B',10);
//         $this->Cell(0,4,strtoupper($getdata['detail_perusahaan']['kantor'] ?? 'PERUSAHAAN'),0,1,'C');

//         // Alamat
//         $alamatFull = $getdata['detail_perusahaan']['alamat'] ?? '';
//         $alamat = $alamatFull;
//         $kontak = '';

//         if (preg_match('/^(.*?)(telp|tel|hp|wa|phone)\.?\s*:?\s*(.*)$/i', $alamatFull, $match)) {
//             $alamat = trim($match[1] ?? '');
//             $kontak = trim(($match[2] ?? '').' '.$match[3] ?? '');
//         }

//         $textAlamat = trim($alamat);
//         if(!empty($kontak)){
//             $textAlamat .= "\n".$kontak;
//         }

//         $this->SetFont('Arial','',7);
//         $this->MultiCell(0,3,$textAlamat,0,'C');

//         $this->Ln(1);

//         $this->SetFont('Arial','B',9);
//         $this->Cell(0,4,'SALES ORDER',0,1,'C');

//         $this->Ln(2);
//         $this->Line(5,$this->GetY(),75,$this->GetY());
//         $this->Ln(2);
//     }

//     function Footer()
//     {
//         $this->SetY(-15);

//         $this->Line(5,$this->GetY(),75,$this->GetY());

//         $this->Ln(2);

//         $this->SetFont('Arial','',8);
//         $this->Cell(0,5,'Terima kasih telah berbelanja!',0,1,'C');
//     }

//     // ======================================
//     // PRINT ITEM PRODUK
//     // ======================================
//     function printItem($no,$nama,$qty,$harga,$disc1,$disc2,$total)
//     {
//         // Nama produk
//         $this->SetFont('Arial','B',8);
//         $this->MultiCell(70,4,$no.'. '.$nama,0,'L');

//         // Format diskon
//         $discText = '';

//         if($disc1 > 0){
//             $discText .= $disc1.'%';
//         }

//         if($disc2 > 0){
//             if($discText!=''){
//                 $discText .= '+';
//             }
//             $discText .= $disc2.'%';
//         }

//         if($disc1 == 0 && $disc2 == 0){
//             $discText = '0%';
//         }

//         // Detail item
//         $this->SetFont('Arial','',8);

//         $this->Cell(15,4,'Qty',0,0);
//         $this->Cell(15,4,number_format($qty,0),0,0,'R');

//         $this->Cell(15,4,'Harga',0,0);
//         $this->Cell(25,4,number_format($harga,0),0,1,'R');

//         $this->Cell(15,4,'Disc',0,0);
//         $this->Cell(15,4,$discText,0,0,'R');

//         $this->Cell(15,4,'Total',0,0);
//         $this->Cell(25,4,number_format($total,0),0,1,'R');

//         $this->Ln(1);

//         // garis antar item
//         $this->Line(5,$this->GetY(),75,$this->GetY());

//         $this->Ln(2);
//     }

//     function AddContent()
//     {
//         $getdata = $this->data;

//         $viewdata = $getdata['detail'] ?? [];
//         $detail_customer = $getdata['detail_customer'] ?? [];

//         // =====================
//         // INFO TRANSAKSI
//         // =====================
//         $this->SetFont('Arial','',8);

//         $this->Cell(35,4,'Customer : '.($detail_customer['nama'] ?? '-'),0,0,'L');
//         $this->Cell(35,4,$viewdata['jenis_penjualan'] ?? '',0,1,'R');

//         $this->Cell(35,4,'No : '.($viewdata['nomor'] ?? ''),0,0,'L');
//         $this->Cell(35,4,Date::parse($viewdata['tanggal'])->format('d/m/Y'),0,1,'R');

//         $this->Cell(35,4,'Kasir : '.($getdata['user_transaksi']['full_name'] ?? ''),0,0,'L');
//         $this->Cell(35,4,Date::parse($viewdata['created_at'])->setTimezone('Asia/Jakarta')->format('H:i'),0,1,'R');

//         $this->Ln(1);

//         if(!empty($viewdata['ket'])){
//             $this->MultiCell(0,4,'Note : '.$viewdata['ket'],0,'L');
//         }

//         if(!empty($getdata['detail_mekanik'])){
//             $mekanik = implode(', ', array_column($getdata['detail_mekanik'],'nama'));
//             $this->MultiCell(0,4,'Mekanik : '.$mekanik,0,'L');
//         }

//         $this->Ln(2);
//         $this->Line(5,$this->GetY(),75,$this->GetY());
//         $this->Ln(2);

//         // =====================
//         // LIST PRODUK
//         // =====================
//         $no = 1;

//         foreach ($getdata['list_produk'] as $item){

//             $kode = $item['kode_barang'];

//             $nama = $getdata['detail_produk'][$kode]['nama'] ?? 'Produk';

//             $this->printItem(
//                 $no,
//                 $nama,
//                 $item['jumlah_jual'] ?? 0,
//                 $item['harga'] ?? 0,
//                 $item['diskon_persen'] ?? 0,
//                 $item['diskon_persen2'] ?? 0,
//                 $item['total_harga'] ?? 0
//             );

//             $no++;
//         }

//         // =====================
//         // TOTAL
//         // =====================
//         $detail = $getdata['detail'] ?? [];

//         $this->Ln(2);
//         $this->Line(5,$this->GetY(),75,$this->GetY());
//         $this->Ln(2);

//         $this->SetFont('Arial','B',9);

//         $this->Cell(40,5,'Total',0,0,'L');
//         $this->Cell(30,5,number_format($detail['total'] ?? 0,0,',','.'),0,1,'R');

//         $this->Cell(40,5,'Discount',0,0,'L');
//         $this->Cell(30,5,number_format($detail['diskon_harga'] ?? 0,0,',','.'),0,1,'R');

//         $this->Cell(40,5,'Grand Total',0,0,'L');
//         $this->Cell(30,5,number_format($detail['grand_total'] ?? 0,0,',','.'),0,1,'R');
//     }
// }


// // =====================
// // AUTO HEIGHT KERTAS
// // =====================
// $productCount = count($getdata['list_produk'] ?? []);

// $headerHeight = 65;
// $footerHeight = 25;
// $productHeight = 22;

// $paperHeight = $headerHeight + $footerHeight + ($productCount * $productHeight);

// if($paperHeight < 120){
//     $paperHeight = 120;
// }

// $pdf = new PDF($getdata,$paperHeight);

// $pdf->AliasNbPages();
// $pdf->AddPage();
// $pdf->AddContent();

// $pdf->Output('I','Print-'.$getdata['detail']['nomor'].'.pdf');


class PDF extends MultiCellTable
{
    protected $data;

    function __construct($data,$height)
    {
        parent::__construct('P','mm',[80,$height]);
        $this->SetMargins(5,5,5);
        $this->SetAutoPageBreak(false);
        $this->data = $data;
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];

        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;

        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;

        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);

        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;

        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;

        while($i<$nb)
        {
            $c=$s[$i];

            if($c=="\n"){
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }

            if($c==' ')
                $sep=$i;

            $l+=$cw[$c];

            if($l>$wmax){
                if($sep==-1){
                    if($i==$j)
                        $i++;
                }else{
                    $i=$sep+1;
                }

                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;

            }else{
                $i++;
            }
        }

        return $nl;
    }

    function Header()
    {
        $getdata = $this->data;

        // Logo
        if(empty($getdata['detail_perusahaan']['foto'])){
            $this->Image('../themes/admin/AdminOne/image/public/icon.png',32,5,16);
        }else{
            $this->Image('../themes/admin/AdminOne/image/public/'.$getdata['detail_perusahaan']['foto'],32,5,16);
        }

        $this->Ln(14);

        // Nama Perusahaan
        $this->SetFont('Arial','B',10);
        $this->Cell(0,4,strtoupper($getdata['detail_perusahaan']['kantor'] ?? 'PERUSAHAAN'),0,1,'C');

        // Alamat + kontak
        $alamatFull = $getdata['detail_perusahaan']['alamat'] ?? '';
        $alamat = $alamatFull;
        $kontak = '';

        if (preg_match('/^(.*?)(telp|tel|hp|wa|phone)\.?\s*:?\s*(.*)$/i', $alamatFull, $match)) {
            $alamat = trim($match[1] ?? '');
            $kontak = trim(($match[2] ?? '').' '.$match[3] ?? '');
        }

        $textAlamat = trim($alamat);
        if(!empty($kontak)){
            $textAlamat .= "\n".$kontak;
        }

        $this->SetFont('Arial','',7);
        $this->MultiCell(0,3,$textAlamat,0,'C');

        $this->Ln(1);

        $this->SetFont('Arial','B',9);
        $this->Cell(0,4,'SALES ORDER',0,1,'C');

        $this->Ln(2);
        $this->Line(5,$this->GetY(),75,$this->GetY());
        $this->Ln(2);
    }

    function Footer()
    {
        // $this->SetY(-15);
        // $this->Line(5,$this->GetY(),75,$this->GetY());
        // $this->Ln(2);
        // $this->SetFont('Arial','',8);
        // $this->Cell(0,5,'Terima kasih telah berbelanja!',0,1,'C');
    }

    function printItem($no,$nama,$qty,$satuan,$harga,$disc1,$disc2,$total)
    {
        // =====================
        // FORMAT DISKON
        // =====================
        $discText = '';

        if($disc1 > 0){
            $discText .= number_format($disc1,2);
        }

        if($disc2 > 0){
            if($discText!=''){
                $discText .= '+';
            }
            $discText .= number_format($disc2,2);
        }

        if($discText==''){
            $discText='0';
        }

        $xStart = 3;

        // =====================
        // NAMA PRODUK
        // =====================
        $this->SetFont('Arial','B',8);

        $yStart = $this->GetY();

        // nomor
        $this->SetX($xStart);
        $this->Cell(5,4,$no.'.',0,0,'L');

        // nama produk (wrap rapi)
        $this->SetXY($xStart+5,$yStart);
        $this->MultiCell(62,4,$nama,0,'L');

        // =====================
        // DETAIL ITEM
        // =====================
        $this->SetFont('Arial','',8);

        $this->SetX($xStart+5);

        $this->Cell(12,4,number_format($qty,2),0,0,'L');
        $this->Cell(10,4,$satuan,0,0,'L');
        $this->Cell(25,4,number_format($harga,2,',','.'),0,0,'R');
        $this->Cell(20,4,$discText,0,1,'R');

        // =====================
        // TOTAL PER ITEM
        // =====================
        $this->SetFont('Arial','B',8);

        $this->SetX($xStart+5);

        $this->Cell(30,4,'Total',0,0,'L');
        $this->Cell(37,4,number_format($total,2,',','.'),0,1,'R');

        $this->Ln(1);
    }

    function AddContent()
    {
        $getdata = $this->data;
        $viewdata = $getdata['detail'] ?? [];
        $detail_customer = $getdata['detail_customer'] ?? [];

        // INFO TRANSAKSI
        $this->SetFont('Arial','',8);
        
        $this->Cell(35,4,'Customer : '.($detail_customer['nama'] ?? '-'),0,0,'L');
        $this->Cell(35,4,$viewdata['jenis_penjualan'] ?? '',0,1,'R');

        $this->Cell(35,4,'No : '.($viewdata['nomor'] ?? ''),0,0,'L');
        $this->Cell(35,4,Date::parse($viewdata['tanggal'])->format('d F Y') ?? '',0,1,'R');

        $this->Cell(35,4,'Kasir : '.($getdata['user_transaksi']['full_name'] ?? ''),0,0,'L');
        $this->Cell(35,4,Date::parse($viewdata['created_at'])->setTimezone('Asia/Jakarta')->format('H:i:s'),0,1,'R');

        $this->Ln(1);

        if(!empty($viewdata['ket'])){
            $this->MultiCell(0,4,'Note : '.$viewdata['ket'],0,'L');
        }

        if(!empty($getdata['detail_mekanik'])){
            $mekanik = implode(', ', array_column($getdata['detail_mekanik'],'nama'));
            $this->MultiCell(0,4,'Mekanik : '.$mekanik,0,'L');
        }

        $this->Ln(2);
        $this->Line(5,$this->GetY(),75,$this->GetY());
        $this->Ln(2);

        // HEADER KOLOM PRODUK
        $this->SetFont('Arial','B',8);

        // posisi awal header
        $yHeader = $this->GetY();

        // sejajarkan dengan item
        $this->SetX(10);

        $this->Cell(10,4,'Qty',0,0,'L');
        $this->Cell(10,4,'Sat',0,0,'L');
        $this->Cell(25,4,'Harga',0,0,'R');
        $this->Cell(20,4,'Disc %',0,1,'R');
        // $this->Cell(20,4,'Total',0,1,'R');

        // garis tepat di bawah header
        $this->Line(5,$yHeader+5,75,$yHeader+5);

        $this->Ln(3);

        // LIST PRODUK
        $no = 1;
        foreach ($getdata['list_produk'] as $item){
            $kode = $item['kode_barang'];
            $nama = $getdata['detail_produk'][$kode]['nama'] ?? 'Produk';
            $satuan = $getdata['satuan_barang_produk'][$kode]['nama'] ?? '';

            $this->printItem(
                $no,
                $nama,
                $item['jumlah_jual'] ?? 0,
                $satuan,
                $item['harga'] ?? 0,
                $item['diskon_persen'] ?? 0,
                $item['diskon_persen2'] ?? 0,
                $item['total_harga'] ?? 0
            );
            $no++;
        }

        // TOTAL
        $detail = $getdata['detail'] ?? [];

        $this->Ln(2);
        $this->Line(5,$this->GetY(),75,$this->GetY());
        $this->Ln(2);

        $this->SetFont('Arial','B',9);

        $this->Cell(40,5,'Sub Total',0,0,'L');
        $this->Cell(30,5,number_format($detail['total'] ?? 0,2,',','.'),0,1,'R');

        $this->Cell(40,5,'Discount',0,0,'L');
        $this->Cell(30,5,number_format($detail['diskon_harga'] ?? 0,2,',','.'),0,1,'R');

        $this->Cell(40,5,'Grand Total',0,0,'L');
        $this->Cell(30,5,number_format($detail['grand_total'] ?? 0,2,',','.'),0,1,'R');

        $this->Ln(2);
        $this->Line(5,$this->GetY(),75,$this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial','',8);
        $this->Cell(0,5,'Terima kasih telah berbelanja!',0,1,'C');
    }
}

// FUNCTION HITUNG JUMLAH BARIS
function countLines($text,$maxChar = 28)
{
    return ceil(strlen($text) / $maxChar);
}

// AUTO HEIGHT KERTAS
$headerHeight = 65;
$footerHeight = 35;
$productHeight = 0;

foreach($getdata['list_produk'] as $item){
    $kode = $item['kode_barang'];
    $nama = $getdata['detail_produk'][$kode]['nama'] ?? '';

    // hitung baris nama produk
    $line = countLines($nama);
    // tinggi nama produk
    $namaHeight = $line * 4;
    // tinggi detail + total
    $detailHeight = 9;
    $productHeight += ($namaHeight + $detailHeight);
}

$paperHeight = $headerHeight + $footerHeight + $productHeight;

if($paperHeight < 120){
    $paperHeight = 120;
}

// GENERATE PDF
$pdf = new PDF($getdata,$paperHeight);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddContent();

$pdf->Output('I','Print-'.$getdata['detail']['nomor'].'.pdf');