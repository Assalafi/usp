<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';
function codeMod($input) {
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}
function firstUpper($input) {
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

// Fetch the percentage value from the database (assuming the `per` column holds the exam percentage)
$per = DB::table('results')->where(['code' => $course, 'session' => $session])->value('per');

// Default percentage if `per` is not available (e.g., 70% for exams and 30% for C.A Marks)
if (!$per) {
    $per = 70;  // Default exam percentage
}
$ca_percentage = 100 - $per;  // C.A Marks percentage

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

// Table initialization
$table = new easyTable($pdf, '{12, 39, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
$dept = DB::table('results')->select('department')->groupBy('department')->where(['code' => $course, 'session' => $session])->get();

foreach ($dept as $dp) {
    $data = DB::table('results')->where(['code' => $course, 'session' => $session, 'department' => $dp->department])->get();

    // Table header
    $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
    $table->printRow();
    $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
    $table->printRow();
    $table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
    $table->printRow();
    $table->easyCell('DEPARTMENT OF '.ucwords(strtoupper($program)), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
    $table->printRow();
    $pdf->Ln(5);
    $table->easyCell(ucwords(strtolower($semester)) . ' Semester End-of-Course Examination Results for ' . $session . ' Academic Session', 'colspan:14; font-size:12; font-style:B; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Course Code/Title: ' . codeMod($course) . ': ' . firstUpper($title) . '  Unit (s): ' . $unit, 'colspan:14; font-size:12; font-style:B; align:C;border:0;');
    $table->printRow();
    $table->easyCell('Department of '. ucwords(strtolower(DB::table('department')->where('code', $dp->department)->value('title'))) . ' ', 'colspan:14; font-size:12; font-style:B; align:L;border:0;');
    $table->printRow();
    $pdf->Ln(1);

    // Dynamic column labels based on `per` value
    $table->rowStyle('align:{CCCCCCCCCCCCCC}; font-style:B;');
    $table->easyCell("S/No.", 'rowspan:2');
    $table->easyCell("ID. No.", 'rowspan:2');
    $table->easyCell("C.A Marks ($ca_percentage%)", 'rowspan:2');  // Dynamic C.A Marks
    $table->easyCell("Exam. Marks ($per%)", 'rowspan:2');  // Dynamic Exam Marks
    $table->easyCell("Total Marks (100%)", 'rowspan:2');
    $table->easyCell("Grades", 'colspan:6', 'align:C; valign:M');
    $table->easyCell("GP", 'rowspan:2');
    $table->easyCell("U.GP", 'rowspan:2');
    $table->easyCell("Remarks.", 'rowspan:2');
    $table->printRow();

    // Grades header
    $table->rowStyle('align:{CCCCCC}; font-style:B;');
    $table->easyCell("A");
    $table->easyCell("B");
    $table->easyCell("C");
    $table->easyCell("D");
    $table->easyCell("E");
    $table->easyCell("F");
    $table->printRow();

    // Table body (data rows)
    $x = 0;
    foreach ($data as $row) {
        $x++;
        $table->rowStyle('align:{CCCCCCCCCCCCCC};');
        $table->easyCell($x.".");
        $table->easyCell($row->username);
        if ($ca_percentage == 0) {
            $table->easyCell('NA');
        } else {
            $table->easyCell($row->ca < 10 ? '0' . $row->ca : $row->ca);
        }
        // $table->easyCell($row->ca < 10 ? '0' . $row->ca : $row->ca);
        $table->easyCell($row->exam < 10 ? '0' . $row->exam : $row->exam);
        $table->easyCell($row->total < 10 ? '0' . $row->total : $row->total);

        // Grades logic
        $table->easyCell($row->grade == 'A' ? "A" : "");
        $table->easyCell($row->grade == 'B' ? "B" : "");
        $table->easyCell($row->grade == 'C' ? "C" : "");
        $table->easyCell($row->grade == 'D' ? "D" : "");
        $table->easyCell($row->grade == 'E' ? "E" : "");
        $table->easyCell($row->grade == 'F' ? "F" : "");

        $table->easyCell($row->point . '.0');
        $table->easyCell($row->ugp < 10 ? '0' . $row->ugp : $row->ugp);
        if ($row->remark == "PASSED") {
            $table->easyCell("Pass");
        } else if ($row->remark == "FAILED") {
            $table->easyCell("Fail");
        } else {
            $table->easyCell($row->remark);
        }
        $table->printRow();
    }

    // Reset counters
    $a = 0;
    $b = 0;
    $c = 0;
    $d = 0;
    $e = 0;
    $f = 0;
    $pdf->AddPage();
}

$table->endTable(4);

// Output the PDF
$name = $course . ' ' . $session;
//$pdf->Output('D', $name . '.pdf', true);
$pdf->Output();
die;
