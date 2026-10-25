<?php

use Illuminate\Support\Facades\DB;
use App\Models\Student;

include 'exfpdf.php';

date_default_timezone_set('Africa/Lagos');

$asset = static function (string $relative): string {
    $absolute = function_exists('public_path') ? public_path($relative) : $relative;
    return is_string($absolute) && file_exists($absolute) ? $absolute : $relative;
};

$field = static function ($pdf, float $x, float $y, float $width, string $label, string $value, float $size = 10): void {
    $pdf->SetXY($x, $y);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetTextColor(91, 112, 131);
    $pdf->Cell($width, 4, strtoupper($label), 0, 0, 'L');
    $pdf->SetXY($x, $y + 4);
    $pdf->SetFont('helvetica', 'B', $size);
    $pdf->SetTextColor(27, 54, 78);
    $pdf->MultiCell($width, 4.5, $value !== '' ? $value : 'Not provided', 0, 'L');
};

$users = DB::table('hostel')->where('id', $id)->get();
$pdf = new exFPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$navy = [20, 56, 84];
$blue = [38, 139, 196];
$gold = [205, 157, 57];
$ink = [27, 54, 78];
$muted = [91, 112, 131];
$pale = [245, 248, 251];
$border = [218, 228, 235];
$green = [36, 135, 97];

