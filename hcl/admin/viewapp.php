<html>
<head>
<title>Harvard Card application processing</title>
<link rel="stylesheet" type="text/css" href="../hcl.css">
</head>
<body>

<a href="http://libraries.mit.edu/hcl/admin/listapps.php">Back to application list</a><br>

<?php
if ($_SERVER['REMOTE_ADDR'] != '18.7.29.240') {
   echo "<p>Sorry, proxy server access only.</p>";
   echo "</body>";
   echo "</html>";
   exit (0);
}
if (!isset($_GET['app_number']))
  die('Error - No application number provided');
else
  $app_number = $_GET['app_number'];

//connect to hcl database
if (!mysql_connect('libdb.mit.edu','hcl','54aH0FCe1xmx'))
  die('Failed to connect to HCL database: ' . mysql_error());

$sql = "select * from hcl.applications where app_number = $app_number";
$resource = mysql_query($sql);
$app_data = mysql_fetch_assoc($resource);
if (!$app_data)
  die("Error - Nothing found for application number $app_number");

//at this point, all data from the application is in $app_data
date_default_timezone_set('America/New_York');
?>
<table>
  <tbody>
    <tr>
      <td>Applicant Name:</td>
      <td><?php echo $app_data['fullname']; ?></td>
    </tr><tr>
      <td>Application Date:</td>
      <td><?php echo $app_data['appdate']; ?></td>
    </tr><tr>
      <td>Decision:</td>
      <td><?php
        switch ($app_data['decision']) {
          case 'approved':
            echo '<span class="green">' . $app_data['decision'] . '</span>';
            break;
          case 'denied':
            echo '<span class="warning">' . $app_data['decision'] . '</span>';
            break;
          default:
            echo $app_data['decision'];
        } ?></td>
    </tr><tr>
      <td>Applicant Status:</td>
      <td><?php echo $app_data['status']; ?></td>
    </tr><tr>
      <td>Associated Department:</td>
      <td><?php if ($app_data['department2'] != '') echo $app_data['department2']; else echo $app_data['department1']; ?></td>
    </tr><tr>
      <td><?php if ($app_data['status'] == 'GRAD') echo 'Expected Graduation Date:'; else echo 'Expected Termination Date:'; ?></td>
      <td><?php echo $app_data['endingdate']; ?></td>
    </tr><tr>
      <td>MIT ID Number:</td>
      <td><?php echo $app_data['mit_id']; ?></td>
    </tr><tr>
      <td>Kerberos Name:</td>
      <td><?php echo $app_data['kerbname']; ?></td>
    </tr><tr>
      <td>Email Address:</td>
      <td><a href="mailto:<?php echo $app_data['email']; ?>"><?php echo $app_data['email']; ?></a></td>
    </tr><tr>
      <td>Physical Address:</td>
      <td><pre><?php echo $app_data['address']; ?></pre></td>
    </tr><tr>
      <td>Phone Number:</td>
      <td><?php echo $app_data['phone']; ?></td>
    </tr>
  </tbody>
</table>
<p>Additional Comments:</p>
<pre><?php echo $app_data['comment']; ?></pre>
<br>

<p>Aleph Data:</p>

<?php
//connect to aleph
$aleph = oci_connect('script_user','tuespref',
		     '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=library.mit.edu)(PORT=1521)))(CONNECT_DATA=(SID=ALEPH22)))');

//get aleph id for this person
   $fixed_kerbname_z308 = strtoupper($app_data['kerbname']) . '@MIT.EDU';
   $z308kerbname = substr_replace('04                                                                                                                                                                                                                                                               MIT50',$fixed_kerbname_z308,2,strlen($fixed_kerbname_z308));
   $z308mit_id = substr_replace('02                                                                                                                                                                                                                                                               MIT50',$app_data['mit_id'],2,strlen($app_data['mit_id']));

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
    echo '<div class="warning">There is no Aleph account associated with this Kerberos/ID pair.</div>';
    $aleph_id = array();
  } else {
    echo '<div class="warning">There is an Aleph account associated with this ID number but not with this Kerberos name.</div>';
    $aleph_id = array($aleph_id2);
} else if (!$aleph_id2) {
    echo '<div class="warning">There is an Aleph account associated with this Kerberos name, but not with this ID number.</div>';
    $aleph_id = array($aleph_id1);
  } else if ($aleph_id1 != $aleph_id2) {
      echo '<div class="warning">This Kerberos name and ID number are associated with different accounts in Aleph. Looking at both.</div>';
      $aleph_id = array($aleph_id1, $aleph_id2);
    } else
      $aleph_id = array($aleph_id1);

