<?php
	if ($field->multiple) {
		if (is_array($field->value)) {
			foreach($field->value as $val) {
?>
<input type="hidden" name="<?= $field->tablename ?>_<?= $field->name ?>[]" value="<?= $val ?>" />
<?php
			}
		}
	} else {
?>
<input type="hidden" name="<?= $field->tablename ?>_<?= $field->name ?>" value="<?= $field->value ?>" />
<?php
	}
?>