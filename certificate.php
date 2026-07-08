<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';

$pdf = new exFPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 15);

foreach ($certificates as $cert) {
    $pdf->AddPage();

    // Fonts
    $pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
    $pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
    $pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
    $pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
    // Add Calligrapher script font for names (closest to Edwardian style)
    $pdf->AddFont('Calligrapher', '', 'calligra.php');

    $pageW = $pdf->GetPageWidth();
    $pageH = $pdf->GetPageHeight();

    // ==================== BORDER ====================
    // New border image
    $borderPath = public_path('uploads/cert.png');
    if (file_exists($borderPath)) {
        $pdf->Image($borderPath, 0, 0, $pageW, $pageH);
    } else {
        // Fallback to simple borders if image doesn't exist
        $pdf->SetDrawColor(20, 47, 102);
        $pdf->SetLineWidth(1.5);
        $pdf->Rect(10, 10, $pageW - 20, $pageH - 20);
        
        $pdf->SetDrawColor(191, 154, 74);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, 15, $pageW - 30, $pageH - 30);
    }

    // Content margins (inside the border)
    $marginL = 30;
    $marginR = 30;
    $contentW = $pageW - $marginL - $marginR;

    // ==================== LOGO ====================
    $logoPath = public_path('uploads/logo.png');
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, ($pageW / 2) - 15, 22, 30);
    }

    // ==================== ID NUMBER (right of logo) ====================
    $pdf->SetFont('Times', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($pageW - $marginR - 60, 35);
    $pdf->Cell(35, 6, 'ID. No. ', 0, 0, 'R');
    $pdf->SetFont('Calligrapher', '', 16);
    $pdf->Cell(25, 6, $cert['username'] ?? '', 0, 0, 'L');

    // ==================== UNIVERSITY NAME ====================
    $pdf->SetY(62);
    $pdf->SetFont('Times', 'B', 24);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'UNIVERSITY OF MAIDUGURI', 0, 1, 'C');

    $pdf->Ln(12);

    // ==================== "This is to certify that" ====================
    $pdf->SetFont('Times', 'I', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'This is to certify that', 0, 1, 'C');

    $pdf->Ln(2);

    // ==================== STUDENT NAME (calligraphy + dotted line) ====================
    $pdf->SetFont('Calligrapher', '', 28);
    $pdf->SetTextColor(0, 0, 0);
    $studentName = ucwords(strtolower($cert['student_name']));
    // Dotted line before and after name
    $nameW = $pdf->GetStringWidth($studentName);
    $lineStart = $marginL + 5;
    $lineEnd = $pageW - $marginR - 5;
    $nameX = ($pageW - $nameW) / 2;
    $nameY = $pdf->GetY();
    // Draw dotted line under name area
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $dotY = $nameY + 12;
    for ($x = $lineStart; $x < $lineEnd; $x += 2) {
        $pdf->Line($x, $dotY, $x + 1, $dotY);
    }
    $pdf->Cell(0, 14, $studentName, 0, 1, 'C');

    // ==================== BODY TEXT (italic) ====================
    $pdf->SetFont('Times', 'I', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'having  fulfilled  all  the  requirements  of  the  University', 0, 1, 'C');
    $pdf->Cell(0, 8, 'and  passed  the  prescribed  examinations  has,  under  the', 0, 1, 'C');
    $pdf->Cell(0, 8, 'authority  of  the  Senate,  been  admitted  to  the  Degree  of', 0, 1, 'C');

    $pdf->Ln(2);

    // ==================== DEGREE (calligraphy + dotted line) ====================
    $pdf->SetFont('Calligrapher', '', 24);
    $pdf->SetTextColor(0, 0, 0);
    $degree = ucwords(strtolower($cert['degree']));
    $degreeY = $pdf->GetY();
    $dotY2 = $degreeY + 10;
    for ($x = $lineStart; $x < $lineEnd; $x += 2) {
        $pdf->Line($x, $dotY2, $x + 1, $dotY2);
    }
    $pdf->Cell(0, 12, $degree, 0, 1, 'C');

    // ==================== "with" ====================
    $pdf->SetFont('Times', 'I', 14);
    $pdf->Cell(0, 7, 'with', 0, 1, 'C');

    $pdf->Ln(1);

    // ==================== CLASS OF DEGREE (calligraphy + dotted line) ====================
    $pdf->SetFont('Calligrapher', '', 24);
    $classOfDegree = ucwords(strtolower($cert['class_of_degree']));
    $classY = $pdf->GetY();
    $dotY3 = $classY + 10;
    for ($x = $lineStart; $x < $lineEnd; $x += 2) {
        $pdf->Line($x, $dotY3, $x + 1, $dotY3);
    }
    $pdf->Cell(0, 12, $classOfDegree, 0, 1, 'C');

    // ==================== "in" ====================
    $pdf->SetFont('Times', 'I', 14);
    $pdf->Cell(0, 7, 'in', 0, 1, 'C');

    $pdf->Ln(1);

    // ==================== DEPARTMENT (calligraphy + dotted line) ====================
    $pdf->SetFont('Calligrapher', '', 24);
    $department = ucwords(strtolower($cert['department']));
    $deptY = $pdf->GetY();
    $dotY4 = $deptY + 10;
    for ($x = $lineStart; $x < $lineEnd; $x += 2) {
        $pdf->Line($x, $dotY4, $x + 1, $dotY4);
    }
    $pdf->Cell(0, 12, $department, 0, 1, 'C');

    $pdf->Ln(4);

    // ==================== GRADUATION DATE ====================
    $pdf->Ln(10);
    $pdf->SetFont('Times', 'I', 14);
    $pdf->SetTextColor(0, 0, 0);
    $gradDate = 'Given this ' . $cert['graduation_date'] . '.';
    $pdf->Cell(0, 7, $gradDate, 0, 1, 'C');
}

$pdf->Output();
die;
