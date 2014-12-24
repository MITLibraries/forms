<?php
class DebugLog {

	private $startTime;

	public function __construct() {
		$this->startTime = microtime(true);
		date_default_timezone_set('America/New_York');
	}

	public function write ($msg) {
		$msg = $this->startTime . ' ' . filter_var($msg,FILTER_SANITIZE_STRING);
		error_log($msg);
	}
}

/* Implementation example
$log = new DebugLog();
$log->write('Testing...');
*/

?>