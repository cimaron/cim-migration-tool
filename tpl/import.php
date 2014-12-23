<?php
/**
 * Protect access
 */
defined('BASEPATH') or die();


$dh = opendir('var/imports');
$imports = array();

while ($file = readdir($dh)) {

	$file = explode('.', $file);
		
	if (strpos($file[0], '-') && $file[1] == 'php') {
		list($n, $name) = explode('-', $file[0]);
		$imports[] = (object)array('n' => (int)$n, 'name' => $name, 'full' => $file[0]);
	}	
}
	
function dosort($a, $b) {
	return $a->n > $b->n ? 1 : -1;
}

usort($imports, 'dosort');

?>

<?php if ($type == 'text') { ?>

	<?php include 'messages.php'; ?>

	<input type="button" value="Start Import" onClick="startImport();" /><br /><br />
	<textarea id="log" style="height: 400px; width: 600px;"></textarea>
	
	<?php return; ?>

<?php } ?>

<div class="row">
	
	<div class="col-lg-3 well">

		<table class="table table-striped">
			<thead>
				<tr style="border-bottom: 10px;">
					<th>#</th>
					<th><input type="checkbox" onClick="toggleImports(this, this.checked)" /></th>
					<th>Import</th>
				</tr>
			</thead>
		
		<?php foreach ($imports as $import) { ?>
			<tr>
				<td width="5" style="padding: 0 10px;">
					<?php echo $import->n; ?>
				</td>
				<td width="5" style="padding: 0 10px;">
					<input type="checkbox" name="import[]" value="<?php echo htmlentities($import->full); ?>" />
				</td>
				<td style="padding: 0 10px;">
					 <?php echo htmlentities($import->name); ?>
				</td>
			</tr>
		<?php } ?>
		</table>

		<div style="margin-top: 20px;" class="row-fluid">
			<input type="button" class="btn btn-success btn-block" value="Start Import" onClick="startImport();" />
			<input type="button" class="btn btn-default btn-block" id="listen" value="Listen" onClick="listen();" />
		</div>
	
	</div>

	<div class="col-lg-9">

		<?php include 'messages.php'; ?>

		<div id="log_html"></div>

	</div>

</div>


