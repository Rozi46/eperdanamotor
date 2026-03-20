<?php
require '../fpdf/fpdf.php';

class MultiCellTable extends FPDF
{
    var $widths;
    var $aligns;
    var $Heights;

    // function SetWidths($w)
    // {
    //     $this->widths = $w;
    // }

    function SetWidths($w)
    {
        foreach($w as $k=>$v){
            $w[$k] = (float)$v;
        }

        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    // function SetHeights($h)
    // {
    //     $this->Heights = $h;
    // }

    function SetHeights($h)
    {
        foreach($h as $k=>$v){
            $h[$k] = (float)$v;
        }

        $this->heights = $h;
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    // function Row($data)
    // {
    //     $nb = 0;
    //     for ($i = 0; $i < count($data); $i++) {
    //         $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];
    //         $nb = max($nb, $this->NbLines($this->widths[$i], $text));
    //     }

    //     $h = isset($this->heights[0]) ? $this->heights[0] : 5 * $nb;

    //     $this->CheckPageBreak($h);

    //     for ($i = 0; $i < count($data); $i++) {
    //         $w = $this->widths[$i];

    //         $a = 'L';
    //         if (is_array($data[$i]) && isset($data[$i][1])) {
    //             $a = $data[$i][1];
    //         } elseif (isset($this->aligns[$i])) {
    //             $a = $this->aligns[$i];
    //         }

    //         $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];

    //         $x = $this->GetX();
    //         $y = $this->GetY();
    //         $this->Rect($x, $y, $w, $h);
    //         $this->MultiCell($w, 5, $text, 0, $a);
    //         $this->SetXY($x + $w, $y);
    //     }

    //     $this->Ln($h);
    // }

    function Row($data)
{
    $nb = 0;

    for ($i = 0; $i < count($data); $i++) {
        $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];
        $nb = max($nb, $this->NbLines($this->widths[$i], $text));
    }

    // Pastikan height selalu numeric
    if (isset($this->heights[0])) {
        $h = (float)$this->heights[0];
    } else {
        $h = (float)(5 * $nb);
    }

    $this->CheckPageBreak($h);

    for ($i = 0; $i < count($data); $i++) {

        $w = (float)$this->widths[$i];

        $a = 'L';
        if (is_array($data[$i]) && isset($data[$i][1])) {
            $a = $data[$i][1];
        } elseif (isset($this->aligns[$i])) {
            $a = $this->aligns[$i];
        }

        $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];

        $x = $this->GetX();
        $y = $this->GetY();

        $this->Rect($x, $y, $w, $h);

        $this->MultiCell($w, 5, $text, 0, $a);

        $this->SetXY($x + $w, $y);
    }

    $this->Ln($h);
}

    function getDynamicHeight($text, $width)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($width == 0)
            $width = $this->w - $this->rMargin - $this->x;
        $wmax = ($width - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $text);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return 5 * $nl; // 5 = tinggi line per baris
    }

    function RowHeader($data, $headerHeight = 10)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];
            $nb = max($nb, $this->NbLines($this->widths[$i], $text));
        }
        
        // Use custom header height instead of calculated height
        $h = $headerHeight;
        $this->CheckPageBreak($h);
        
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = 'L';
            if (is_array($data[$i]) && isset($data[$i][1])) {
                $a = $data[$i][1];
            } elseif (isset($this->aligns[$i])) {
                $a = $this->aligns[$i];
            }
            
            $text = is_array($data[$i]) ? $data[$i][0] : $data[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Draw cell border
            $this->Rect($x, $y, $w, $h);
            
            // Center text vertically in the taller header cell
            $text_y = $y + ($h - 5) / 2; // Adjust vertical position
            $this->SetXY($x, $text_y);
            $this->MultiCell($w, 5, $text, 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }
}
?>
