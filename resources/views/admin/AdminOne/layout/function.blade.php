<?php
   function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[(int)$nilai];
        } else if ($nilai <20) {
            $temp = penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp =penyebut(intval($nilai/10))." puluh". penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebut(intval($nilai/100)) . " ratus" . penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebut(intval($nilai/1000)) . " ribu" . penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = penyebut(intval($nilai/1000000)) . " juta" . penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = penyebut(intval($nilai/1000000000)) . " milyar" . penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = penyebut(intval($nilai/1000000000000)) . " trilyun" . penyebut(fmod($nilai,1000000000000));
        }     
        return $temp;
    }
    
    function terbilang($nilai) {
        $nilai = str_replace(',', '.', $nilai); 
        $nilai = (float)$nilai; 

        $bilangan_bulat = floor($nilai);
        $desimal = round(($nilai - $bilangan_bulat) * 100);

        if ($nilai < 0) {
            $hasil = "minus" . penyebut(abs($bilangan_bulat));
        } else {
            $hasil = trim(penyebut($bilangan_bulat));
        }

        if ($desimal > 0) {
            $hasil .= " koma" . penyebut($desimal);
        }    		
        return ucfirst($hasil)." rupiah";
    }

  	function daterute($tgl1, $tgl2){
  		list($h,$m) = explode(':',date("H:i",strtotime($tgl1)));
		$tranAwal = mktime($h,$m,'1','1'); 
		list($h,$m) = explode(':',date("H:i",strtotime($tgl2)));
		$tranAkhir = mktime($h,$m,'1','1');
		list($d) = explode(':',date("d",strtotime($tgl1)));
		$hariAwal = mktime($d,'1'); 
		list($d) = explode(':',date("d",strtotime($tgl2)));
		$hariAkhir = mktime($d,'1');
		$hari = $hariAkhir - $hariAwal;
		$allhari = $hari/3600;
		$transit = $tranAkhir - $tranAwal; 
		$det_transit = $transit% 3600;
		if($allhari < 1){
			if(floor($det_transit/60) == 0){
				$wkttransit = floor($transit/3600)." Jam ";
			}else{
				$wkttransit = floor($transit/3600)." Jam ".floor($det_transit/60)." Menit";
			}
		}else{
			$wkttransit = floor($allhari)." Hari ".floor($transit/3600)." Jam ".floor($det_transit/60)." Menit";
		}
		$det_transit = $transit% 3600;
		return  $wkttransit ;
  	}
  	function lastonline($tgl1, $tgl2){
  		$tgl1 = strtotime($tgl1);
  		$tgl2 = strtotime($tgl2);
  		$diff_secs = abs($tgl1 - $tgl2);
  		$base_year = min(date("Y", $tgl1), date("Y", $tgl2));
  		$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
  		return array( "years" => date("Y", $diff) - $base_year, "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1, "months" => date("n", $diff) - 1, "days_total" => floor($diff_secs / (3600 * 24)), "days" => date("j", $diff) - 1, "hours_total" => floor($diff_secs / 3600), "hours" => date("G", $diff), "minutes_total" => floor($diff_secs / 60), "minutes" => (int) date("i", $diff), "seconds_total" => $diff_secs, "seconds" => (int) date("s", $diff) );
  	}

  	function HitungHari($date1,$date2){
  		$date1 = explode("-", $date1);
		$date2 =  explode("-", $date2);
		$date1 =  mktime(0, 0, 0, $date1[1],$date1[2],$date1[0]);
		$date2 =  mktime(0, 0, 0, $date2[1],$date2[2],$date2[0]);
		$interval =($date2 - $date1)/(3600*24);
		return  $interval ;
  	}

  	function angka_format_short( $n, $precision = 1 ) {
  		if ($n < 900) {
			$n_format = number_format($n, $precision);
			$suffix = '';
		} else if ($n < 900000) {
			$n_format = number_format($n / 1000, $precision);
			$suffix = ' Rb';
		} else if ($n < 900000000) {
			$n_format = number_format($n / 1000000, $precision);
			$suffix = ' Jt';
		} else if ($n < 900000000000) {
			$n_format = number_format($n / 1000000000, $precision);
			$suffix = ' M';
		} else {
			$n_format = number_format($n / 1000000000000, $precision);
			$suffix = ' T';
		}
		if ( $precision > 0 ) {
			$dotzero = '.' . str_repeat( '0', $precision );
			$n_format = str_replace( $dotzero, '', $n_format );
		}
		return $n_format . $suffix;
  	}

  	function format_tgl($timestamp = '', $date_format = 'l / j F Y') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}

  	function format_tgl_one($timestamp = '', $date_format = 'j F Y - H:i', $suffix = 'WIB') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
	function format_tgl_two($timestamp = '', $date_format = 'l, j F Y | H:i', $suffix = 'WIB') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
	function format_tgl_tree($timestamp = '', $date_format = 'l , j F Y') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit((string) $timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
	function format_tgl_only($timestamp = '', $date_format = 'j F Y') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit((string) $timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
	function format_tgl_only_v1($timestamp = '', $date_format = 'd F Y') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
	function format_bln_only($timestamp = '', $date_format = 'F') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}

  	function format_date_one($timestamp = '', $date_format = 'H:i', $suffix = 'WIB') {
		if (trim ($timestamp) == '')
		{
				$timestamp = time ();
		}
		elseif (!ctype_digit ($timestamp))
		{
			$timestamp = strtotime ($timestamp);
		}
		$date_format = preg_replace ("/S/", "", $date_format);
		$pattern = array (
			'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
			'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
			'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
			'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
			'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
			'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
			'/April/','/Mei/','/June/','/July/','/August/','/September/',
			'/October/','/November/','/December/',
		);
		$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
			'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
			'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des',
			'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September',
			'Oktober','November','Desember',
		);
		$date = date ($date_format, $timestamp);
		$date = preg_replace ($pattern, $replace, $date);
		$date = "{$date}";
		return $date;
	}
?>
