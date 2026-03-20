<?php 
					$pdf->Cell(40,-75,''.$data_nagari["_nama_nagari"].', '.format_tgl_only_v1(date('d-M-Y')).'',0,0,'C');
					$pdf->Ln();
					$pdf->Cell($calllbtlttd);
					$pdf->Cell(40,85,''.$data_pagawai_ttd["_jabatan_pejabat"].'',0,0,'C');
					$pdf->Ln();
					$pdf->Cell($calllbtlttd);
					$pdf->Cell(40,-30,''.$data_pagawai_ttd["_nama_pejabat"].'',0,0,'C');
					
	function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $indent=0){
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;

		$wFirst = $w-$indent;
		$wOther = $w;

		$wmaxFirst=($wFirst-2*$this->cMargin)*1000/$this->FontSize;
		$wmaxOther=($wOther-2*$this->cMargin)*1000/$this->FontSize;

		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 && $s[$nb-1]=="\n")
			$nb--;
		$b=0;
		if($border)
		{
			if($border==1)
			{
				$border='LTRB';
				$b='LRT';
				$b2='LR';
			}
			else
			{
				$b2='';
				if(is_int(strpos($border,'L')))
					$b2.='L';
				if(is_int(strpos($border,'R')))
					$b2.='R';
				$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
			}
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$ns=0;
		$nl=1;
			$first=true;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				if($this->ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border && $nl==2)
					$b=$b2;
				continue;
			}
			if($c==' ')
			{
				$sep=$i;
				$ls=$l;
				$ns++;
			}
			$l+=$cw[$c];

			if ($first)
			{
				$wmax = $wmaxFirst;
				$w = $wFirst;
			}
			else
			{
				$wmax = $wmaxOther;
				$w = $wOther;
			}

			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
					if($this->ws>0)
					{
						$this->ws=0;
						$this->_out('0 Tw');
					}
					$SaveX = $this->x; 
					if ($first && $indent>0)
					{
						$this->SetX($this->x + $indent);
						$first=false;
					}
					$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
						$this->SetX($SaveX);
				}
				else
				{
					if($align=='J')
					{
						$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
						$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
					}
					$SaveX = $this->x; 
					if ($first && $indent>0)
					{
						$this->SetX($this->x + $indent);
						$first=false;
					}
					$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
						$this->SetX($SaveX);
					$i=$sep+1;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				$ns=0;
				$nl++;
				if($border && $nl==2)
					$b=$b2;
			}
			else
				$i++;
		}
		if($this->ws>0)
		{
			$this->ws=0;
			$this->_out('0 Tw');
		}
		if($border && is_int(strpos($border,'B')))
			$b.='B';
		$this->Cell($w,$h,substr($s,$j,$i),$b,2,$align,$fill);
		$this->x=$this->lMargin;
	}
?>