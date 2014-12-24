<p>Hello</p>
<?php

date_default_timezone_set('America/New_York');

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require 'warehouse.php';

$warehouse = new Warehouse();

$name = $warehouse->lookupDisplayName('mjbernha@mit.edu');
echo 'Lookup Display Name: ' . $name . '<br>';
?>
<p>World</p>
