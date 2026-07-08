<?php
use Illuminate\Support\Facades\DB;

include('../resources/views/pdf/pdf_mc_table.php');
include '../resources/views/pdf/exfpdf.php';
include '../resources/views/pdf/easyTable.php';

set_time_limit(300);
date_default_timezone_set("Africa/Lagos");

class RecruitmentPDF extends exFPDF
{
    function Header()
    {
        // Minimal header - just filter info
        $this->SetY(3);
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, 'Applicants Export - ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $this->Ln(1);
    }

    function Footer()
    {
        // Simple footer
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create PDF instance with LANDSCAPE orientation
$pdf = new RecruitmentPDF();
$pdf->AliasNbPages();
$pdf->AddPage('L'); // LANDSCAPE!
$pdf->SetMargins(1, 15, 1); // 1mm sides, 15mm top for better spacing

// Add filters if any - minimal spacing
if (!empty($filters)) {
    $filterText = '';
    if (!empty($filters['department'])) $filterText .= 'Dept: ' . $filters['department'] . ' | ';
    if (!empty($filters['post_applied'])) $filterText .= 'Post: ' . $filters['post_applied'] . ' | ';
    if (!empty($filters['state'])) $filterText .= 'State: ' . $filters['state'] . ' | ';
    if (!empty($filters['lga'])) $filterText .= 'LGA: ' . $filters['lga'] . ' | ';
    if (!empty($filters['gender'])) $filterText .= 'Gender: ' . $filters['gender'] . ' | ';
    if (!empty($filters['status'])) $filterText .= 'Status: ' . ($filters['status'] === 'NEW' ? 'Submitted' : $filters['status']);
    
    if ($filterText) {
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->Cell(0, 2, rtrim($filterText, ' | '), 0, 1, 'L');
        $pdf->Ln(1);
    }
}

// Check if there are applicants
if (empty($applicants)) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'No applicants found matching the selected criteria.', 0, 1, 'C');
} else {
    // Create table for LANDSCAPE - ABSOLUTE MAXIMUM WIDTH (297mm - 2mm margins = 295mm)
    // Columns: S/NO(10) NAME(50) GENDER(16) DOB(18) STATE(20) LGA(20) QUALIFICATION(42) POST(42) DEPARTMENT(42) GSM(22) = 295
    $table = new easyTable($pdf, '{10, 47, 16, 18, 20, 20, 42, 42, 42, 22}', 'width:295; border-color:black; font-size:7; border:1; paddingY:1; paddingX:1;');
    
    // Add headers
    $table->easyCell('S/NO', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('NAME', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('GENDER', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DATE OF BIRTH', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('STATE', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('LGA', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('QUALIFICATION', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('POST APPLIED', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DEPARTMENT', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('GSM NO', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->printRow();
    
    // Add data rows
    foreach ($applicants as $applicant) {
        $table->easyCell($applicant['sno'], 'align:C;');
        $table->easyCell($applicant['name'], 'align:L;');
        $table->easyCell($applicant['gender'], 'align:C;');
        $table->easyCell($applicant['date_of_birth'], 'align:C;');
        $table->easyCell($applicant['state'], 'align:L;');
        $table->easyCell($applicant['lga'], 'align:L;');
        $table->easyCell($applicant['qualification'], 'align:L; font-size:6;');
        $table->easyCell($applicant['post_applied'], 'align:L; font-size:6;');
        $table->easyCell($applicant['department'], 'align:L; font-size:6;');
        $table->easyCell($applicant['gsm_no'], 'align:C;');
        $table->printRow();
    }
    
    // Add total row in table with colspan
    $table->easyCell('Total: ' . count($applicants) . ' applicants', 'colspan:10; font-style:B; font-size:7; bgcolor:#f9f9f9; border:1;');
    $table->printRow();
    
    $table->endTable(2);
}

// Output PDF
$pdf->Output('D', 'applicants_' . date('Y-m-d_H-i-s') . '.pdf');
?>
