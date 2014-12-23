<?php
define('BASEPATH', dirname(__FILE__));

require_once BASEPATH . '/configuration.php';


session_start();

if (isset($_POST['action']) && $_POST['action'] == 'auth') {

	$username = $_POST['username'];
	$password = $_POST['password'];

	if (isset($credentials[$username]) && $credentials[$username] == $password) {
		$user = new stdClass;
		$user->username = $username;
		$user->password = $password;
		$_SESSION['import.user'] = $user;
	} else {
		$_SESSION['import.error'] = "Invalid login";
	}

	header('Location: index.php?type=html');
	exit;
}

$user = isset($_SESSION['import.user']) ? $_SESSION['import.user'] : NULL;
$type = isset($_GET['type']) ? $_GET['type'] : 'text';

if ($user) {

	if (isset($_GET['action']) && $_GET['action'] == 'read') {
	
		require_once 'include/Progress.php';

		if ($type == 'text') {
			header('Content-Type: text/text');
		} else {
			header('Content-Type: text/html');
		}

		echo Progress::readLog();
		exit;
	}	
}

?>
<?php include 'tpl/layout.php'; ?>

