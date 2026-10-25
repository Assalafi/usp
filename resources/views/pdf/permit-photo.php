<?php

use Illuminate\Support\Facades\DB;
use App\Models\Student;

include 'exfpdf.php';

date_default_timezone_set('Africa/Lagos');

$asset = static function (string $relative): string {
    $absolute = function_exists('public_path') ? public_path($relative) : $relative;
    return is_string($absolute) && file_exists($absolute) ? $absolute : $relative;
};

$fitFont = static function ($pdf, string $text, float $width, float $maximum = 13, float $minimum = 8): float {
    $size = $maximum;
    while ($size > $minimum) {
        $pdf->SetFont('helvetica', 'B', $size);
        if ($pdf->GetStringWidth($text) <= $width) {
            break;
        }
        $size -= 0.5;
    }
    return $size;
};

$labelValue = static function ($pdf, float $x, float $y, float $width, string $label, string $value, float $size = 9.5): void {
    $pdf->SetXY($x, $y);
    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(101, 119, 134);
    $pdf->Cell($width, 4, strtoupper($label), 0, 0, 'L');
    $pdf->SetXY($x, $y + 4);
    $pdf->SetFont('helvetica', 'B', $size);
    $pdf->SetTextColor(24, 51, 72);
    $pdf->MultiCell($width, 4.3, $value !== '' ? $value : 'Not provided', 0, 'L');
};

$allocationTile = static function ($pdf, float $x, float $y, float $width, string $label, string $value): void {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(215, 227, 235);
    $pdf->Rect($x, $y, $width, 29, 'DF');
    $pdf->SetFillColor(61, 158, 210);
    $pdf->Rect($x, $y, 2.2, 29, 'F');
    $pdf->SetXY($x + 6, $y + 5);
    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(101, 119, 134);
    $pdf->Cell($width - 10, 4, strtoupper($label), 0, 1, 'L');
    $pdf->SetXY($x + 6, $y + 11);
    $size = strlen($value) > 18 ? 8.4 : (strlen($value) > 12 ? 9.5 : 12);
    $pdf->SetFont('helvetica', 'B', $size);
    $pdf->SetTextColor(24, 51, 72);
    $pdf->MultiCell($width - 10, 4.4, $value !== '' ? $value : '-', 0, 'L');
};

$users = DB::table('hostel')->where('id', $id)->get();
$pdf = new exFPDF('P', 'mm', 'A4');
$pdf->SetMargins(14, 14, 14);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$temporaryPhotoPaths = [];

