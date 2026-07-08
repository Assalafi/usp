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

$departments = DB::table('results')
    ->select('department')
    ->groupBy('department')
    ->where(['code' => $course, 'session' => $session])
    ->get();

foreach ($departments as $dp) {
    // Fetch data for each department
    $data = DB::table('results')
        ->where(['code' => $course, 'session' => $session, 'department' => $dp->department])
        ->get();

    // Fetch the grading system for this department
    $gradingSystem = DB::table('grading_system')
        ->where('ref', 'GENERAL2018CURRENT')
        ->orderBy('min_score', 'desc')
        ->get();
    // Create a new table for each department
    $table = new easyTable($pdf, '{11, 40, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');

    // Header with department info
    $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
    $table->printRow();
    $table->easyCell('University of Maiduguri', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
    $table->printRow();
    $table->easyCell('(' . ucwords(strtolower($faculty)) . ')', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
    $table->printRow();
    $table->easyCell(ucwords(strtolower($program)), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
    $table->printRow();
    $pdf->Ln(5);
    $table->easyCell(ucwords(strtolower($semester)) . ' Semester End-of-Course Examination Results for ' . $session . ' Academic Session', 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Course Code/Title    ' . $course . ': ' . ucwords(strtolower($title)) . '  Units: ' . $unit, 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Department of ' . ucwords(strtolower(DB::table('department')->where('code', $dp->department)->value('title'))) . ' ', 'colspan:14; font-size:12; align:C;border:0;');
    $table->printRow();
    $pdf->Ln(1);
    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    $table->easyCell("S/No.", 'rowspan:2');
    $table->easyCell("ID. No.", 'rowspan:2');
    $table->easyCell("C.A Marks (30%)", 'rowspan:2');
    $table->easyCell("Exam. Marks (70%)", 'rowspan:2');
    $table->easyCell("Total Marks (100%)", 'rowspan:2');

    // Dynamically set grade columns based on the grading system for the department
    $numGrades = count($gradingSystem);
    $table->easyCell("Grades", 'colspan:' . $numGrades, 'align:C; valign:M');
    $table->easyCell("GP", 'rowspan:2');
    $table->easyCell("U.GP", 'rowspan:2');
    $table->easyCell("Remarks.", 'rowspan:2');
    $table->printRow();
    //dd($gradingSystem);
    // Dynamically set the grade headers
    $table->rowStyle('align:{CCCCCC};');
    foreach ($gradingSystem as $gs) {
        $table->easyCell($gs->grade);
    }
    $table->printRow();

    $x = 0;
    $gradeCounts = array_fill(0, $numGrades, 0); // Initialize grade counts

    foreach ($data as $row) {
        //echo $row->username .'<br>';
        $x++;
        $table->rowStyle('align:{CCCCCCCCCCCCCC};');
        $table->easyCell($x);
        $table->easyCell($row->username);
        $table->easyCell(str_pad($row->ca, 2, '0', STR_PAD_LEFT));
        $table->easyCell(str_pad($row->exam, 2, '0', STR_PAD_LEFT));
        $table->easyCell(str_pad($row->total, 2, '0', STR_PAD_LEFT));

        $grade = "";
        foreach ($gradingSystem as $key => $gs) {
            if ($row->total >= $gs->min_score && $row->total <= $gs->max_score) {
                $grade = $gs->grade;
                $gradeCounts[$key]++;
                break;
            }
        }

        $table->easyCell($grade);
        foreach ($gradeCounts as $count) {
            $table->easyCell($count > 0 ? str_pad($count, 2, '0', STR_PAD_LEFT) : '00');
        }

        $table->easyCell($row->point . '.0');
        $table->easyCell(str_pad($row->ugp, 2, '0', STR_PAD_LEFT));
        $table->easyCell($row->remark);
        $table->printRow();
    }

    // Totals row for each grade
    $table->rowStyle('align:{CCCCCCCCCCCCCC};');
    $table->easyCell("Total", 'colspan:5');
    foreach ($gradeCounts as $count) {
        $table->easyCell(str_pad($count, 2, '0', STR_PAD_LEFT));
    }
    $table->easyCell(str_pad(array_sum($gradeCounts), 2, '0', STR_PAD_LEFT), 'colspan:3');
    $table->printRow();

    //$pdf->AddPage();
}
$table->endTable(4);
$name = $course . ' ' . $session;
$pdf->Output();
die;
