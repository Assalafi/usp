<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
include('exfpdf.php');
include('easyTable.php');

// Retrieve key parameters (assumed to be defined elsewhere)
$lvl     = $lvl;      // e.g., '100', '200', etc.
$session = $session;  // e.g., "2020/2021"
$program = $program;  // program code
$type    = $type;
// Faculty, department and program details.
$fac      = DB::table('program')->where('code', $program)->value('faculty');
$proName  = DB::table('program')->where('code', $program)->value('title');
$dept     = DB::table('program')->where('code', $program)->value('department');
$deptName = DB::table('department')->where('code', $dept)->value('title');
$facName  = DB::table('faculty')->where('code', $fac)->value('title');

/**
 * Format a full name so that all but the last name are in title case
 * and the last name is in uppercase.
 */
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

/**
 * Add a space between letters and digits in a course code.
 */
function codeMod($input)
{
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}

/**
 * Format an array of course codes into a string.
 */
function formatCourseList(array $courses)
{
    if (empty($courses)) {
        return 'Nil';
    }
    if (count($courses) === 1) {
        return $courses[0];
    }
    if (count($courses) === 2) {
        return $courses[0] . ' and ' . $courses[1];
    }
    $last = array_pop($courses);
    return implode(', ', $courses) . ' and ' . $last;
}

/**
 * Retrieve cumulative data from the session_history table.
 */
function getCumulativeData($username, $currentSession)
{
    $totalUnits = DB::table('session_history')
                    ->where('username', $username)
                    ->where('session', '<=', $currentSession)
                    ->sum('total_unit');

    $totalGradePoints = DB::table('session_history')
                           ->where('username', $username)
                           ->where('session', '<=', $currentSession)
                           ->sum('product');

    return [$totalUnits, $totalGradePoints];
}


/**
 * Process a student's course registrations and cumulative history to compute:
 * - Total units, total grade points, CGPA,
 * - Number of failing courses and pending courses,
 * - A formatted list of failing and pending course codes.
 */
function getStudentData($username, $session, $session_history, $level, $fac)
{
    // Get student full name (adjust field selection as needed)
    $name = DB::table('students')->where('username', $username)->select('fullname')->value('fullname');
    // $level = DB::table('students')->where('username', $username)->select('level')->value('level');

    // Initialize counters and arrays.
    $unit = 0;
    $ugp = 0;
    $failCount = 0;
    $pendingCount = 0;
    $failingCourses = [];
    $pendingCourses = [];

    // Process course registrations.
    $registrations = DB::table('student_course_registration')
                        ->where(['username' => $username, 'session' => $session])
                        ->orderBy('username', 'ASC')
                        ->get();

    foreach ($registrations as $course) {
        if($course->level == $level && strtoupper($session_history) != 'REPEAT'){
            // $unit += DB::table('course')->where('code', $course->code)->value('unit');
            $unit += $course->unit;
        }
        if ($course->status === 'awaiting') {
            $pendingCount++;
            $pendingCourses[] = codeMod($course->code);
        } else {
            $ugp += $course->ugp;
            if ($course->grade === 'F') {
                $failCount++;
                $failingCourses[] = codeMod($course->code);
            }
        }
    }

    // Get cumulative session history data.
    list($cumUnits, $cumGradePoints) = getCumulativeData($username, $session);
    $unit += $cumUnits;
    $ugp += $cumGradePoints;
    $cgpa = ($unit > 0) ? $ugp / $unit : 0;

    return [
        'username'        => $username,
        'name'            => formatName($name),
        'unit'            => $unit,
        'ugp'             => $ugp,
        'cgpa'            => $cgpa,
        'failCount'       => $failCount,
        'pendingCount'    => $pendingCount,
        'failingCourses'  => formatCourseList($failingCourses),
        'pendingCourses'  => formatCourseList($pendingCourses),
        'faculty'         => $fac,
    ];
}

/**
 * Classify student status based on the following rules:
 * A: Passed all prescribed courses (no fails, no pending, and cgpa > 1.0)
 * B: Passed with carryover (cgpa > 1.0 and total fails+pending < 7)
 * C: Must repeat (failed more than six courses OR cgpa < 1.0 with no pending but with result)
 * D: Pending (total fails+pending > 6)
 *
 * (Note: Adjust conditions as needed so that each student appears only in one category.)
 */
