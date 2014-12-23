<?php

//require_once dirname(__FILE__) . '/XmlStreamReader.php';
require_once dirname(__FILE__) . '/Progress.php';


/**
 * Impor Class
 */
abstract class Import {

	protected $log_name = "Importing";

	/**
	 * @var   string
	 */
	protected $table = '';
	
	/**
	 * Constructor
	 *
	 * @param   string   $path   Import file path
	 */
	public function __construct($table = 'tmp_Import') {
		$this->table = $table;
	}

	public function getTable() {
		return $this->table;
	}

	/**
	 * Run import
	 */
	public function run() {

		$this->before_run();

		//do something
		$result = $this->process();

		$this->after_run();

		return $result;
	}

	public function before_run() {
		set_time_limit(0);
		global $database;
	}

	public function before_process_record(&$data) {
	}

	public function after_process_record(&$data) {
	}

	public function after_run() {
	}

	protected function writeLog($increment = 1) {

		static $i = 0, $total, $last;

		if ($last === NULL) {
			$last = microtime(true);
			$total = $this->getTotalSteps();
		}

		$i += $increment;

		$now = microtime(true);
		if ($now - $last > .25 || $total <= $i) {
			$last = $now;
			Progress::writeLog($this->log_name, $i, $total, $_GET['type'] ? $_GET['type'] : 'text');
		}
	}

	abstract protected function getTotalSteps();

	protected function print_r($var) {

		echo '<div style="white-space: pre; font-family: monospace; max-height: 300px; overflow: auto;">' . htmlentities(print_r($var, true)) . '</div>';
	
	}
}

