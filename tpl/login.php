<?php
/**
 * Protect access
 */
defined('BASEPATH') or die();

?>

<div class="row">
<div class="col-lg-6 col-lg-offset-3">

	<?php include 'messages.php'; ?>

	<form action="index.php" method="post">

		<input type="hidden" name="action" value="auth" />
	
		<div class="form-group">
			<label for="username">Username</label>
			<input type="text" class="form-control" id="username" name="username" placeholder="Enter username" />
		</div>

		<div class="form-group">
			<label for="password">Password</label>
			<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" />
		</div>

		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-block">Go</button>
		</div>

	</form>

</div>
</div>