function classifyStudent(array $data)
{
    $getFaculty = $data['faculty'];
    $baseCGPA = 0.99;
    if($getFaculty == 'PHARM' || $getFaculty == 'VET'){
        $baseCGPA = 2.39;
    }
    if ($data['failCount'] === 0 && $data['pendingCount'] === 0 && $data['cgpa'] > $baseCGPA) {
        return 'A';
    } elseif ($data['cgpa'] > $baseCGPA && ($data['failCount'] + $data['pendingCount']) < 7) {
        return 'B';
    } elseif ($data['failCount'] > 6 || ($data['cgpa'] < $baseCGPA && $data['pendingCount'] === 0 && $data['ugp'] != 0)) {
        return 'C';
    } elseif (($data['failCount'] + $data['pendingCount']) > 6) {
        return 'D';
    }
    return null; // Student does not fall into any category.
}

/**
 * Determine class based on CGPA.
 */
function getClassFromCGPA($cgpa)
{
    if ($cgpa >= 4.5) {
        return 'First';
    } elseif ($cgpa >= 3.5) {
        return 'Upper';
    } elseif ($cgpa >= 2.4) {
        return 'Lower';
    } elseif ($cgpa >= 1.5) {
        return 'Third';
    } elseif ($cgpa >= 1.0) {
        return 'Pass';
    }
    return 'Fail';
}

// ---------------------------------------------------


// Map level to roman numeral representations.
$levelMap = [
    '100' => ['current' => 'I', 'next' => 'II'],
    '200' => ['current' => 'II', 'next' => 'III'],
    '300' => ['current' => 'III', 'next' => 'IV'],
    '400' => ['current' => 'IV', 'next' => 'V'],
    '500' => ['current' => 'V', 'next' => '(GRADUATION)'],
];

$currentLevel = isset($levelMap[$lvl]) ? $levelMap[$lvl]['current'] : $lvl;
$nextLevel    = isset($levelMap[$lvl]) ? $levelMap[$lvl]['next'] : '';

// Retrieve session history records.
$sessionHistory = DB::table('session_history')
    ->where(['session' => $session, 'level' => $lvl, 'program' => $program])
    ->get();
// dd($sessionHistory);
// Initialize category arrays.
$categoryA = []; // Proceed without carryover.
$categoryB = []; // Proceed with carryover.
$categoryC = []; // Repeat (probation).
$categoryD = []; // Pending.

foreach ($sessionHistory as $record) {
    $studentData = getStudentData($record->username, $session, $record->status, $record->level, $fac);
    $cat = classifyStudent($studentData);
    if ($cat === 'A') {
        $categoryA[] = $studentData;
    } elseif ($cat === 'B') {
        $categoryB[] = $studentData;
    } elseif ($cat === 'C') {
        $categoryC[] = $studentData;
    } elseif ($cat === 'D') {
        $categoryD[] = $studentData;
    }
    if($cat == null){
        $categoryN[] = $studentData;
        // dd($categoryN);
    }
}

// Initialize PDF.
$pdf = new exFPDF();
$pdf->AddPage('O');
$pdf->SetFont('helvetica', '', 8);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

$table = new easyTable($pdf, '{11, 25, 55, 10, 14, 12, 13, 128}', 'width:185; border-color:black; font-size:8; border:1; paddingY:0.5;');

