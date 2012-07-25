<?php
	try{
		$options=file_get_contents($field->options);
		$options=strip_tags($options);
		$optionsarray=explode("\n",$options);
		for($x=0;$x<sizeof($optionsarray);$x++) {
			if (empty($optionsarray[$x])) {
				unset($optionsarray[$x]);
			}
		}
	} catch (Exception $e) {
		show_error($e);
	}
?>
<select name="<?= $field->tablename ?>_<?= $field->name ?>">
	<option></option>
	<?php
		$found=false;
		foreach($optionsarray as $option) {
	?>
	<option <?php
		if ($field->value==$option) {
			$found=true;
	?>
	selected='selected'
	<?php
		}
	?>><?= $option ?></option>
	<?php
		}
		if (!$found && !empty($field->value)) {
	?>
	<option selected='selected'><?= $field->value ?></option>
	<?php
		}
	?>
</select>