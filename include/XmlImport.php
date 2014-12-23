<?php

require_once dirname(__FILE__) . '/XmlStreamReader.php';


/**
 * XmlImport Class
 */
abstract class XmlImport extends Import {

	/**
	 * @var   string
	 */
	protected $path = '';

	/**
	 * @var   XmlStreamReader
	 */
	protected $reader = NULL;

	/**
	 * @var   int
	 */
	public $counter = 0;
	protected $last = 0;

	/**
	 * Constructor
	 *
	 * @param   string   $path   Import file path
	 */
	public function __construct($path, $table = 'tmp_Import', $nodename = '') {
		parent::__construct($table);
		$this->path = $path;
		$this->table = $table;
		$this->reader = new XmlStreamReader($this->path, $nodename, array($this, 'process_record'));
	}
	
	public function getTable() {
		return $this->table;
	}

	/**
	 * Insert an xml node record
	 *
	 * @param   object   $parser   XmlParser
	 * @param   object   $node     SimpleXmlElement Node
	 * @param   int      $pos      Input file position
	 * @param   int      $size     Input file size
	 */
	public function process_record($parser, $node, $pos, $size) {

		global $database;
		$data = array();

		$this->before_process_record($node, $data);

		$database->insertRow($this->table, $data);

		$this->after_process_record($node, $data);

		$now = microtime(true);
		if (($now - $this->last) > .5) {
			self::writeLog("Importing " . basename($this->path), $pos, $size);
			$count = 0;
			$this->last = $now;
		}

		$this->counter++;
	}

	/**
	 * Run import
	 */
	public function run() {

		$this->before_run();

		$result = $this->reader->process();

		$this->after_run();

		return $result;
	}

	public function before_run() {
		set_time_limit(0);
		global $database;
		$database->emptyTable($this->table);
	}

	public function before_process_record(&$node, &$data) {
		foreach ($node as $k => $v) {
			$data[$k] = (string)$v;
		}
	}

	public function after_process_record(&$node, &$data) {
	}

	public function after_run() {
		$size = filesize($this->path);
		self::writeLog("Importing " . basename($this->path), $size, $size);
	}

}