// Header
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(' . strtoupper($facName) . ')', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARTMENT OF ' . strtoupper($deptName), 'colspan:14; font-size:10; font-style:B; align:C;border:0;');
$table->printRow();
$table->easyCell("Provisional Academic Status of Part {$currentLevel} Students at the End of {$session} Academic Session", 'colspan:14; font-size:10; font-style:B; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);

/**
 * Helper to output a category.
 *
 * @param string $headerText Header line for the category.
 * @param array  $dataList   Array of student data.
 * @param string $status     Status string (e.g. "Proceed", "Repeat", etc.)
 * @param bool   $includeClass Whether to include the class column (used in Pending).
 */
function outputCategory($table, $headerText, array $dataList, $status, $includeClass = false)
{
    static $sn = 0; // Serial number counter.

    // Sort $dataList by 'username' in ascending order.
    usort($dataList, function ($a, $b) {
        return strcmp($a['username'], $b['username']);
    });

    $table->easyCell($headerText, 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
    $table->printRow();

    // Table header row.
    $headers = ["S/No.", "ID. No.", "Name", "Cum. Unit", "Cum. Product", "CGPA", "Status"];
    if ($includeClass) {
        $headers[] = "Remarks";
        $headers[] = "Class";
    } else {
        $headers[] = "Remarks";
    }
    $headerStyle = 'align:{CCCCCCCC}; font-style:B;';
    foreach ($headers as $h) {
        $table->easyCell($h, 'font-style:B;');
    }
    $table->printRow();

    if (empty($dataList)) {
        // If no records, print Nil.
        $table->easyCell($headerText . ' Nil', 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
        $table->printRow();
        return 0;
    }

    foreach ($dataList as $data) {
        // $remarks = ($data['pendingCourses'] !== 'Nil')
        //     ? 'F[' . $data['failCount'] . ']: ' . $data['failingCourses'] . "\n" . 'P[' . $data['pendingCount'] . ']: ' . $data['pendingCourses']
        //     : 'F[' . $data['failCount'] . ']: ' . $data['failingCourses'];

        $remarks = ($data['pendingCourses'] !== 'Nil')
        ? ($data['failCount'] > 0 ? 'F[' . $data['failCount'] . ']: ' : 'F: ') . $data['failingCourses'] . "\n" . 'P[' . $data['pendingCount'] . ']: ' . $data['pendingCourses']
        : ($data['failCount'] > 0 ? 'F[' . $data['failCount'] . ']: ' : 'F: ') . $data['failingCourses'];
        $ssn = '';
        $sn++;
        $ssn = $sn.'.';
        $table->rowStyle('align:{CCLCCCCL};');
        $table->easyCell($ssn);
        $table->easyCell($data['username']);
        $table->easyCell($data['name']);
        $table->easyCell((float)$data['unit']);
        $table->easyCell((float)$data['ugp']);
        $table->easyCell(number_format($data['cgpa'], 2, '.', ''));
        $table->easyCell($status);
        $table->easyCell($remarks);

        if ($includeClass) {
            // For pending category, also show the computed class.
            $table->easyCell(getClassFromCGPA($data['cgpa']));
        }

        $table->printRow();
    }

    return count($dataList);
}

// Output Category A: Proceed without carryover.
$countA = outputCategory(
    $table,
    "A. The following Part {$currentLevel} Students, having passed all the prescribed courses are to proceed to Part {$nextLevel}:",
    $categoryA,
    'Proceed'
);
$pdf->Ln(4);

// Output Category B: Proceed with carryover.
$countB = outputCategory(
    $table,
    "B. The following Part {$currentLevel} Students, having passed some prescribed courses and failed others, are to proceed to Part {$nextLevel} but will carry over failed courses:",
    $categoryB,
    'Proceed'
);
$pdf->Ln(4);

// Output Category C: Repeat (probation).
$countC = outputCategory(
    $table,
    "C. The following Part {$currentLevel} Students, having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses, are to repeat (probation):",
    $categoryC,
    'Repeat'
);
$pdf->Ln(4);

// Output Category D: Pending (include class column).
$countD = outputCategory(
    $table,
    "D. The status of the following Part {$currentLevel} Students is 'pending' to be determined after obtaining the pending result(s):",
    $categoryD,
    'Pending',
    true
);
$pdf->Ln(4);

// Static rows for categories E, F, G.
$table->easyCell("E. The following Part {$currentLevel} Students, are on suspension: Nil", 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$table->printRow();
$pdf->Ln(2);
$table->easyCell("F. The following Part {$currentLevel} Students, having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses during the repeat year (probation) are to withdraw: Nil", 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$table->printRow();
$pdf->Ln(2);
$table->easyCell("G. The following students, having failed to register for session {$session}, are considered to have voluntarily withdrawn from the program: Nil", 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$table->printRow();
$pdf->Ln(2);
// H. Others
$table->easyCell("H. Others: Nil", 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$table->printRow();

$pdf->Ln(4);

// SUMMARY:
$table->easyCell('SUMMARY:', 'colspan:14; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Proceed without carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $countA), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Proceed with carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $countB), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Repeat (probation)', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $countC), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Pending', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $countD), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
// The following counts (Suspension, Expulsion, Withdraw, Voluntary Withdraw) remain zero.
$table->easyCell('Suspension', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", 0), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Expulsion', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", 0), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", 0), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Voluntarily Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", 0), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Total', 'colspan:3; font-style:B; font-size:10; align:L;border:0;');
$totalCount = $countA + $countB + $countC + $countD;
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $totalCount), 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->printRow();

$table->endTable(4);

$pdf->Output();
die;