foreach ($aleph_id as $this_id) {
  $sql = "select z303_update_date, z303_delinq_1, z303_delinq_n_1, z303_delinq_2, z303_delinq_n_2, z303_delinq_3, z303_delinq_n_3 from mit50.z303 where z303_rec_key = '$this_id'";
  $statement = oci_parse($aleph, $sql);
  oci_execute($statement, OCI_DEFAULT);
  $results = oci_fetch_assoc($statement);
  $lastupdated = strtotime($results['Z303_UPDATE_DATE']);

  echo "Aleph account with ID $this_id was last updated " . date('l F jS, Y', $lastupdated) . '<br>';

  //compare $lastupdated to current date, warn if more than one day old
  if (time() - $lastupdated > 86400)
    echo '<div class="warning">This account was last updated more than one day ago.</div>';

  //check for library account blocks
  $blocks = array(array('code' => $results['Z303_DELINQ_1'], 'note' => $results['Z303_DELINQ_N_1']),
		  array('code' => $results['Z303_DELINQ_2'], 'note' => $results['Z303_DELINQ_N_2']),
		  array('code' => $results['Z303_DELINQ_3'], 'note' => $results['Z303_DELINQ_N_3']));

  if ($blocks[0]['code'] == 0 && $blocks[1]['code'] == 0 && $blocks[2]['code'] == 0)
    echo "Aleph account with ID $this_id has no global blocks on it.<br>";
  else
    foreach ($blocks as $this_block)
      if ($this_block['code'] != 0)
        echo '<div class="warning">Aleph account with ID ' . $this_id . ' has a global block on it:<br><pre>' . $this_block['note'] . '</pre></div>';

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
      echo '<div class="warning">Aleph account with ID ' . $this_id . ' has fines over $270.</div>';
      $problem = true;
    }
    else 
    {
      echo "Aleph account with ID $this_id does not have excessive fines.<br>";
    }
    // overdue recall blocks
    $statement = oci_parse($aleph, "select z36_id from mit50.z36 where z36_id = '$this_id' and z36_letter_number > 0 and Z36_RECALL_DUE_DATE <> 0");
    oci_execute($statement, OCI_DEFAULT);
    $results = oci_fetch_assoc($statement);
    if ($results) 
    {
      echo '<div class="warning">Aleph account with ID ' . $this_id . ' has overdue recalls.</div>';
      $problem = true;
    }
    else 
    {
      echo "Aleph account with ID $this_id has no overdue recalled items.<br>";
    }
}
?>
<br><br>
<form action="http://libproxy.mit.edu:8080/form?qurl=http%3A%2F%2Flibraries.mit.edu%2Fhcl%2Fadmin%2Fprocessapp.php" method="post">
<input type="hidden" name="app_number" value="<?php echo $app_number; ?>">
<fieldset>
Decision: <select name="decision">
  <option value="approved"<?php if ($app_data['decision'] == 'approved') echo ' selected'; ?>>approved</option>
  <option value="denied"<?php if ($app_data['decision'] == 'denied') echo ' selected'; ?>>denied</option>
  <option value="pending"<?php if ($app_data['decision'] == 'pending') echo ' selected'; ?>>pending</option>
</select>
<br>
Processor's Initials: <input type="text" name="decisionmaker" size="20" maxlength="20" value="<?php echo $app_data['decisionmaker']; ?>">
<br>
Expiry Date(YYYY-MM-DD): <input type="text" name="expirydate" size="10" maxlength="10" value="<?php echo $app_data['expirydate']; ?>">
(May 31 for grad students, August 31 for others)
<br>
Notes:<br><textarea rows="3" cols="40" name="decisionnote"><?php echo $app_data['decisionnote']; ?></textarea><br>
<input type="submit" value="Process application">
</fieldset>
</form>

<?php
if ($app_data['decision'] == 'approved')
  echo '<a href="http://libraries.mit.edu/hcl/admin/processapp.php?app_number=' . $app_number . '">Skip to PDF generation</a>';

oci_close($aleph);
mysql_close();
?>

</body>
</html>
