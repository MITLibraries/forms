<?php
if ($_SERVER[REMOTE_ADDR] != '18.7.29.240') {
   echo "<html>";
   echo "<head>";
   echo "<title>Harvard Card application processing</title>";
   echo '<link rel="stylesheet" type="text/css" href="../hcl.css">';
   echo "<p>Sorry, proxy server access only.</p>";
   echo "</body>";
   echo "</html>";
   exit (0);
}

header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="hcl_apps.csv"');

$applist = split(',',$_GET['numbers']);

//check for empty list

if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('Failed to connect to HCL database: ' . mysql_error());

$sql = 'select * from hcl.applications where ';

foreach ($applist as $app)
  $sql .= "app_number = $app or ";

$sql = substr($sql,0,-4);

echo '"Application date","MIT ID#","Kerberos","Name","Email","Address",';
echo '"Phone","Status","Department","Ending date","Application comment",';
echo '"Decision","Decision date","Staff member","Decision note","Expiry"';
echo "\n";

if (!$resource = mysql_query($sql))
  die('Failed to run SQL query: ' . mysql_error());

while ($resultrow = mysql_fetch_assoc($resource)) {
  foreach ($resultrow as $colname => $value)
    $resultrow[$colname] = str_replace('"','""',$value);
  echo '"' . $resultrow['appdate'] . '",';
  echo '"' . $resultrow['mit_id'] . '",';
  echo '"' . $resultrow['kerbname'] . '",';
  echo '"' . $resultrow['fullname'] . '",';
  echo '"' . $resultrow['email'] . '",';
  echo '"' . $resultrow['address'] . '",';
  echo '"' . $resultrow['phone'] . '",';
  echo '"' . $resultrow['status'] . '",';
  if ($resultrow['department2'] != '')
    echo '"' . $resultrow['department2'] . '",';
  else
    echo '"' . $resultrow['department1'] . '",';
  echo '"' . $resultrow['endingdate'] . '",';
  echo '"' . $resultrow['comment'] . '",';
  echo '"' . $resultrow['decision'] . '",';
  echo '"' . $resultrow['decisiondate'] . '",';
  echo '"' . $resultrow['decisionmaker'] . '",';
  echo '"' . $resultrow['decisionnote'] . '",';
  echo '"' . $resultrow['expirydate'] . '"';
  echo "\n";
}
?>
