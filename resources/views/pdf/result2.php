<?php
// , 'approve' => $type
use Illuminate\Support\Facades\DB;

include ('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';
set_time_limit(3000);  // 5 minutes

function codeMod($input)
{
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}

function firstUpper($input)
{
    $excludeWords = ['or', 'and', 'of', 'the', 'in', 'on', 'at', 'by', 'for', 'to', 'iii'];
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
    protected $type;  // Variable to store the type

    // Constructor to accept the type
    function __construct($type)
    {
        parent::__construct();
        $this->type = $type;  // Store the type for later use
    }

    function Header()
    {
        // Determine the watermark text based on the type
        $watermarkText = ($this->type === 'vc') ? 'APPROVED' : 'NOT APPROVED';

        // Add watermark on every page
        $this->SetFont('Arial', 'B', 80);
        $this->SetTextColor(255, 192, 203);  // Pink color for the watermark

        // Calculate the center position
        $textWidth = $this->GetStringWidth($watermarkText);
        $x = ($this->GetPageWidth() - $textWidth) / 2;
        $y = ($this->GetPageHeight() / 2);

        // Rotate and print the watermark
        $this->RotateText($x, $y, $watermarkText, 45);

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
$semester = $semester;
$per = DB::table('results')->where(['code' => $course, 'session' => $session, 'approve' => $type, 'semester' => $semester])->value('per');

// Default percentage if `per` is not available (e.g., 70% for exams and 30% for C.A Marks)
if (!$per) {
    $per = 70;  // Default exam percentage
}
$ca_percentage = 100 - $per;  // C.A Marks percentage
$getCourses = DB::table('course')->where(['code' => $course])->get();
foreach ($getCourses as $row) {
    //$semester = $row->semester;
    $title = $row->title;
    $unit = $row->unit;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
    $program = DB::table('department')->where(['code' => $row->department])->value('title');
}

$pdf = new PDFWithWatermark($type);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

// $pdf->Rotate(0); // Reset rotation

$dept = DB::table('results')->select('department')->groupBy('department')->where(['code' => $course, 'session' => $session, 'approve' => $type, 'semester' => $semester])->get();
//dd($dept, $course, $session, $type, $semester);
foreach ($dept as $dp) {
    $count = 2;
    $columnWidths = [];
    $heading = 0;
    $grading_type = DB::table('results')->where(['code' => $course, 'session' => $session, 'department' => $dp->department, 'approve' => $type, 'semester' => $semester])->groupBy('grading')->select('grading')->get('grading');
    foreach ($grading_type as $gt) {
        $heading++;
        $grades = DB::table('grading_system')->where('ref', $gt->grading)->orderBy('grade');
        $count = $grades->count();
        $grade = $grades->pluck('grade');

        $columnWidths = [];
        for ($i = 0; $i < $count; $i++) {
            $columnWidths[] = 8;  // Assign each dynamic column a width of 10
        }
        $fixedWidths = [13, 28, 13, 13, 14];
        $allWidths = array_merge($fixedWidths, $columnWidths, [10, 11, 18]);
        $columnString = '{' . implode(', ', $allWidths) . '}';
        $table = new easyTable(
            $pdf,
            $columnString,
            'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;'
        );
        $data = DB::table('results')->where(['code' => $course, 'session' => $session, 'semester' => $semester, 'department' => $dp->department, 'grading' => $gt->grading])->orderBy('username', 'ASC')->get();
        if ($heading == 1) {
            // Table header
            $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
            $table->printRow();
            $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
            $table->printRow();
            $table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
            $table->printRow();
            $table->easyCell('DEPARTMENT OF ' . ucwords(strtoupper($program)), 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
            $table->printRow();
            $pdf->Ln(5);
            $table->easyCell(ucwords(strtolower($semester)) . ' Semester End-of-Course Examination Results for ' . $session . ' Academic Session', 'colspan:14; font-size:12; font-style:B; align:C;border:0;');
            $table->printRow();
            $table->easyCell('Course Code/Title: ' . codeMod($course) . ': ' . firstUpper($title) . '  Unit (s): ' . $unit, 'colspan:14; font-size:12; font-style:B; align:C;border:0;');
            $table->printRow();
            $table->easyCell('Department of ' . ucwords(strtolower(DB::table('department')->where('code', $dp->department)->value('title'))) . ' ', 'colspan:14; font-size:12; font-style:B; align:L;border:0;');
            $table->printRow();
        }

        $pdf->Ln(1);
        $table->rowStyle('align:{CCCCCCCCCCCCCC}; font-style:B;');
        //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
        $table->easyCell('S/No.', 'rowspan:2');
        $table->easyCell('ID. No.', 'rowspan:2');
        $table->easyCell("C.A Marks ($ca_percentage%)", 'rowspan:2');  // Dynamic C.A Marks
        $table->easyCell("Exam. Marks ($per%)", 'rowspan:2');  // Dynamic Exam Marks
        $table->easyCell('Total Marks (100%)', 'rowspan:2');
        $table->easyCell('Grades', 'colspan:' . $count, 'align:C; valign:M');
        $table->easyCell('GP', 'rowspan:2');
        $table->easyCell('U.GP', 'rowspan:2');
        $table->easyCell('Remarks.', 'rowspan:2');
        $table->printRow();

        $table->rowStyle('align:{CCCCCCCCC}; font-style:B;');
        foreach ($grade as $g) {
            $table->easyCell($g);
        }
        $table->printRow();
        $x = 0;
        $gradeCounts = [];
        foreach ($data as $row) {
            $x = $x + 1;
            $table->rowStyle('align:{CCCCCCCCCCCCCC};');
            $table->easyCell($x);
            $table->easyCell($row->username);
            if ($row->ca < 10) {
                if ($ca_percentage == 0) {
                    $table->easyCell('N/A');
                } else {
                    $table->easyCell('0' . $row->ca);
                }
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

            foreach ($grade as $g) {
                if ($row->grade == $g) {
                    if (isset($gradeCounts[$g])) {
                        $gradeCounts[$g]++;
                    } else {
                        // If the grade doesn't exist, initialize it with a count of 1
                        $gradeCounts[$g] = 1;
                    }
                    $table->easyCell($g);
                } else {
                    $table->easyCell('');
                }
            }

            // dd($gradeCounts);
            $table->easyCell($row->point . '.0');
            if ($row->ugp < 10) {
                $table->easyCell('0' . $row->ugp);
            } else {
                $table->easyCell($row->ugp);
            }
            if ($row->remark == 'PASSED') {
                $table->easyCell('Pass');
            } else if ($row->remark == 'FAILED') {
                $table->easyCell('Fail');
            } else {
                $table->easyCell($row->remark);
            }
            $table->printRow();
        }

        $overallTotal = 0;
        $table->rowStyle('align:{CCCCCCCCCCCCCC};');
        $table->easyCell('Total', 'colspan:5');
        foreach ($grade as $gd) {
            $total = isset($gradeCounts[$gd]) ? $gradeCounts[$gd] : 0;
            $table->easyCell(str_pad($total, 2, '0', STR_PAD_LEFT));
            $overallTotal += $total;
        }

        $table->easyCell(str_pad($overallTotal, 2, '0', STR_PAD_LEFT), 'colspan:3');
        $table->printRow();
        $a = 0;
        $b = 0;
        $c = 0;
        $d = 0;
        $e = 0;
        $f = 0;
        $table->endTable(4);
    }
    // dd($grade);
    $pdf->AddPage();
}

// -----------------------------------------
// $pdf->Output();
$name = $course . ' ' . $session;
// $pdf->Output('D',$name.'.pdf',true);
$pdf->Output('D', $name . '.pdf', true);
// $pdf->Output();
die;
