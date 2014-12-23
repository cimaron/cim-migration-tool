<?php


/**
 * XML Stream Processing Class
 */
class XmlStreamReader {

	/**
	 * @var   resource
	 */
	protected $fh = NULL;

	/**
	 * @var   string
	 */
	public $name = '';

	/**
	 * @var   string
	 */
	protected $path = '';

	/**
	 * @var   string
	 */
	protected $buffer = '';

	/**
	 * @var   function
	 */
	protected $hook = '';

	/**
	 * Constructor
	 *
	 * @param   string   $path         File to read
	 * @param   string   $searchnode   Node names to be processed
	 * @param   string   $hook         Function to be called on node processing
	 */
	public function __construct($path, $searchnode = '', $hook = '') {

		if (file_exists($path)) {
			$this->path = $path;
			$this->fh = fopen($this->path, "r");
		}

		if (!$searchnode) {
			$searchnode = preg_replace('#\.[^\.]+#', '', basename($path));
		}

		$this->name = $searchnode;
		$this->hook = $hook;
	}

	/**
	 * Process nodes
	 */
	public function process() {

		$startnode = '<' . $this->name . '>';
		
		$endnode = '</' . $this->name . '>';

		$size = filesize($this->path);

		while (!feof($this->fh)) {

			//read more
			while (!feof($this->fh) && strpos($this->buffer, $endnode) === false) {
				$this->buffer .= fread($this->fh, 4096);		
			}
		
			$startpos = strpos($this->buffer, $startnode);
			$endpos = strpos($this->buffer, $endnode) + strlen($endnode);

			if ($startpos === false || $endpos === false || $startpos > $endpos) {
				//throw error?
				return false;
			}

			$nodestr = substr($this->buffer, $startpos, $endpos - $startpos);
			
			$this->buffer = substr($this->buffer, $endpos);

			$node = simplexml_load_string($nodestr);

			if ($this->hook) {
				call_user_func_array($this->hook, array($this, $node, ftell($this->fh), $size));
			}
		}

		return true;
	}

}


