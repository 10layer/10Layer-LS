			<div class="radiogroup">
					<div class='radio'><input type="radio" name="<?= $field->tablename ?>_<?= $field->name ?>" value="1" class="<?= $field->class ?>" <?php if ($field->value==1) { ?> checked="checked" <?php } ?> /><div class="radio_label">Yes</div></div>
					<div class='radio'><input type="radio" name="<?= $field->tablename ?>_<?= $field->name ?>" value="0" class="<?= $field->class ?>" <?php if ($field->value==0) { ?> checked="checked" <?php } ?> /><div class="radio_label">No</div></div>
				</div>