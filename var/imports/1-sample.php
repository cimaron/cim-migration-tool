<?php

require_once BASEPATH . '/include/Import.php';

class ImportSample extends Import {

	protected $log_name = "Sample Import";

	public function getTotalSteps() {
		return 200;
	}

	public function before_run() {
		global $database;
		
		echo "This displays before running.<br />\n";
	}

	public function process() {
		global $database;

		$time = 10;
		$sleep = $time / $this->getTotalSteps();

		for ($i = 0; $i < $this->getTotalSteps(); $i++) {
			usleep($sleep * 1000 * 1000);
			$this->writeLog();
		}
	}

	public function after_run() {
		echo "This displays after running.<br />\n";
	}

}

