<?php
	$this->load->library("cdn");
	
	if (empty($field->value)) {
	?>
	<div style="margin-top: 20px">Awaiting upload</div>
	<?php
	} else {
	?>
	<div style="background:#fff;width:400px;float: left; overflow:hidden;clear: right; margin-top: 20px;border: 1px #757474 solid;width: 500px;padding: 5px;"><?= $field->value ?></div>
	<input type="hidden" value="<?= $field->value ?>" name="<?= $field->name ?>" />
	<?php
	}
?>