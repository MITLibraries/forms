<!Doctype HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"
               "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
<title>Harvard Library Application:  MIT Libraries</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!-- Copyright (C) 2001 Massachusetts Institute of Technology-->
<link href="../css/simple2.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" text="#333333" link="#336699" vlink="#000033">
<!-- top stuff -->
<table width="717" border=0 cellpadding=0 cellspacing=0> 
  <tr><!-- graphics and links at top --> 
<td width=717> <map name="topgfxmap"> 
<area shape="rect" coords="9,6,23,17" href="#content" alt="Skip to content">
<area shape="rect" coords="54,7,78,19" href="#form" alt="Skip to form">
<area shape="rect" alt="MIT libraries" coords="176,2,298,20" href="../index.html"> 
<area shape="rect" alt="Site Index" coords="544,7,628,17" href="../site-index.html"> 
<area shape="rect" alt="Search" coords="631,7,693,17" href="../search-web.html">
</map> 
<img src="../img/topgfx.gif" width=717 height=25 border=0 usemap="#topgfxmap" alt="Skip to content | Skip to form | MIT Libraries | Site Index | Search"></td>
</tr> 
<tr><!-- white line --> <td width=717 bgcolor="#FFFFFF"> <img src="../img/spacer.gif" width=717 height=1 alt=""></td>
</tr> 
<tr><!-- blue band --> <td width=717 bgcolor="#669999"> <img src="../img/spacer.gif" width=717 height=6 alt=""></td>
</tr> 
<tr><!-- white line --> <td width=717 bgcolor="#FFFFFF"> <img src="../img/spacer.gif" width=717 height=1 alt=""></td>
</tr> 
<tr><!-- navigation --> <td width=717 bgcolor="#FFFFFF"> <a href="../search/index.html"><img
       src="../img/nav/nav_soc.gif" width=183 height=14 border=0
       alt="Search Our Collections |" ></a><img
       src="../img/spacer.gif" width=1 height=14
       alt=""><a href="../ask-us/index.html"><img
       src="../img/nav/nav_rh.gif" width=122 height=14 border=0
       alt="Ask Us |" ></a><img
       src="../img/spacer.gif" width=1 height=14
       alt=""><a href="../help/index.html"><img
       src="../img/nav/nav_sc.gif" width=144 height=14 border=0
       alt="Help Yourself |" ></a><img
       src="../img/spacer.gif" width=1 height=14
       alt=""><a href="../ordering/index.html"><img
       src="../img/nav/nav_bo.gif" width=174 height=14 border=0
       alt="Borrowing + Ordering |" ></a><img
       src="../img/spacer.gif" width=1 height=14
       alt=""><a href="../about/index.html"><img
       src="../img/nav/nav_au.gif" width=90 height=14 border=0
       alt="About Us" ></a><br> 
  <img src="../img/spacer.gif" width=717 height=10  alt="--"></td>
</tr> </table>
<table><tr><!-- header and white space --> <td width=717 height=16 bgcolor="#FFFFFF"> 
<img src="../img/spacer.gif" width=717 height=18 alt=""></td></tr> 
<tr><!-- libraries logo and main text --> 
<td width=715 bgcolor="#FFFFFF"> <table width="715" border=0 cellpadding=0 cellspacing=0> 
<tr> <td rowspan=2 align=left valign=top> <p><a href="../index.html"><img src="../img/level2/logo_on_white.gif" width=80 height=36 border=0 alt="MIT Libraries"></a></p>
  <p>&nbsp;</p>
  </td><td width=418 height=15> 
<img src="../img/spacer.gif" width=418 height=15 alt=""></td><td width=117 height=15> 
<img src="../img/spacer.gif" width=115 height=15 alt=""></td>
</tr> <tr> <td colspan="2" align=left valign=top> 

  <p>
<?php

  date_default_timezone_set('America/New_York');

//get post data - proxy server actually sends back get data.
$source = isset($_GET['source'])?$_GET['source']:'';
$mit_id = isset($_GET['mit_id'])?$_GET['mit_id']:'';
$kerbname = isset($_GET['kerbname'])?$_GET['kerbname']:'';
$fullname = isset($_GET['fullname'])?$_GET['fullname']:'';
$email = isset($_GET['email'])?$_GET['email']:'';
$address = isset($_GET['address'])?$_GET['address']:'';
$phone = isset($_GET['phone'])?$_GET['phone']:'';
$status = isset($_GET['status'])?$_GET['status']:'';
$department1 = isset($_GET['department'])?$_GET['department']:'';
$department2 = isset($_GET['other'])?$_GET['other']:'';
$endingdate = isset($_GET['graddate'])?$_GET['graddate']:'';
$comment = isset($_GET['comments'])?$_GET['comments']:'';

