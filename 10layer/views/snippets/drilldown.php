<input id="autocomplete_view_<?= $field->tablename ?>_<?= $field->name ?>" type="text" tablename="<?= $field->tablename ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" class="autocomplete <?php if ($field->multiple) { ?>multiple<?php } ?> <?= $field->class ?>" value="<?php if (!$field->multiple) { print $field->data->fields["title"]->value; } ?>" />
	<br clear="both" />
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