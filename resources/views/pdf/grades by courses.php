<?php

use Illuminate\Support\Facades\DB;

include ('pdf_mc_table.php');
// include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';

function codeMod($input)
{
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}

function firstUpper($input)
{
    $excludeWords = ['or', 'and', 'of', 'the', 'in', 'on', 'at', 'by', 'for', 'to'];
    $input = ucwords(strtolower($input));
    $words = explode(' ', $input);
    foreach ($words as &$word) {
        if (in_array(strtolower($word), $excludeWords)) {
            $word = strtolower($word);
        }
    }
    return implode(' ', $words);
}

class PDFWithWatermark extends exFPDF
{
    protected $angle = 0;

    function Header()
    {
        // Add watermark on every page
        $this->SetFont('Arial', 'B', 80);
        $this->SetTextColor(255, 192, 203);  // Pink color for the watermark

        // Calculate the center position
        $textWidth = $this->GetStringWidth('UNIMAID');
        $x = 10;
        $y = 30;

        // Rotate and print the watermark
        $this->RotateText($x, $y, 'UNIMAID', 45);

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
$bb = 0;
$c = 0;
$cc = 0;
$d = 0;
$e = 0;
$f = 0;
$name = '';
$course = $code;
$session = $session;
$program = DB::table('program')->where(['department' => $course])->pluck('code');
$res = DB::table('results')->whereIn('program', $program)->where(['semester' => $semester, 'session' => $session])->select('code')->groupBy('code')->pluck('code');
$getCourses = DB::table('department')->where(['code' => $course])->get();
foreach ($getCourses as $row) {
    $title = $row->title;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
}

$pdf = new PDFWithWatermark();
$pdf->AddPage('O');
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

// $pdf->Rotate(0); // Reset rotation

$table = new easyTable($pdf, '{11, 25, 100, 15, 25, 10, 10, 10, 10, 10, 10, 10, 10, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.7;');
$data = DB::table('course')->whereIn('code', $res)->where(['department' => $code])->orderBy('level', 'ASC')->orderBy('code', 'ASC')->get();
$final = DB::table('course')->whereIn('code', $res)->where(['department' => $code])->orderBy('level', 'DESC')->select('level')->value('level');
// echo $dp -> department;
// die;
$table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
$table->printRow();
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
$table->printRow();
$table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARTMENT OF ' . strtoupper($title) . '', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
$table->printRow();
$table->easyCell(strtoupper('Distribution of Grades by Courses'), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
$table->printRow();
$pdf->Ln(5);
$table->easyCell(firstUpper($semester) . ' Semester End-of-Course Examination Results for ' . $session . ' Acadamic Session', 'colspan:14; font-size:12; align:C;border:0;');
$table->printRow();
$pdf->Ln(1);
$table->rowStyle('align:{CCCCCCCCCCCCCC}; font-style:B;');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell('S/No.', 'rowspan:2');
$table->easyCell('Course Code', 'rowspan:2');
$table->easyCell('Course Title', 'rowspan:2');
$table->easyCell('Unit (s)', 'rowspan:2');
$table->easyCell('Total No. of Candidates', 'rowspan:2');
$table->easyCell('Grades', 'colspan:8', 'align:C; valign:M');
$table->easyCell('Remark', 'rowspan:2');
$table->printRow();

$table->rowStyle('align:{CCCCCC};');
$table->easyCell('A');
$table->easyCell('B');
$table->easyCell('B+');
$table->easyCell('C');
$table->easyCell('C+');
$table->easyCell('D');
$table->easyCell('E');
$table->easyCell('F');
$table->printRow();
$x = 0;
foreach ($data as $row) {
    $x = $x + 1;
    $total = 0;
    $results = DB::table('results')->where(['code' => $row->code, 'session' => $session, 'semester' => $semester])->select('grade')->get();
    $reg = DB::table('student_course_registration')->where(['code' => $row->code, 'session' => $session, 'semester' => $semester])->select('id')->count('id');
    foreach ($results as $rs) {
        if ($rs->grade == 'A') {
            $a++;
        }
        if ($rs->grade == 'B') {
            $b++;
        }
        if ($rs->grade == 'B+') {
            $bb++;
        }
        if ($rs->grade == 'C') {
            $c++;
        }
        if ($rs->grade == 'C+') {
            $cc++;
        }
        if ($rs->grade == 'D') {
            $d++;
        }
        if ($rs->grade == 'E') {
            $e++;
        }
        if ($rs->grade == 'F') {
            $f++;
        }
    }
    $total = $a + $b + $c + $d + $e + $f + $bb + $cc;
    $with = $reg - $total;
    $table->rowStyle('align:{CCLCCCCCCCCCCC};');
    $table->easyCell($x);
    if ($final == $row->level && $final > 300) {
        if (substr($row->code, -2) == '99') {
            $table->easyCell('*' . codeMod($row->code));
        } else {
            $table->easyCell('*' . codeMod($row->code));
        }
    } else {
        $table->easyCell(codeMod($row->code));
    }

    $table->easyCell(firstUpper($row->title), 'font-size:10;');
    $table->easyCell($row->unit);
    if (($total) < 10) {
        $table->easyCell('0' . $total);
    } else {
        $table->easyCell($total);
    }

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
    if ($bb < 10) {
        $table->easyCell('0' . $bb);
    } else {
        $table->easyCell($bb);
    }
    if ($c < 10) {
        $table->easyCell('0' . $c);
    } else {
        $table->easyCell($c);
    }
    if ($cc < 10) {
        $table->easyCell('0' . $cc);
    } else {
        $table->easyCell($cc);
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
    if ($with > 0) {
        if ($with < 10) {
            $table->easyCell('');
        } else {
            $table->easyCell('');
        }
    } else {
        $table->easyCell('');
    }
    $table->printRow();

    $a = 0;
    $b = 0;
    $bb = 0;
    $c = 0;
    $cc = 0;
    $d = 0;
    $e = 0;
    $f = 0;
}
$pdf->Ln(5);
$table->easyCell('* Externally Moderated', 'colspan:14; font-size:10; border:0;');
$table->printRow();
if ($x == 199990) {
    if ($with == $reg) {
    } else {
        $table->easyCell('** Yet to Submit', 'colspan:14; font-size:10; border:0;');
        $table->printRow();
    }
}

$table->endTable(4);

// -----------------------------------------
// $pdf->Output();
$name = $course . ' ' . $session;
// $pdf->Output('D',$name.'.pdf',true);
$pdf->Output();
die;
