<!doctype html>
<html>
<head>
  <title>Testing MySQLi</title>
  </head>
  <body>
    <h1>This is a test</h1>
<?php

  $source = 'Harvard'; 
  echo 'Begin';
  $appdate = date();
//  echo (string) $appdate;
//  $appdate = $appdate->format('Y-m-d');
//  $appdate = '2013-03-01';
  $mit_id = '922132264';
  $kerbname = 'MJBERNHA';
  $fullname = 'Matthew Bernhardt';
  $email = 'mjbernha@mit.edu';
  $address = '178 Appleton Street';
  $phone = '614-440-1859';
  $status = 'RS';
  $department1 = 'Libraries';
  $department2 = 'Web developer for SDA';
  $endingdate = '';
  $comment = 'Im testing the form.';
  $decision = 'denied';
  $decisionmaker = 'not sure';
  $decisiondate = '2012-03-01';
  $expiry = '2012-03-01';

  $conn = mysqli_connect('libdb.mit.edu','hcl','54aH0FCe1xmx','hcl');

  if (mysqli_connect_errno()) {
    echo '<p>Connection failed: %s \n', mysqli_connect_error();
    exit();
  }

  // define sql statement
  $sql = "INSERT INTO hcl.applications_test ("
    ."source, appdate, mit_id, kerbname, fullname, email, address, phone, status, department1, department2, endingdate, comment, decision, decisionmaker, decisiondate, expirydate"
    .") VALUES ("
    ."?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

  echo $sql;
  // define statement
  $stmt = mysqli_prepare($conn, $sql);

  // bind sql and parameters into statement
  mysqli_stmt_bind_param($stmt, 'sssssssssssssssss', $source, $appdate, $mit_id, $kerbname, $fullname, $email, $address, $phone, $status, $department1, $department2, $endingdate, $comment, $decision, $decisionmaker, $decisiondate, $expiry);

  // http://php.net/manual/en/mysqli-stmt.bind-param.php
  // demonstration code has value definitions after the statement - but ours are already set?

  // execute
  mysqli_stmt_execute($stmt);

  // discard statement
  mysqli_stmt_close($stmt);

  mysqli_close($conn);

?>
<p>Success!</p>
</body>
</html>