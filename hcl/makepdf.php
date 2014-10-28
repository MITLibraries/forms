<?php
require_once('fpdf/fpdi.php');

$pdfname = $_POST['pdfname'];
$fullname = $_POST['name'];
$email = $_POST['email'];
$status = $_POST['status'];
$department = $_POST['department'];
$date = $_POST['date'];
$approver = $_POST['approver'];
$expiry = $_POST['expiry'];

$pdf =& new FPDI();
$pdf->AddPage();
$pdf->setSourceFile($pdfname);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx, 0, 0);
$pdf->SetFont('Arial'); 
$pdf->SetTextColor(0,0,0); 

//here we should look up coordinates in hcl.templates and use those instead of these numbers
mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx');
$resource = mysql_query("select * from hcl.templates where pdfname = '$pdfname'");
$template = mysql_fetch_assoc($resource);

$pdf->SetXY($template['name_x'],$template['name_y']);
$pdf->Write(0,$fullname);
$pdf->SetXY($template['email_x'],$template['email_y']);
$pdf->Write(0,$email);
$pdf->SetXY($template['status_x'],$template['status_y']);
$pdf->Write(0,$status);
$pdf->SetXY($template['department_x'],$template['department_y']);
$pdf->Write(0,$department);
$pdf->SetXY($template['date_x'],$template['date_y']);
$pdf->Write(0,$date);
$pdf->SetXY($template['approver_x'],$template['approver_y']);
$pdf->Write(0,$approver);
$pdf->SetXY($template['expiry_x'],$template['expiry_y']);
$pdf->Write(0,$expiry);

$newpdfname = split(',',$fullname);
$newpdfname = basename($pdfname,'.pdf') . '-' . $newpdfname[0] . '.pdf';
$pdf->Output($newpdfname, 'D');
?>