foreach ($users as $row) {
    $external = is_array($permitStudent ?? null) ? $permitStudent : null;
    $student = null;
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

    $isSbrs = $external !== null;
    $name = $name !== '' ? $name : trim((string) $row->occupant);
    $programme = $programme !== '' ? $programme : ($isSbrs ? 'School of Basic and Remedial Studies' : 'Programme unavailable');
    $category = $isSbrs ? 'SBRS / Remedial' : 'Undergraduate';
    $genderLabel = strtoupper($gender) === 'MALE' ? 'Male' : (strtoupper($gender) === 'FEMALE' ? 'Female' : 'Not provided');
    $permitNumber = sprintf('HP-%06d', (int) $row->id);
    $issuedDate = date('d F Y');
    try {
        $session = (string) \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
    } catch (\Throwable $e) {
        $session = '';
    }
    $session = $session !== '' ? $session : 'Current academic session';

    $photoPath = $asset('card/default.jpg');
    $photoType = 'JPG';
    if ($external && !empty($external['photo_base64']) && !empty($external['photo_mime'])) {
        $photoBytes = base64_decode((string) $external['photo_base64'], true);
        $mime = strtolower(trim((string) $external['photo_mime']));
        if ($photoBytes !== false && strlen($photoBytes) <= 1024 * 1024 && in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $temporaryPhoto = tempnam(sys_get_temp_dir(), 'hostel_permit_');
            if ($temporaryPhoto !== false && file_put_contents($temporaryPhoto, $photoBytes) !== false && @getimagesize($temporaryPhoto)) {
                $photoPath = $temporaryPhoto;
                $photoType = $mime === 'image/png' ? 'PNG' : 'JPG';
                $temporaryPhotoPaths[] = $temporaryPhoto;
            }
        }
    } elseif ($student && trim((string) $student->picture) !== '') {
        $candidate = $asset('storage/picture/' . ltrim((string) $student->picture, '/'));
        $imageInfo = @getimagesize($candidate);
        if ($imageInfo && in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png'], true)) {
            $photoPath = $candidate;
            $photoType = ($imageInfo['mime'] ?? '') === 'image/png' ? 'PNG' : 'JPG';
        }
    }

    // Quiet, paper-like background with a modern university-blue accent.
    $pdf->SetFillColor(247, 250, 252);
    $pdf->Rect(0, 0, 210, 297, 'F');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(10, 10, 190, 277, 'F');
    $pdf->SetDrawColor(222, 231, 237);
    $pdf->SetLineWidth(0.25);
    $pdf->Rect(10, 10, 190, 277, 'D');
    $pdf->SetFillColor(61, 158, 210);
    $pdf->Rect(10, 10, 4, 277, 'F');

    // Clean white institutional header.
    $logo = $asset('uploads/logo-transparent.png');
    $pdf->Image($logo, 19, 17, 22, 23, 'PNG');
    $pdf->SetXY(47, 18);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(103, 7, 'UNIVERSITY OF MAIDUGURI', 0, 1, 'L');
    $pdf->SetX(47);
    $pdf->SetFont('helvetica', 'I', 9.5);
    $pdf->SetTextColor(204, 73, 67);
    $pdf->Cell(103, 5, '...Centre of Excellence', 0, 1, 'L');
    $pdf->SetX(47);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(101, 119, 134);
    $pdf->Cell(103, 5, 'STUDENT AFFAIRS DIVISION', 0, 0, 'L');
    $pdf->SetXY(154, 18);
    $pdf->SetFillColor(232, 246, 252);
    $pdf->SetDrawColor(190, 222, 236);
    $pdf->Rect(154, 18, 37, 17, 'DF');
    $pdf->SetXY(158, 21);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(42, 125, 169);
    $pdf->Cell(29, 4, 'OFFICIAL PERMIT', 0, 1, 'C');
    $pdf->SetX(158);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(29, 5, $permitNumber, 0, 0, 'C');
    $pdf->SetDrawColor(224, 232, 238);
    $pdf->Line(19, 46, 191, 46);

    // Title area - light, spacious, and easy to scan.
    $pdf->SetFillColor(238, 247, 252);
    $pdf->Rect(18, 52, 174, 19, 'F');
    $pdf->SetFillColor(61, 158, 210);
    $pdf->Rect(18, 52, 3, 19, 'F');
    $pdf->SetXY(27, 56);
    $pdf->SetFont('helvetica', 'B', 14.5);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(116, 6, 'HOSTEL ACCOMMODATION PERMIT', 0, 1, 'L');
    $pdf->SetXY(27, 63);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(101, 119, 134);
    $pdf->Cell(116, 4, $session . ' academic session', 0, 0, 'L');
    $pdf->SetXY(151, 58);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(39, 139, 97);
    $pdf->Cell(34, 5, 'PAID & CONFIRMED', 0, 0, 'R');

    // Identity card with prominent photograph.
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(215, 227, 235);
    $pdf->Rect(18, 78, 174, 65, 'DF');
    $pdf->SetFillColor(246, 249, 251);
    $pdf->Rect(18, 78, 174, 9, 'F');
    $pdf->SetXY(24, 81);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(162, 4, 'RESIDENT IDENTIFICATION', 0, 0, 'L');

    $photoX = 24;
    $photoY = 93;
    $photoW = 36;
    $photoH = 43;
    $pdf->SetFillColor(241, 245, 248);
    $pdf->SetDrawColor(190, 207, 218);
    $pdf->Rect($photoX, $photoY, $photoW, $photoH, 'DF');
    $imageInfo = @getimagesize($photoPath);
    if ($imageInfo && ($imageInfo[0] ?? 0) > 0 && ($imageInfo[1] ?? 0) > 0) {
        $scale = min(($photoW - 2) / $imageInfo[0], ($photoH - 2) / $imageInfo[1]);
        $drawW = $imageInfo[0] * $scale;
        $drawH = $imageInfo[1] * $scale;
        $drawX = $photoX + (($photoW - $drawW) / 2);
        $drawY = $photoY + (($photoH - $drawH) / 2);
        try {
            $pdf->Image($photoPath, $drawX, $drawY, $drawW, $drawH, $photoType);
        } catch (\Throwable $e) {
            // The framed space remains visible if an invalid legacy image is encountered.
        }
    }

    $nameSize = $fitFont($pdf, strtoupper($name), 119, 13.5, 9);
    $pdf->SetXY(68, 93);
    $pdf->SetFont('helvetica', 'B', $nameSize);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(118, 7, strtoupper($name), 0, 1, 'L');
    $pdf->SetXY(68, 101);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(42, 125, 169);
    $pdf->Cell(118, 5, (string) $row->occupant, 0, 1, 'L');
    $pdf->SetDrawColor(230, 236, 240);
    $pdf->Line(68, 109, 186, 109);
    $labelValue($pdf, 68, 112, 76, 'Programme / school', $programme, 8.7);
    $labelValue($pdf, 148, 112, 38, 'Category', $category, 8.2);
    $labelValue($pdf, 68, 128, 38, 'Gender', $genderLabel, 8.7);
    $labelValue($pdf, 110, 128, 76, 'Date issued', $issuedDate, 8.7);

    // Large allocation tiles make the most important information obvious.
    $pdf->SetXY(18, 151);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(174, 5, 'ALLOCATED BED SPACE', 0, 1, 'L');
    $allocationTile($pdf, 18, 159, 40, 'Hall', trim((string) $row->hall));
    $allocationTile($pdf, 62.5, 159, 40, 'Block', trim((string) $row->block));
    $allocationTile($pdf, 107, 159, 40, 'Room', trim((string) $row->room));
    $allocationTile($pdf, 151.5, 159, 40.5, 'Bed', trim((string) $row->bed));

    // Validity and conduct notice.
    $pdf->SetFillColor(235, 248, 242);
    $pdf->SetDrawColor(181, 224, 204);
    $pdf->Rect(18, 196, 174, 17, 'DF');
    $pdf->SetXY(24, 200);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(32, 124, 88);
    $pdf->Cell(47, 5, 'VALID AND CONFIRMED', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(57, 86, 75);
    $pdf->MultiCell(112, 4.2, 'Present this permit to the Hall Officer during check-in. This allocation is personal and cannot be sold or transferred.', 0, 'L');

    $pdf->SetXY(18, 222);
    $pdf->SetFont('helvetica', '', 8.3);
    $pdf->SetTextColor(76, 96, 111);
    $pdf->MultiCell(174, 4.5, 'The named student is authorised to occupy the bed space shown above, subject to University hostel regulations and the conditions of residence.', 0, 'C');

    // Approval area with restrained signature treatment.
    $signature = $asset('uploads/sign2.png');
    $pdf->Image($signature, 82, 238, 45, 14, 'PNG');
    $pdf->SetDrawColor(80, 101, 116);
    $pdf->SetLineWidth(0.25);
    $pdf->Line(76, 253, 134, 253);
    $pdf->SetXY(55, 255);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(21, 64, 91);
    $pdf->Cell(100, 5, 'Dean of Students', 0, 1, 'C');
    $pdf->SetX(55);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetTextColor(101, 119, 134);
    $pdf->Cell(100, 4, 'Student Affairs Division', 0, 0, 'C');

    // Minimal footer.
    $pdf->SetDrawColor(224, 232, 238);
    $pdf->Line(18, 271, 192, 271);
    $pdf->SetXY(18, 275);
    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(110, 126, 138);
    $pdf->Cell(174, 4, 'Verify the student identity and allocated bed before admission into the hostel.', 0, 1, 'C');
    $pdf->SetX(18);
    $pdf->SetFont('helvetica', 'I', 6.8);
    $pdf->Cell(174, 4, 'Generated from the University of Maiduguri Student Portal', 0, 0, 'C');
}

if (isset($outputPath) && is_string($outputPath)) {
    $pdf->Output('F', $outputPath);
} else {
    $pdf->Output();
}

foreach ($temporaryPhotoPaths as $temporaryPhotoPath) {
    if (is_string($temporaryPhotoPath) && is_file($temporaryPhotoPath)) {
        @unlink($temporaryPhotoPath);
    }
}
exit;
?>
