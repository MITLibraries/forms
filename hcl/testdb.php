<?php

$warehousedata = '';

// connect to warehouse
$warehouse = oci_connect('libuser','tmp3216',
			 '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=warehouse.mit.edu)(PORT=1521)))(CONNECT_DATA=(SID=DWRHS)))');
if (!$warehouse) {
  $warehousedata .= "Error - Unable to connect to Data Warehouse for verification.\n";
  echo "Failed WH";
 }

if ($warehouse) {
  echo "Connected WH";
  oci_execute(oci_parse($warehouse, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD'"), OCI_DEFAULT);

  // search warehouse for ID and Kerberos name, if not found result will be set to false
  $sql = "select * from library_student where mit_id = '$mit_id'";
  $statement = oci_parse($warehouse, $sql);
  oci_execute($statement, OCI_DEFAULT);
  $studentIDresults = oci_fetch_assoc($statement);
 }

$alephdata = '';

// connect to aleph (currently using test server)
$aleph = oci_connect('script_user','tuespref',
		     '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=library.mit.edu)(PORT=1521)))(CONNECT_DATA=(SID=ALEPH20)))');

if (!$aleph) {
  $alephdata .= "Error - Unable to connect to Aleph for verification.\n";
  echo "Failed Aleph";
 }

if ($aleph) {
  echo "Connected Aleph";
    }

?>
