<html>
<head>
<title>Harvard Card application processing</title>
<link rel="stylesheet" type="text/css" href="../hcl.css">
</head>
<body>

<?php
//if ($_SERVER[REMOTE_ADDR] != '18.7.29.240') {
//   echo "$_SERVER[REMOTE_ADDR]";
//   echo "<p>Sorry, proxy server access only.</p>";
//   echo "</body>";
//   echo "</html>";
//   exit (0);
//}

if (isset($_GET['app_number']))
  $app_number = $_GET['app_number'];
else
  die('Error - no application number provided');

if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('Failed to connect to HCL database: ' . mysql_error());

if (isset($_GET['decision']) || isset($_GET['decisionmaker']) || isset($_GET['expirydate']) || isset($_GET['decisionnote'])) {

if (isset($_GET['decision']))
  $decision = mysql_real_escape_string($_GET['decision']);
else
  $decision = '';
if (isset($_GET['decisionmaker']))
  $decisionmaker = mysql_real_escape_string($_GET['decisionmaker']);
else
  $decisionmaker = '';
if (isset($_GET['expirydate']))
  $expirydate = mysql_real_escape_string($_GET['expirydate']);
else
  $expirydate = '';
if (isset($_GET['decisionnote']))
  $decisionnote = mysql_real_escape_string($_GET['decisionnote']);
else
  $decisionnote = '';

$decisiondate = date('Y-m-d');

$sql = "update hcl.applications set decision = '$decision', decisionmaker = '$decisionmaker', expirydate = '$expirydate', decisiondate = '$decisiondate', decisionnote = '$decisionnote' where app_number = $app_number";
if (!mysql_query($sql))
  die('Failed to update table');
else
  echo 'Successfully updated application status.<br>';
}

?>

<a href="http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/listapps.php">Back to application list</a><br>
<a href="http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/viewapp.php?app_number=<?php echo $app_number; ?>">Back to application detail view</a><br>

<?php
$sql = "select * from hcl.applications where app_number = $app_number";
$resource = mysql_query($sql);
$app_data = mysql_fetch_assoc($resource);
if (!$app_data)
  die("Error - Nothing found for application number $app_number");
if ($app_data['decision'] != 'approved')
  die();
?>

<form method="post" action="../makepdf.php">
<fieldset>
Use template <select name="pdfname">
<?php
$sql = 'select pdfname from hcl.templates';
$resource = mysql_query($sql);
while ($resultrow = mysql_fetch_assoc($resource))
  echo '<option value="' . $resultrow['pdfname'] . '">' . $resultrow['pdfname'] . '</option>';
?>
</select>
<input type="hidden" name="app_number" value="<?php echo $app_number; ?>"><br>
Source: <input type="text" readonly name="source" value="<?php echo $app_data['source']; ?>"><br>
Name: <input type="text" readonly name="name" value="<?php echo $app_data['fullname']; ?>"><br>
Email: <input type="text" readonly name="email" value="<?php echo $app_data['email']; ?>"><br>
Status: <input type="text" readonly name="status" value="<?php echo $app_data['status']; ?>"><br>
Department: <input type="text" readonly name="department" value="<?php if ($app_data['department2'] != '') echo $app_data['department2']; else echo $app_data['department1']; ?>"><br>
Approved on: <input type="text" readonly name="date" value="<?php echo $app_data['decisiondate']; ?>"><br>
Approved by: <input type="text" readonly name="approver" value="<?php echo $app_data['decisionmaker']; ?>"><br>
Expires: <input type="text" readonly name="expiry" value="<?php echo $app_data['expirydate']; ?>"><br>
<input type="submit" value="Generate PDF">
</fieldset>
</form>

</body>
</html>
