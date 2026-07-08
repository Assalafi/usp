<?php

use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';

$pdf = new exFPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);

foreach ($certificates as $cert) {
    $pdf->AddPage();

    // Fonts
    $pdf->AddFont('Calligrapher', '', 'calligra.php');
    $pdf->AddFont('Edwardian', '', 'edwardian.php');
    $pdf->AddFont('FreeScript', '', 'FREESCPT.php');
    $pdf->AddFont('GreatVibes', '', 'GreatVibes-Regular.php');       // 1. Elegant flowing calligraphy
    $pdf->AddFont('AlexBrush', '', 'AlexBrush-Regular.php');         // 2. Smooth elegant script
    $pdf->AddFont('TangerineBold', '', 'Tangerine-Bold.php');        // 3. Bold thin calligraphy
    $pdf->AddFont('PinyonScript', '', 'PinyonScript-Regular.php');   // 4. Formal ornamental calligraphy
    $pdf->AddFont('Parisienne', '', 'Parisienne-Regular.php');       // 5. Sophisticated French script

    $pageW = $pdf->GetPageWidth();  // 210
    $pageH = $pdf->GetPageHeight(); // 297

    // ==================== BACKGROUND IMAGE (contains ALL static content) ====================
    $showBg = isset($cert_bg) ? $cert_bg : '1';
    if ($showBg == '1') {
        $bgPath = public_path('uploads/cert-type2.png');
        $pdf->Image($bgPath, 0, 0, $pageW, $pageH);
    }

    // ==================== DYNAMIC DATA ONLY ====================
    $pdf->SetTextColor(0, 0, 0);

    // --- ID Number (on dotted line, right of logo) ---
    $pdf->SetFont('Calligrapher', '', 16);
    $pdf->SetXY(146.5, 60.5);
    $pdf->Cell(40, 6, $cert['username'] ?? '', 0, 0, 'L');

    // --- Student Name (on first dotted line, centered) ---
    // CHOOSE FONT: GreatVibes | AlexBrush | TangerineBold | PinyonScript | Parisienne
    $nameFont = 'Calligrapher';  // <-- CHANGE THIS to switch font
    $nameSize = 34;            // <-- CHANGE THIS to adjust size
    $pdf->SetFont($nameFont, '', $nameSize);
    $studentName = ucwords(strtolower($cert['student_name']));
    $pdf->SetXY(30, 110);
    $pdf->Cell(150, 12, $studentName, 0, 0, 'C');

    // --- Degree (on dotted line after body text, centered) ---
    $pdf->SetFont('Calligrapher', '', 22);
    $degree = $cert['degree'];
    $pdf->SetXY(30, 148);
    $pdf->Cell(150, 10, $degree, 0, 0, 'C');

    // --- Class of Degree (on dotted line after "with", centered) ---
    $pdf->SetFont('Calligrapher', '', 22);
    $classOfDegree = ucwords(strtolower($cert['class_of_degree']));
    $pdf->SetXY(30, 172.5);
    $pdf->Cell(150, 10, $classOfDegree, 0, 0, 'C');

    // --- Department (on dotted line after "in", centered) ---
    $pdf->SetFont('Calligrapher', '', 22);
    $department = ucwords(strtolower($cert['department']));
    $pdf->SetXY(30, 192.5);
    $pdf->Cell(150, 10, $department, 0, 0, 'C');

    // --- Graduation Date (fill in "Given this...day of...20...") ---
    $pdf->SetFont('Calligrapher', '', 14);
    // Parse graduation_date format: "12th Day of September, 2025"
    $rawDate = $cert['graduation_date'] ?? '';
    if (preg_match('/(\d+)\w*\s+Day\s+of\s+(\w+),?\s*(\d{4})/i', $rawDate, $m)) {
        $d = (int)$m[1];
        $suffix = match(true) {
            $d % 100 >= 11 && $d % 100 <= 13 => 'th',
            $d % 10 == 1 => 'st',
            $d % 10 == 2 => 'nd',
            $d % 10 == 3 => 'rd',
            default => 'th',
        };
        $day = $d . $suffix;
        $month = $m[2];
        $year = substr($m[3], 2);
    } else {
        $day = '';
        $month = '';
        $year = preg_match('/\d{4}/', $rawDate, $m) ? substr($m[0], 2) : '';
    }
    // Position day on "Given this......day of......20...."
    $pdf->SetXY(71, 215.5);
    $pdf->Cell(15, 6, $day, 0, 0, 'C');
    $pdf->SetXY(119, 215.5);
    $pdf->Cell(30, 6, $month, 0, 0, 'C');
    $pdf->SetXY(156, 215.5);
    $pdf->Cell(15, 6, $year, 0, 0, 'C');

    // ==================== QR CODE (centered between signatures) ====================
    $qrData = "UNIVERSITY OF MAIDUGURI - GRADUATION CERTIFICATE\n"
        . "Name: " . $cert['student_name'] . "\n"
        . "ID No: " . ($cert['username'] ?? '') . "\n"
        . "Award: " . $cert['degree'] . "\n"
        . "Class of Degree: " . $cert['class_of_degree'] . "\n"
        . "Course of Study: " . $cert['department'] . "\n"
        . "Date of Graduation: " . $cert['graduation_date'];

    $qrOptions = new QROptions([
        'outputInterface' => QRGdImagePNG::class,
        'scale'           => 5,
        'imageBase64'     => true,
    ]);

    $qrBase64 = (new QRCode($qrOptions))->render($qrData);
    $qrBase64 = preg_replace('/^data:image\/png;base64,/', '', $qrBase64);
    $qrTempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    file_put_contents($qrTempFile, base64_decode($qrBase64));

    $qrSize = 25;
    $qrX = ($pageW / 2) - ($qrSize / 2);
    $pdf->Image($qrTempFile, 30, 30, $qrSize, $qrSize);

    unlink($qrTempFile);
}

$pdf->Output();
die;
