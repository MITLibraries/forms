<?php
class Warehouse {

  private $warehouse;

  public function __construct() {

    $db = require(dirname(__FILE__) . '/warehouse-connection.php');

    // connect to warehouse
    $this->warehouse = oci_connect($db['user'],$db['db'],$db['cst']);

    if (!$this->warehouse) {
      // Need to do something if the connection fails
    }

  }

  public function __destruct() {
    // disconnect from warehouse
    oci_close($this->warehouse);
  }

  public function lookupDisplayName ($email) {
    $sql = "SELECT FIRST_NAME, MIDDLE_NAME, LAST_NAME FROM EMPLOYEE_DIRECTORY WHERE EMAIL_ADDRESS = :e1";
    $statement = oci_parse($this->warehouse, $sql);
    oci_bind_by_name($statement, ':e1', strtoupper($email));
    oci_execute($statement, OCI_DEFAULT);
    $rs = oci_fetch_assoc($statement);
    $name = $rs['FIRST_NAME'] . ' ' . $rs['MIDDLE_NAME'] . ' ' . $rs['LAST_NAME'];
    return $name;
  }

}

/* Implementation example
$warehouse = new Warehouse();
$name = $warehouse->lookupDisplayName('mjbernha@mit.edu');
*/

?>
