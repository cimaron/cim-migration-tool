<?php
/**
 * Protect access
 */
defined('BASEPATH') or die();
?>


<div class="messages">
<?php if (isset($_SESSION['import.message'])) { ?>
	<div class="alert alert-info"><?php echo $_SESSION['import.message']; unset($_SESSION['import.message']); ?></div>
<?php } ?>
<?php if (isset($_SESSION['import.error'])) { ?>
	<div class="alert alert-danger"><?php echo $_SESSION['import.error']; unset($_SESSION['import.error']); ?></div>
<?php } ?>
</div>