foreach ($users as $row) {
    $external = is_array($permitStudent ?? null) ? $permitStudent : null;
    $isSbrs = $external !== null;
    $name = '';
    $programme = '';
    $gender = trim((string) ($row->gender ?? ''));

    if ($external) {
        $name = trim((string) ($external['name'] ?? $row->occupant));
        $programme = trim((string) ($external['programme_name'] ?? 'School of Basic and Remedial Studies'));
        $gender = trim((string) ($external['gender'] ?? $gender));
    } else {
        $student = Student::where('username', $row->occupant)->first();
        if ($student) {
            $name = trim(implode(' ', array_filter([$student->first_name, $student->last_name, $student->other_name])));
            $programme = trim((string) (optional($student->programs)->title ?: 'Programme unavailable'));
            $gender = trim((string) ($student->gender ?: $gender));
        }
    }

    $name = $name !== '' ? $name : trim((string) $row->occupant);
    $programme = $programme !== '' ? $programme : ($isSbrs ? 'School of Basic and Remedial Studies' : 'Programme unavailable');
    $genderLabel = strtoupper($gender) === 'MALE' ? 'Male' : (strtoupper($gender) === 'FEMALE' ? 'Female' : 'Not provided');
    $category = $isSbrs ? 'SBRS / Remedial' : 'Undergraduate';
    $session = '';
    try {
        $session = (string) \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
    } catch (\Throwable $e) {
        $session = '';
    }
    $session = $session !== '' ? $session : 'Current academic session';
    $permitNumber = sprintf('HP-%06d', (int) $row->id);
    $issuedDate = date('d F Y');

    $pdf->SetFillColor(248, 250, 252);
    $pdf->Rect(0, 0, 210, 297, 'F');
    $pdf->SetFillColor($navy[0], $navy[1], $navy[2]);
    $pdf->Rect(0, 0, 210, 7, 'F');
    $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
    $pdf->Rect(0, 7, 210, 1.2, 'F');

    $logo = $asset('uploads/logo-transparent.png');
    $pdf->Image($logo, 17, 14, 23, 23, 'PNG');
    $pdf->SetXY(45, 14);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor($navy[0], $navy[1], $navy[2]);
    $pdf->Cell(150, 7, 'UNIVERSITY OF MAIDUGURI', 0, 1, 'L');
    $pdf->SetX(45);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(190, 73, 65);
    $pdf->Cell(150, 5, '...Centre of Excellence', 0, 1, 'L');
    $pdf->SetX(45);
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(150, 5, 'P.M.B. 1069, Maiduguri, Borno State - Nigeria', 0, 1, 'L');
    $pdf->SetDrawColor($border[0], $border[1], $border[2]);
    $pdf->SetLineWidth(0.35);
    $pdf->Line(15, 43, 195, 43);

    $pdf->SetFillColor($blue[0], $blue[1], $blue[2]);
    $pdf->Rect(15, 48, 180, 17, 'F');
    $pdf->SetXY(21, 51);
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(120, 6, 'HOSTEL ACCOMMODATION PERMIT', 0, 0, 'L');
    $pdf->SetXY(21, 58);
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->Cell(120, 4, 'Student Affairs Division  |  ' . $session, 0, 0, 'L');
    $pdf->SetXY(145, 52);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(44, 5, $permitNumber, 0, 1, 'R');
    $pdf->SetXY(145, 58);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->Cell(44, 4, 'Official permit reference', 0, 0, 'R');

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor($border[0], $border[1], $border[2]);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect(15, 72, 180, 48, 'DF');
    $pdf->SetFillColor($pale[0], $pale[1], $pale[2]);
    $pdf->Rect(15, 72, 180, 9, 'F');
    $pdf->SetXY(21, 75);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->Cell(90, 4, 'PERMIT HOLDER', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor($green[0], $green[1], $green[2]);
    $pdf->Cell(83, 4, 'PAYMENT CONFIRMED', 0, 1, 'R');
    $field($pdf, 21, 86, 108, 'Full name', $name, 11.5);
    $field($pdf, 137, 86, 51, 'ID number', (string) $row->occupant, 9.2);
    $field($pdf, 21, 102, 108, 'Programme / school', $programme, 9.3);
    $field($pdf, 137, 102, 51, 'Category', $category, 8.4);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(15, 128, 180, 45, 'DF');
    $pdf->SetFillColor($pale[0], $pale[1], $pale[2]);
    $pdf->Rect(15, 128, 180, 9, 'F');
    $pdf->SetXY(21, 131);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->Cell(90, 4, 'ALLOCATED BED SPACE', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(83, 4, 'Issued ' . $issuedDate, 0, 1, 'R');
    $field($pdf, 21, 143, 38, 'Hall', trim((string) $row->hall), 10);
    $field($pdf, 65, 143, 38, 'Block', trim((string) $row->block), 10);
    $field($pdf, 109, 143, 38, 'Room', trim((string) $row->room), 10);
    $field($pdf, 153, 143, 35, 'Bed', trim((string) $row->bed), 10);
    $field($pdf, 21, 159, 82, 'Gender', $genderLabel, 9.5);
    $field($pdf, 109, 159, 79, 'Permit status', 'Active and confirmed', 8.8);

    $pdf->SetFillColor(237, 247, 252);
    $pdf->SetDrawColor(175, 218, 234);
    $pdf->Rect(15, 181, 180, 29, 'DF');
    $pdf->SetXY(21, 186);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($blue[0], $blue[1], $blue[2]);
    $pdf->Cell(168, 5, 'AUTHORISED ACCOMMODATION', 0, 1, 'L');
    $pdf->SetXY(21, 193);
    $pdf->SetFont('helvetica', '', 8.6);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->MultiCell(168, 4.5, 'This permit confirms the allocation shown above. It is personal to the named student and must be presented to the Hall Officer during check-in. It may not be sold, transferred, or used by another person.', 0, 'L');

    $pdf->SetXY(15, 222);
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(180, 5, 'Issued by Student Affairs Division', 0, 1, 'C');
    $signature = $asset('uploads/sign2.png');
    $pdf->Image($signature, 82, 231, 45, 15, 'PNG');
    $pdf->SetDrawColor($ink[0], $ink[1], $ink[2]);
    $pdf->SetLineWidth(0.25);
    $pdf->Line(75, 247, 135, 247);
    $pdf->SetXY(45, 249);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->Cell(120, 5, 'Dean of Students', 0, 1, 'C');

    $pdf->SetDrawColor($border[0], $border[1], $border[2]);
    $pdf->Line(15, 267, 195, 267);
    $pdf->SetXY(15, 271);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(180, 4, 'Keep this permit safe. Verification may be requested at the point of entry.', 0, 1, 'C');
    $pdf->SetXY(15, 276);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->Cell(180, 4, 'Generated from the University of Maiduguri Student Portal', 0, 0, 'C');
}

if (isset($outputPath) && is_string($outputPath)) {
    $pdf->Output('F', $outputPath);
} else {
    $pdf->Output();
}
exit;
?>
