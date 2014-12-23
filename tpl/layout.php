<?php
/**
 * Protect access
 */
defined('BASEPATH') or die();
?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<title>Import</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php if (!$user || $type == 'html') { ?>
		<link href="assets/css/bootstrap.min.css" rel="stylesheet" />	
	<?php } ?>

	<script src="assets/js/jquery-1.11.2.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>

	<script>DisplayType = <?php echo json_encode($type); ?>;</script>
	<script src="assets/js/import.js"></script>

</head>

<body>

	<div class="container" style="margin-top: 40px;">

	<?php if (isset($user)) { ?>
		<?php include 'tpl/import.php'; ?>
	<?php } else { ?>
		<?php include 'tpl/login.php'; ?>
	<?php } ?>

	<div style="margin-top: 40px; text-align: center;">
	<?php if ($type == 'text') { ?>
		<a style="font-size: 10px;" href="?type=html">[html version]</a>
	<?php } else { ?>
		<a style="font-size: 10px;" href="?type=text">[text version]</a>
	<?php } ?>
	</div>
	
	</div>
</body>
</html>
