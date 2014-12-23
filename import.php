<?php
define('BASEPATH', dirname(__FILE__));

if (file_exists(dirname(__FILE__) . '/../configuration.php')) {

	/**
	 * Joomla Setup (necessary for advanced DB routines, nested set, etc.)
	 */
	define('_JEXEC', 1);
	define('DS', DIRECTORY_SEPARATOR);
	
	ini_set('display_errors', 1); 
	error_reporting(E_ALL);
	
	if (!defined('_JDEFINES')) {
		define('JPATH_BASE', realpath(dirname(__FILE__).'/..'));
		require_once JPATH_BASE.'/includes/defines.php';
	}
	
	require_once JPATH_BASE.'/includes/framework.php';
	$session = JFactory::getSession();
	JError::$legacy = false;

	$db = JFactory::getDbo();
	$db->setDebug(0);
	
}

require_once BASEPATH . '/configuration.php';
require_once BASEPATH . '/include/Database.php';

$database = new Database($config->user, $config->password, $config->db, $config->host, $config->dbprefix);

$imports = isset($_REQUEST['import']) ? $_REQUEST['import'] : array();

$result = array(
	'error' => array(),
	'warning' => array(),
	'message' => array(),
);

foreach ($imports as $import) {
	
	preg_match('#^(\d+)\-(.*)$#', $import, $matches);
	list($full, $number, $name) = $matches;
	
	$path = BASEPATH . '/var/imports/' . $full . '.php';	
	if (!file_exists($path)) {
		$result['error'][] = $full . ': File does not exist';
		continue;
	}

	require_once $path;

	$class = 'Import' . $name;
	if (!class_exists($class)) {
		$result['error'][] = $class . ': Class does not exist';
		continue;
	}

	$task = new $class(BASEPATH . '/data');
	ob_start();
	$task->run();
	$out = ob_get_clean();
	
	if ($out) {
		$result['warning'][] = $out;
	}
}

$result['message'][] = "Complete.";

header('Content-Type: application/json');
echo json_encode($result);

