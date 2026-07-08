<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';

set_time_limit(0);
date_default_timezone_set("Africa/Lagos");

$pdf = new exFPDF();
$pdf->SetMargins(20, 15, 20);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

foreach ($certificates as $cert) {
    $pdf->AddPage();

    $name = strtoupper($cert['student_name']);
    $id = $cert['username'] ?? '';
    $degree = $cert['degree'];
    $class = $cert['class_of_degree'];
    $dept = $cert['department'];
    $gradDate = $cert['graduation_date'];

    $pdf->SetLeftMargin(3);
    $headerTable = new easyTable($pdf, '{30, 174}', 'width:204; border-color:black; font-size:10; border:0; paddingY:1;');
    $pdf -> Image('uploads/logo-white.png',14,12,24,0,'PNG');
    $headerTable->easyCell('', 'rowspan:3;');
    $headerTable->easyCell('UNIVERSITY OF MAIDUGURI', 'font-style:B; font-size:30; align:R; border:0; font-color:#87CEEB;');
    $headerTable->printRow();
    $headerTable->easyCell('(Office of the Registrar)', 'font-style:B; font-size:14; align:C; border:0;');
    $headerTable->printRow();
    $headerTable->easyCell('DIRECTORATE OF SENATE AND ACADEMIC MATTERS', 'font-style:B; font-size:14; align:C; border:0;');
    $headerTable->printRow();
    $headerTable->endTable(2);

    $pdf->SetMargins(20, 15, 20);
    $pdf->SetX(20);
    $table = new easyTable($pdf, '{85, 85}', 'width:170; border-color:black; font-size:10; border:1; paddingY:1;');
    $pdf->SetLineWidth(1.5);
    $y = 45;
    $pdf->Line(0, $y, 250, $y);
    $pdf->Ln(20);
    $pdf->Ln(3);
    $table->easyCell('<b>Ref: R/ACA.267/Vol. XI</b>', 'font-size:12; align:L;border:0;');
    $table->easyCell('<b>Date:</b> ' . $gradDate, 'font-size:12; align:R;border:0;');
    $table->printRow();
    $pdf->Ln(10);
    $table->easyCell($name, 'font-size:12; border:0;');
    $table->printRow();
    $pdf->Ln(10);
    $table->easyCell('<b>DEGREE STATEMENT OF RESULT: CANDIDATE NO:</b> ' . $id, ' colspan:2; font-size:12; border:0;');
    $table->printRow();
    $table->endTable(2);

    $pdf->SetFont('FontUTF8', '', 12);
    $pdf->Ln(10);
    $pdf->SetX(20);
    $pdf->Write(7, 'I am pleased to inform you that you have been awarded the Degree of ');
    $pdf->SetFont('FontUTF8', 'B', 12);
    $pdf->Write(7, $degree);
    $pdf->SetFont('FontUTF8', '', 12);
    $pdf->Write(7, ' with ');
    $pdf->SetFont('FontUTF8', 'B', 12);
    $pdf->Write(7, $class);
    $pdf->SetFont('FontUTF8', '', 12);
    $pdf->Write(7, ' by the Senate of this University.');

    $pdf->Ln(12);
    $pdf->SetX(20);
    $pdf->MultiCell(170, 7, 'Certificates, Diplomas and Degrees will be formally awarded to all deserving students at the next Convocation Ceremony of the University.', 0, 'J');

    $pdf->Ln(5);
    $pdf->SetX(20);
    $pdf->MultiCell(170, 7, 'The issuing of the Degree Certificate itself will take place after the Convocation Ceremony. In the mean time, this letter is being issued to you in lieu of your degree certificate for purposes of employment or admission.', 0, 'J');

    $pdf->Ln(5);
    $pdf->SetX(20);
    $pdf->MultiCell(170, 7, 'Transcripts of Academic Records are issued to institutions and organizations on request and payment of the appropriate fees.', 0, 'J');

    $pdf->Ln(30);
    $pdf->Image('uploads/sign.jpg', 20, $pdf->GetY(), 20);
    $pdf->Ln(12);
    $pdf->SetX(20);
    $pdf->SetFont('FontUTF8', 'B', 12);
    $pdf->Cell(170, 7, 'FILIBUS YAMTA MSHELIA', 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->SetX(20);
    $pdf->SetFont('FontUTF8', '', 12);
    $pdf->Cell(170, 7, 'For: REGISTRAR', 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->SetX(20);
    $pdf->Cell(170, 7, "CC: Student's Record File", 0, 1, 'L');
}

$pdf->Output();
die;
