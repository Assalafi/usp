<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';

class PDFWithWatermark extends exFPDF
{
    protected $angle = 0;

    function Header()
    {
        // Add watermark on every page
        $this->SetFont('Arial', 'B', 80);
        $this->SetTextColor(255, 192, 203); // Pink color for the watermark

        // Calculate the center position
        $textWidth = $this->GetStringWidth('NOT APPROVED');
        $x = ($this->GetPageWidth() - $textWidth) / 2;
        $y = ($this->GetPageHeight() / 2);

        // Rotate and print the watermark
        $this->RotateText($x, $y, 'NOT APPROVED', 45);

        // Reset the rotation back to 0
        $this->Rotate(0);
    }

    function RotateText($x, $y, $txt, $angle)
    {
        // Rotate around a point
        $this->Rotate($angle, $x, $y);
        $this->Text(-20, 210, $txt);
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.2F %.2F %.2F %.2F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}
$a = 0;
$b = 0;
$c = 0;
$d = 0;
$e = 0;
$f = 0;
$name = '';
$course = $code;
$session = $session;
$getCourses = DB::table('course')->where(['code' => $course])->get();
foreach ($getCourses as $row) {
    $semester = $row->semester;
    $title = $row->title;
    $unit = $row->unit;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
    $program = DB::table('program')->where(['code' => $row->program])->value('title');
}

$pdf = new PDFWithWatermark();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

//$pdf->Rotate(0); // Reset rotation

$table = new easyTable($pdf, '{11, 40, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
$dept = DB::table('results')->select('department')->groupBy('department')->where(['code' => $course, 'session' => $session])->get();
foreach ($dept as $dp) {
    $data = DB::table('results')->where(['code' => $course, 'session' => $session, 'department' => $dp->department])->get();
    //echo $dp -> department;
    //die;
    $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
    $table->printRow();
    $table->easyCell('University of Maiduguri', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
    $table->printRow();
    $table->easyCell('(' . ucwords(strtolower($faculty)) . ')', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
    $table->printRow();
    $table->easyCell(ucwords(strtolower($program)), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
    $table->printRow();
    $pdf->Ln(5);
    $table->easyCell(ucwords(strtolower($semester)) . ' Semester End-of-Course Examination Results for ' . $session . ' Acadamic Session', 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Course Code/Title    ' . $course . ': ' . ucwords(strtolower($title)) . '  Units: ' . $unit, 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Department of ' . ucwords(strtolower(DB::table('department')->where('code', $dp->department)->value('title'))) . ' ', 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $pdf->Ln(1);
    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
    $table->easyCell("S/No.", 'rowspan:2');
    $table->easyCell("ID. No.", 'rowspan:2');
    $table->easyCell("C.A Marks (30%)", 'rowspan:2');
    $table->easyCell("Exam. Marks (70%)", 'rowspan:2');
    $table->easyCell("Total Marks (100%)", 'rowspan:2');
    $table->easyCell("Grades", 'colspan:6', 'align:C; valign:M');
    $table->easyCell("GP", 'rowspan:2');
    $table->easyCell("U.GP", 'rowspan:2');
    $table->easyCell("Remarks.", 'rowspan:2');
    $table->printRow();

    $table->rowStyle('align:{CCCCCC};');
    $table->easyCell("A");
    $table->easyCell("B");
    $table->easyCell("C");
    $table->easyCell("D");
    $table->easyCell("E");
    $table->easyCell("F");
    $table->printRow();
    $x = 0;
    foreach ($data as $row) {
        $x = $x + 1;
        $table->rowStyle('align:{CCCCCCCCCCCCCC};');
        $table->easyCell($x);
        $table->easyCell($row->username);
        if ($row->ca < 10) {
            $table->easyCell('0' . $row->ca);
        } else {
            $table->easyCell($row->ca);
        }
        if ($row->exam < 10) {
            $table->easyCell('0' . $row->exam);
        } else {
            $table->easyCell($row->exam);
        }
        if ($row->total < 10) {
            $table->easyCell('0' . $row->total);
        } else {
            $table->easyCell($row->total);
        }
        if ($row->grade == 'A') {
            $table->easyCell("A");
            $a++;
        } else {
            $table->easyCell("");
        }
        if ($row->grade == 'B') {
            $table->easyCell("B");
            $b++;
        } else {
            $table->easyCell("");
        }
        if ($row->grade == 'C') {
            $table->easyCell("C");
            $c++;
        } else {
            $table->easyCell("");
        }
        if ($row->grade == 'D') {
            $table->easyCell("D");
            $d++;
        } else {
            $table->easyCell("");
        }
        if ($row->grade == 'E') {
            $table->easyCell("E");
            $e++;
        } else {
            $table->easyCell("");
        }
        if ($row->grade == 'F') {
            $table->easyCell("F");
            $f++;
        } else {
            $table->easyCell("");
        }
        $table->easyCell($row->point . '.0');
        if ($row->ugp < 10) {
            $table->easyCell('0' . $row->ugp);
        } else {
            $table->easyCell($row->ugp);
        }
        $table->easyCell($row->remark);
        $table->printRow();
    }


    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    $table->easyCell("Total", 'colspan:5');
    if ($a < 10) {
        $table->easyCell('0' . $a);
    } else {
        $table->easyCell($a);
    }
    if ($b < 10) {
        $table->easyCell('0' . $b);
    } else {
        $table->easyCell($b);
    }
    if ($c < 10) {
        $table->easyCell('0' . $c);
    } else {
        $table->easyCell($c);
    }
    if ($d < 10) {
        $table->easyCell('0' . $d);
    } else {
        $table->easyCell($d);
    }
    if ($e < 10) {
        $table->easyCell('0' . $e);
    } else {
        $table->easyCell($e);
    }
    if ($f < 10) {
        $table->easyCell('0' . $f);
    } else {
        $table->easyCell($f);
    }
    if (($a + $b + $c + $d + $e + $f) < 10) {
        $table->easyCell('0' . $a + $b + $c + $d + $e + $f, 'colspan:3');
    } else {
        $table->easyCell($a + $b + $c + $d + $e + $f, 'colspan:3');
    }
    $table->printRow();
    $a = 0;
    $b = 0;
    $c = 0;
    $d = 0;
    $e = 0;
    $f = 0;
    $pdf->AddPage();
}
$table->endTable(4);

//-----------------------------------------
//$pdf->Output();
$name = $course . ' ' . $session;
//$pdf->Output('D',$name.'.pdf',true);
$pdf->Output();
die;