if( (strlen($mit_id) == 0)
  || (!ctype_digit($mit_id))
  || (strlen($kerbname) == 0)
  || (strlen($fullname) == 0)
  || (strlen($address) > 255)
  || (strlen($address) == 0)
  || (strlen($phone) == 0)
  || (strlen($department1) == 0) 
  || (strlen($department2) == 0)
  || (strlen($status) == 0) || ($status == 'default')
  || (strlen($comment) > 255)
  || (strlen($email) == 0)
  || (!preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i',$email))
  )
{

  echo "<h1><a name=\"content\"></a>Error</h1>\n";
  echo "<p>There appears to be a problem with the provided data.\n";
  echo "Please resubmit your application with correct input.</p>\n";
  echo "<p>\n";
}


$form_problem = false;	// flag to mark if something goes wrong
// form problems result in immediate error and does not submit
$data_problem = false;       // flag to mark if something goes wrong
// data problems result in non-approval and are submitted for review

//echo "<p>POST data" , $_GET['mit_id'] . "</p>\n";

if (strlen($mit_id) == 0)
{
  echo "<p>Error - MIT ID is required</p>\n";
  $form_problem = true;
}
else if (!ctype_digit($mit_id))
{
  echo "<p>Error - MIT ID '$mit_id' doesn't appear to be a number</p>\n";
  $form_problem = true;
}
if (strlen($kerbname) == 0)
{
  echo "<p>Error - Kerberos name is required</p>\n";
  $form_problem = true;
}
if (strlen($fullname) == 0)
{
  echo "<p>Error - Full name is required</p>\n";
  $form_problem = true;
}
if (strlen($address) > 255)
{
  echo "<p>Error - Address is too long; maximum is 255 characters</p>\n";
  $form_problem = true;
}
if (strlen($address) == 0)
{
  echo "<p>Error - Address is required</p>\n";
  $form_problem = true;
}
if (strlen($phone) == 0)
{
  echo "<p>Error - Phone number is required</p>\n";
  $form_problem = true;
}
if (strlen($department1) == 0)
{
  echo "<p>Error - Department is required</p>\n";
  $form_problem = true;
}
if (strlen($department2) == 0)
{
  echo "<p>Error - Lab, center, or field of research is required</p>\n";
  $form_problem = true;
}
if (strlen($status) == 0 || $status == 'default')
{
  echo "<p>Error - Status is required</p>\n";
  $form_problem = true;
}
if (strlen($comment) > 255)
{
  echo "<p>Error - Comment is too long; maximum is 255 characters</p>\n";
  $form_problem = true;
}
if (strlen($email) == 0)
{
  echo "<p>Error - Email address is required</p>\n";
  $form_problem = true;
} else if (!preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i',$email))
{
  echo "<p>Error - \'$email\' doesn't appear to be an email address</p>";
  $form_problem = true;
}

/* Build a formdata array */
$formdata = "Submission form: $source\n";
$formdata .= "Full name: $fullname\n";
$formdata .= "Kerberos: $kerbname\n";
$formdata .= "ID#: $mit_id\n";
$formdata .= "Email: $email\n";
$formdata .= "Address: $address\n";
$formdata .= "Phone: $phone\n";
$formdata .= "Status: $status\n";
$formdata .= "Department: $department1\n";
$formdata .= "Lab/center/research: $department2\n";
$formdata .= "Leaving date: $endingdate\n";
$formdata .= "Comments: $comment\n";



//check warehouse and aleph to see if this kerberos/id is valid for auto-approval
//if it is, then make pdf and email it to applicant
//email to monitoring address with form data, approval, and pdf regardless

require_once 'fpdf/fpdi.php';
require_once '/usr/share/php/Mail.php';
require_once '/usr/share/php/Mail/mime.php';

// make kerbname uppercase and escape any apostrophes (there shouldn't be apostrophes anyway, but there might be bad input)
$fixed_kerbname = str_replace("'","''",strtoupper($kerbname));

// warehouse lookups

$warehousedata = '';

  // connect to warehouse
  $warehouse = oci_connect('libuser','tmp3216',
			   '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=warehouse.mit.edu)(PORT=1521)))(CONNECT_DATA=(SID=DWRHS)))');

  oci_execute(oci_parse($warehouse, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD'"), OCI_DEFAULT);

  // search warehouse for ID and Kerberos name, if not found result will be set to false
    $sql = "select * from library_student where mit_id = '$mit_id'";
    $statement = oci_parse($warehouse, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $studentIDresults = oci_fetch_assoc($statement);

    $sql = "select * from library_employee where mit_id = '$mit_id'";
    $statement = oci_parse($warehouse, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $employeeIDresults = oci_fetch_assoc($statement);

    $sql = "select * from library_student where krb_name_uppercase = '$fixed_kerbname'";
    $statement = oci_parse($warehouse, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $studentKerbresults = oci_fetch_assoc($statement);

    $sql = "select * from library_employee where krb_name_uppercase = '$fixed_kerbname'";
    $statement = oci_parse($warehouse, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $employeeKerbresults = oci_fetch_assoc($statement);

  // disconnect from warehouse
  oci_close($warehouse);

  // processing results
    if (!($studentIDresults == $studentKerbresults && $employeeIDresults == $employeeKerbresults)) {
      $warehousedata .= "Error - This Kerberos name does not match this MIT ID number.\n";
      $data_problem = true;
    } else if (!($studentIDresults || $employeeIDresults)) { 
      $warehousedata .= "Error - No record matching this Kerberos name and MIT ID number was found.\n";
      $data_problem = true;
    } else {
      $studentresult = $studentIDresults;
      $employeeresult = $employeeIDresults;
    }

  if (!$data_problem) { // either $studentresult or $employeeresult will be matching the ID/Kerberos
    if ($status == 'GRAD' || $status == 'UG') // should be in $studentresult if $status is GRAD or UG
      if (!$studentresult) {
        $data_problem = true;
        $warehousedata .= "Error - Matching record was found in employee table, but should have been in student table.\n";
        $isStudent = false;
      } else
        $isStudent = true;
    else 
      if (!$employeeresult) {
        $data_problem = true;
        $warehousedata .= "Error - Matching record was found in student table, but should have been in employee table.\n";
        $isStudent = true;
      } else
        $isStudent = false;

    if ($isStudent) {
      $warehousedata .= "Name: {$studentresult['LAST_NAME']}, {$studentresult['FIRST_NAME']} {$studentresult['MIDDLE_NAME']}\n";
      $warehousedata .= "Email: {$studentresult['EMAIL_ADDRESS']}\n";
      $warehousedata .= "Home department: {$studentresult['HOME_DEPARTMENT']}\n";
      $warehousedata .= "Year: {$studentresult['STUDENT_YEAR']}\n";
      if ($status == 'GRAD' && $studentresult['STUDENT_YEAR'] != 'G') {
        $warehousedata .= "Error - Student year does not match declared graduate student status.\n";
        $data_problem = true;
      }
      if ($status == 'UG' && !in_array($studentresult['STUDENT_YEAR'], array('1','2','3','4'))) {
        $warehousedata .= "Error - Student year does not match declared undergraduate student status.\n";
        $data_problem = true;
      }
      if ($status == 'UG' && $source == 'Countway') {
        $warehousedata .= "Error - Student type is Undergrad, not eligible for Countway privileges.\n";
        $data_problem = true;
      }
      $fullname = $studentresult['LAST_NAME'] . ', ' . $studentresult['FIRST_NAME'] . ' ' . $studentresult['MIDDLE NAME'];
    } else {
      $warehousedata .= "Name: {$employeeresult['FULL_NAME']}\n";
      $warehousedata .= "Email: {$employeeresult['EMAIL_ADDRESS']}\n";
      $warehousedata .= "Status: {$employeeresult['LIBRARY_PERSON_TYPE']}\n";
      $warehousedata .= "Department: {$employeeresult['ORG_UNIT_TITLE']}\n";
      $warehousedata .= "End date: {$employeeresult['APPOINTMENT_END_DATE']}\n";
      if ($status == 'RS' && !in_array($employeeresult['LIBRARY_PERSON_TYPE_CODE'], array('23','24','25'))) {
        // '23' is Academic Staff, '24' is Visiting Researcher, '25' is Postdoc
        // Added Postdoc for auto-approval per Cassandra Fox 4-11-2011
        $warehousedata .= "Error - Status does not match declared research staff status.\n";
        $data_problem = true;
      }
      if ($status == 'FAC' && !in_array($employeeresult['LIBRARY_PERSON_TYPE_CODE'], array('11','12','13'))) {
        // '11' is MIT Faculty, '12' is Visiting Faculty, '13' is Retired Faculty
        $warehousedata .= "Error - Status does not match declared faculty status.\n";
        $data_problem = true;
      }
      $fullname = $employeeresult['FULL_NAME'];
    }
  }

// aleph lookups

$alephdata = '';

  // connect to aleph (currently using test server)
  $aleph = oci_connect('script_user','tuespref',
		       '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=library.mit.edu)(PORT=1521)))(CONNECT_DATA=(SID=ALEPH20)))');

  //get aleph id for this person
  $fixed_kerbname_z308 = $fixed_kerbname . '@MIT.EDU';
  $z308kerbname = substr_replace('04                    MIT50',$fixed_kerbname_z308,2,strlen($fixed_kerbname_z308));
  $z308mit_id = substr_replace('02                    MIT50',$mit_id,2,strlen($mit_id));

  $statement = oci_parse($aleph, "select z308_id from mit50.z308 where z308_rec_key = '$z308kerbname'");
  oci_execute($statement, OCI_DEFAULT);
  $results = oci_fetch_assoc($statement);
  if ($results)
    $aleph_id1 = $results['Z308_ID'];
  else
    $aleph_id1 = false;

  $statement = oci_parse($aleph, "select z308_id from mit50.z308 where z308_rec_key = '$z308mit_id'");
  oci_execute($statement, OCI_DEFAULT);
  $results = oci_fetch_assoc($statement);
  if ($results)
    $aleph_id2 = $results['Z308_ID'];
  else
    $aleph_id2 = false;

  if (!$aleph_id1)
    if (!$aleph_id2) {
      $alephdata .= "Error - There is no Aleph account associated with the provided Kerberos or ID.\n";
      $data_problem = true;
      $aleph_id = array();
    } else {
      $alephdata .= "Warning - There is an Aleph account associated with this ID number but not with this Kerberos name.\n";
      $aleph_id = array($aleph_id2);
  } else if (!$aleph_id2) {
      $alephdata .= "Warning - There is an Aleph account associated with this Kerberos name, but not with this ID number.\n";
      $aleph_id = array($aleph_id1);
    } else if ($aleph_id1 != $aleph_id2) {
        $alephdata .= "Error - The Kerberos name and ID number are associated with different accounts in Aleph. Listing both.\n";
        $data_problem = true;
        $aleph_id = array($aleph_id1, $aleph_id2);
      } else
        $aleph_id = array($aleph_id1);

  foreach ($aleph_id as $this_id)
  {
    $sql = "select * from mit50.z303 where z303_rec_key = '$this_id'";
    $statement = oci_parse($aleph, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $results = oci_fetch_assoc($statement);
    $lastupdated = strtotime($results['Z303_UPDATE_DATE']);

    $alephdata .= "Aleph ID: $this_id\n";
    $alephdata .= "Name: {$results['Z303_NAME_KEY']}\n";
    $alephdata .= "Last updated: " . date('l F jS, Y', $lastupdated) . "\n";

    //compare $lastupdated to current date, warn if more than one day old
    if (time() - $lastupdated > 86400)
    {
      $alephdata .= "Error - This account was last updated more than one day ago.\n";
    }

    //check for library account blocks
    // global patron record blocks
    $blocks = array(array('code' => $results['Z303_DELINQ_1'], 'note' => $results['Z303_DELINQ_N_1']),
		    array('code' => $results['Z303_DELINQ_2'], 'note' => $results['Z303_DELINQ_N_2']),
		    array('code' => $results['Z303_DELINQ_3'], 'note' => $results['Z303_DELINQ_N_3']));

    if ($blocks[0]['code'] == 0 && $blocks[1]['code'] == 0 && $blocks[2]['code'] == 0)
    {
      $alephdata .= "Global blocks: none\n";
    }
    else
    {
      foreach ($blocks as $this_block)
        if ($this_block['code'] != 0)
          $alephdata .= "Block: {$this_block['note']} \n";
      $data_problem = true;
    }
    // excessive fines blocks
    $sql = <<<EOL
select substr(Z31_REC_KEY,1,12)
from mit50.z31 
where substr(z31_rec_key,1,12) = '$this_id' 
and z31_status='O' 
group by substr(Z31_REC_KEY,1,12)
having 
sum(to_number(concat(translate(z31_credit_debit,'CD','-0'),translate(z31_sum,' ','0')))) >= 27000
EOL;
    $statement = oci_parse($aleph, $sql);
    oci_execute($statement, OCI_DEFAULT);
    $results = oci_fetch_assoc($statement);
    if ($results) 
    {
      $alephdata .= "Block: Fines are over \$270.\n";
      $data_problem = true;
    }
    else 
    {
      $alephdata .= "Fines blocks: none\n";
    }
    // overdue recall blocks
    $statement = oci_parse($aleph, "select z36_id from mit50.z36 where z36_id = '$this_id' and z36_letter_number > 0 and Z36_RECALL_DUE_DATE <> 0");
    oci_execute($statement, OCI_DEFAULT);
    $results = oci_fetch_assoc($statement);
    if ($results) 
    {
      $alephdata .= "Block: Overdue recalls found.\n";
      $data_problem = true;
    }
    else 
    {
      $alephdata .= "Overdue recall blocks: none\n";
    }
  }

  // disconnect from aleph
  oci_close($aleph);

if($form_problem == true)
{
//  echo "<p>Possible Problem:  $warehousedata</p>\n";
//  echo "<p>Possible Problem:  $alephdata</p>\n";
  print_footer();

  exit (0);
 } 

//if($data_problem == true)
//{
//  echo "<p>Possible Problem:  $warehousedata</p>\n";
//  echo "<p>Possible Problem:  $alephdata</p>\n";
//  print_footer();

//  exit (0);
// }

if ($source == 'Harvard')
{
  if ($status == 'RS' || $status == 'FAC')
  { 
    if (date('m') > 5) 
    {
       $expiry = (date('Y')+1) . '-08-31';
    }  else
    {
       $expiry = date('Y') . '-08-31';
    } 
  } else
  {
    if (date('m') > 5) {
      $expiry = (date('Y')+1) . '-05-31';
    } else
    {
      $expiry = date('Y') . '-05-31';
    }
  }
} else // for Countway
{
  if (date('m') > 5) {
    $expiry = (date('Y')+1) . '-06-30';
  } else
  {
    $expiry = date('Y') . '-06-30';
  }
}

// build pdf using FPDF, with FPDI to put a template pdf in the background
$pdf =& new FPDI();
$pdf->AddPage();
$pdf->setSourceFile('blankletterhead.pdf');
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx, 0, 0);
$pdf->SetFont('Arial'); // default is regular and 12pt
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(20,20); // start 20mm in and 20mm down from top left corner

$pdftext = file_get_contents($source . '_template.txt');
$pdftext = str_replace('[name]',$fullname,$pdftext);
$pdftext = str_replace('[email]',$email,$pdftext);
$pdftext = str_replace('[status]',$status,$pdftext);
$pdftext = str_replace('[department]',(strlen($department2) != 0)?$department2:$department1,$pdftext);
$pdftext = str_replace('[date]',date('Y-m-d'),$pdftext);
$pdftext = str_replace('[expiration]',$expiry,$pdftext);

$pdf->Write(5,$pdftext); // 5mm line height (12pt is ~4.25mm)

// set identifier to name/kerb/email
if (strlen($fullname) != 0)
{
  $identifier = split(',',$fullname);
  $identifier = $identifier[0];
}
else if (strlen($kerbname) != 0)
{
  $identifier = $kerbname;
}
else if (strlen($email) != 0)
{
  $identifier = $email;
}
else
{
  $identifier = 'unknown user';
}

// build and send emails with Mail_Mime and Mail PEAR packages
   $responseaddr = 'circulation@mit.edu'; // email address to notify of applications, and to use as reply address on approval email
// $responseaddr = 'orbitee@mit.edu';

// Only send this email to the patron if it was auto-approved.
if(!$data_problem)
{

  $body = file_get_contents($source . '_approval.txt');
  $body = str_replace('[name]',$fullname,$body);
  if ($source == 'Harvard' && $status == 'UG') {
    $body .= "\n Please note that borrowing privileges for undergraduates is a pilot service with the Harvard College Library offered for the 2010-11 academic year.  Undergraduate borrowing privileges do not extend to the Loeb Graduate School of Design Library, but on site access is permitted.\n";
  }

  $mime = new Mail_mime ();
  $mime->setTXTBody($body);
  $mime->addAttachment($pdf->Output("$source-$identifier.pdf",'S'), 'application/pdf', "$source-$identifier.pdf", false, 'base64');
  $mime->setFrom($responseaddr);
  $mime->setSubject($source . '-MIT-' . $identifier);
  if ($source == 'Countway') {
    $mime->addCc('circinfo@hms.harvard.edu');
//    $mime->addCc('cassfox@mit.edu');
  } else {
    $mime->addCc('wprivmit@fas.harvard.edu');
//    $mime->addCc('orbitee@mit.edu');
  }

  $body = $mime->get();
  $hdrs = $mime->headers();

  $mail =& Mail::factory('mail');
  $mail->send($email, $hdrs, $body);
}

// send notification email regardless

$body = 'Application submitted on ' . date('Y-m-d');
$body .= "\n\n-- Form data --\n" . $formdata;
$body .= "\n\n-- Warehouse data --\n" . $warehousedata;
$body .= "\n\n-- Aleph data --\n" . $alephdata;
$body .= "\n\n-- Process applications here: http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/listapps.php --\n" . $alephdata;

$mime = new Mail_mime ();
$mime->setTXTBody($body);
$mime->addAttachment($pdf->Output("$source-$identifier.pdf",'S'), 'application/pdf', "$source-$identifier.pdf", false, 'base64');
$mime->setFrom($responseaddr);
$mime->setSubject("$source access application for $identifier" . (!$data_problem?' Auto-approved':' Requires review'));
$mime->addCc('circulation@mit.edu');
//$mime->addCc('orbitee@mit.edu');

$body = $mime->get();
$hdrs = $mime->headers();

$mail =& Mail::factory('mail');
$mail->send($responseaddr, $hdrs, $body);

$decision =  (!$data_problem?'approved':'pending');
$decisionmaker = (!$data_problem?'AUTO':'');
$decisiondate = (!$data_problem?date('Y-m-d'):'');

mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx');
mysql_query("insert into hcl.applications(source, appdate, mit_id, kerbname, fullname, email, address, phone, status, department1, department2, endingdate, comment, decision, decisionmaker, decisiondate, expirydate) values('Harvard', CURDATE(), '$mit_id','$kerbname','$fullname','$email','$address','$phone','$status','$department1','$department2','$endingdate','$comment','$decision','$decisionmaker', '$decisiondate', '$expiry')") or die(mysql_error());

echo "<h1><a name=\"content\"></a>Thank You</h1>\n";
echo "<p>Your request for Harvard Library access has been sent. </p>\n";
echo "<p>You will get a response as soon as possible, and no later than 5:00 PM the\n";
echo "next business day.\n";

print_footer();

function print_footer()
{
  echo "If you have questions, please <a href=\"mailto:circulation@mit.edu\">Ask Us!</a> </p>\n";
//  echo "<p>    You will receive a copy of your request via email. <br>\n";
//  echo "(If you don't receive a copy, it's possible that your request didn't go through. In that case, please fill out the form again, or send email to <a href=\"mailto:webmaster@libraries.mit.edu\">webmaster@libraries.mit.edu</a>). </p>\n";
  echo "<p>&nbsp;</p>\n";
  echo "<hr noshade size=\"1\" align=\"LEFT\">\n";
  //  echo "<p><a href=\"http://libraries.mit.edu/about/contact.html\">Contact us</a> </p>\n";
  echo "<p>&nbsp;</p>\n";
  echo "</td>\n";
  echo "</tr> \n";
  echo "<tr> <td width=182 height=50><img src=\"../img/spacer.gif\" width=95 height=50> </td>\n";
  echo "<td width=418 height=50>\n";
  echo "<img src=\"../img/spacer.gif\" width=505 height=50></td>\n";
  echo "<td width=117 height=50>\n";
  echo "<img src=\"../img/spacer.gif\" width=115 height=50></td></tr> </table></td></tr> \n";
  echo "</table>\n";
  echo "<script type=\"text/javascript\">\n";
  echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
  echo "document.write(unescape(\"%3Cscript src=\'\" + gaJsHost + \"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E\"));\n";
  echo "</script>\n";
  echo "<script type=\"text/javascript\">\n";
  echo "var pageTracker = _gat._getTracker(\"UA-1760176-1\");\n";
  echo "pageTracker._initData();\n";
  echo "pageTracker._trackPageview();\n";
  echo "</script></body>\n";
  echo "</html>\n";
}

?>
