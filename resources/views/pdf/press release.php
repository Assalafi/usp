<?php
use Illuminate\Support\Facades\DB;
use App\Models\Program;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';

function formatName($fullName)
{
    $fullName = trim($fullName);
    $nameParts = explode(' ', $fullName);
    $lastNameIndex = count($nameParts) - 1;
    $nameParts[$lastNameIndex] = strtoupper($nameParts[$lastNameIndex]);
    for ($i = 0; $i < $lastNameIndex; $i++) {
        $nameParts[$i] = ucfirst(strtolower($nameParts[$i]));
    }
    return implode(' ', $nameParts);
}

// Initialize variables
$students = [
    'first_class' => [],
    'second_upper' => [],
    'second_lower' => [],
    'third_class' => [],
    'pass' => []
];

$session = $session;
$program = $program;
$programTitle = '';
$rec = Program::where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row->award;
    $faculty = $row->facultys->title;
    $dept = $row->depts->title;
    $programTitle = $row->title;
    $duration = ($row->duration) * 100;
    //$duration = 200;
}

$pdf = new exFPDF();
$pdf->AddPage('O');
$pdf->SetFont('helvetica', '', 8);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
$table = new easyTable($pdf, '{13, 30, 60, 70, 60}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');

$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARMENT OF ' . strtoupper($dept), 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('Press Release of Graduating Students for ' . $session . ' Academic Session', 'colspan:14; font-size:12; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);

$table->easyCell('Degree Awarded: ' . $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'colspan:4; font-size:12; align:L;border:0;');
$table->easyCell('Year of Graduation: ' . date('Y'), 'colspan:1; font-size:12; align:R;border:0;');
$table->printRow();
$table->rowStyle('align:{CCCCCCCC}; font-style:B;');
$table->easyCell("S/No.");
$table->easyCell("ID. No.");
$table->easyCell("Name");
$table->easyCell("Degree Award");
$table->easyCell("Class of Degree");
$table->printRow();

$session_history = DB::table('session_history')->where(['session' => $session, 'level' => $duration, 'program' => $program])->get();
$sn = 1;
$snn = 1;

foreach ($session_history as $results) {
    $id = $results->username;
    $level = $results->level;
    $session_history = $results->status;
    $names = DB::table('students')->where(['username' => $id])->select('first_name','last_name','other_name')->first();
    $name = $names->first_name .' '. $names->last_name .' '. $names->other_name;
    $reg = DB::table('student_course_registration')
    ->where(['username' => $id, 'session' => $session])->orderBy('username', 'ASC')->get();

    $unit = 0;
    $ugp = 0;
    $f = 0;

    foreach ($reg as $result) {
        if( $result->level == $level && strtoupper($session_history) != "REPEAT"){
            $unit += $result->unit;
        }
        if ($result->status != 'awaiting') {
            $ugp += $result->ugp;
            if ($result->grade == 'F') {
                $f++;
            }
        }
    }
    $totalUnits = DB::table('session_history')->where('username', $id)->where('session', '<=', $session)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->where('session', '<=', $session)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
    if ($unit > 0) {
        $cgpa = $ugp / $unit;
        if ($cgpa >= 4.5) {
            $class = 'First Class';
            if ($f == 0) {
                $students['first_class'][] = ['sn' => $sn++, 'id' => $id, 'name' => formatName($name), 'degree' => $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'class' => $class];
            }
        } elseif ($cgpa >= 3.5) {
            $class = 'Second Class Upper Division';
            if ($f == 0) {
                $students['second_upper'][] = ['sn' => $sn++, 'id' => $id, 'name' => formatName($name), 'degree' => $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'class' => $class];
            }
        } elseif ($cgpa >= 2.4) {
            $class = 'Second Class Lower Division';
            if ($f == 0) {
                $students['second_lower'][] = ['sn' => $sn++, 'id' => $id, 'name' => formatName($name), 'degree' => $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'class' => $class];
            }
        } elseif ($cgpa >= 1.5) {
            $class = 'Third Class';
            if ($f == 0) {
                $students['third_class'][] = ['sn' => $sn++, 'id' => $id, 'name' => formatName($name), 'degree' => $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'class' => $class];
            }
        } else {
            $class = 'Pass';
            if ($f == 0 && $ugp != 0) {
                $students['pass'][] = ['sn' => $sn++, 'id' => $id, 'name' => formatName($name), 'degree' => $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'class' => $class];
            }
        }
    }
}

// Print students in the desired order: First Class, Second Class Upper, Second Class Lower, Third Class, and Pass
$degree_classes = ['first_class', 'second_upper', 'second_lower', 'third_class', 'pass'];

foreach ($degree_classes as $degree_class) {
    foreach ($students[$degree_class] as $student) {
        $table->rowStyle('align:{CCLCL};');
        $table->easyCell($snn++);
        $table->easyCell($student['id']);
        $table->easyCell($student['name']);
        $table->easyCell($student['degree']);
        $table->easyCell($student['class']);
        $table->printRow();
    }
}

$pdf->Ln(4);
$table->easyCell('SUMMARY:', 'colspan:2; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('First Class', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", count($students['first_class'])), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Upper Division', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", count($students['second_upper'])), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Lower Division', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", count($students['second_lower'])), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Third Class', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", count($students['third_class'])), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Pass', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", count($students['pass'])), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Total', 'font-style:B; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", --$snn), 'colspan:7; font-style:B; font-size:10; align:L;border:0;');
$table->printRow();
$table->endTable(4);

$pdf->Output();
die;
