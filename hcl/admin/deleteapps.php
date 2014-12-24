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

$applist = split(',',$_GET['numbers']);

if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('false');

foreach ($applist as $app) {
  $sql = "delete from hcl.applications where app_number = $app";
  if (!mysql_query($sql))
    die('false');
}

echo 'true';
?>
