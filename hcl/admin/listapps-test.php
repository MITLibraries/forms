<html>
<head>
<title>Harvard Card application processing</title>
<link rel="stylesheet" type="text/css" href="../hcl.css">
<script type="text/javascript" src="../jquery-1.2.6.pack.js"></script>
<script type="text/javascript" src="listapps.js"></script>
</head>
<body>

<?php
if ($_SERVER[REMOTE_ADDR] != '18.51.1.222') {
   echo "$_SERVER[REMOTE_ADDR]";
   echo "<p>Sorry, proxy server access only.</p>";
   echo "</body>";
   echo "</html>";
   exit (0);
}
?>

<table>
<thead>
  <th colspan="2"></th>
  <th>Application date</th>
  <th>Applicant</th>
  <th>Status</th>
  <th>Department</th>
  <th>Decision</th>
</thead>
<tbody>

<?php
if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('Failed to connect to HCL database: ' . mysql_error());

$sql = 'select * from hcl.applications order by app_number desc';
$resource = mysql_query($sql);

while ($resultrow = mysql_fetch_assoc($resource)) {
  echo '<tr>';
  echo '<td>';
  echo '<input type="checkbox" class="appnumber" id="' . $resultrow['app_number'] . '">';
  echo '</td>';
  echo '<td>';
  echo '<a href="http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/viewapp.php?app_number=' . $resultrow['app_number'] . '">View/Process</a></td>';
  echo '<td>' . $resultrow['appdate'] . '</td>';
  echo '<td>' . $resultrow['fullname'] . '</td>';
  echo '<td>' . $resultrow['status'] . '</td>';
  if ($resultrow['department2'] != '')
    echo '<td>' . $resultrow['department2'] . '</td>';
  else
    echo '<td>' . $resultrow['department1'] . '</td>';
  switch ($resultrow['decision']) {
    case 'approved':
      echo '<td class="green">';
      break;
    case 'denied':
      echo '<td class="warning">';
      break;
    default:
      echo '<td>';
  }
  if($resultrow['decision'] != '')
  {
      echo $resultrow['decision'];
      if ($resultrow['decision'] != 'pending')
      echo ' by ' . $resultrow['decisionmaker'] . ' on ' . $resultrow['decisiondate'];
      if ($resultrow['decision'] == 'approved')
      echo ' (<a href="http://libproxy.mit.edu/login?url=http://libraries.mit.edu/hcl/admin/processapp.php?app_number=' . $resultrow['app_number'] . '">make PDF</a>)';
  }
  echo '</td>';
  echo '</tr>';
}

?>

</tbody>
</table>

Explanatory text and more controls go here.<br>
<button onclick="getcsv()">Export marked records to .csv</button><br>
<button onclick="deleteapps()">Delete marked records</button><br>
<!-- <button onclick="markall()">Mark all records</button><br> -->
<a href="../index.html">Application form</a><br>

Testing some counts.<br>
<?php
if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('Failed to connect to HCL database: ' . mysql_error());

$sql = 'select count(*) from hcl.applications';
$resource = mysql_query($sql);

while ($resultrow = mysql_fetch_row($resource)) {
echo 'Total count: ' . $resultrow[0] . '<br>';
}

$sql = "select count(*) from hcl.applications where decision='approved'";
$resource = mysql_query($sql);

while ($resultrow = mysql_fetch_row($resource)) {
echo 'Total approved: ' . $resultrow[0] . '<br>';
}

$sql = "select department1, status, count(*) from hcl.applications where decision='approved' group by department1, status";
$resource = mysql_query($sql);

echo 'Approved: <br>';
echo '<table>';

while ($resultrow = mysql_fetch_array($resource, MYSQL_NUM)) {
   echo '<tr>';
   foreach ($resultrow as $attribute)
   echo '<td>' . $attribute . '</td>';
   echo '</tr>';
}
echo '</table>';

$sql = "select year(decisiondate), month(decisiondate), status, count(*) from hcl.applications where decision='approved' group by year(decisiondate), month(decisiondate), status";
$resource = mysql_query($sql);

echo 'Approved: <br>';
echo '<table>';
echo '<tr><td>Year</td><td>Month</td><td>Patron Status</td><td>Count</td></tr>';

while ($resultrow = mysql_fetch_array($resource, MYSQL_NUM)) {
   echo '<tr>';
   foreach ($resultrow as $attribute)
   echo '<td>' . $attribute . '</td>';
   echo '</tr>';
}
echo '</table>';

$sql = "select year(decisiondate), month(decisiondate), decision, decisionmaker, count(*) from hcl.applications group by year(decisiondate), month(decisiondate), decision, decisionmaker";
$resource = mysql_query($sql);

echo ': <br>';
echo '<table>';
echo '<tr><td>Year</td><td>Month</td><td>App Status</td><td></td><td>Count</td></tr>'\
;

while ($resultrow = mysql_fetch_array($resource, MYSQL_NUM)) {
   echo '<tr>';
   foreach ($resultrow as $attribute)
   echo '<td>' . $attribute . '</td>';
   echo '</tr>';
}
echo '</table>';

?>

</body>
</html>
