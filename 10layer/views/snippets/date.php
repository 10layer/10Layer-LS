<input type="text" name="<?= $field->tablename ?>_<?= $field->name ?>" value="<?php
	 if (!empty($field->value) && (strtotime($field->value)>0)) {
	 	print date("Y-m-d",strtotime($field->value));
	 }
	?>" class="<?= $field->class ?> datepicker" />