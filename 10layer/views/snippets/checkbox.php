	<?php
	if ($field->multiple) {
			?>
			<div class="checkboxes">
			<?php
				foreach($field->options as $key=>$val) {
				?>
					<div class="<?= $field->class ?> checkbox"> <input type="checkbox" name="<?= $field->tablename ?>_<?= $field->name ?>[]" value="<?= $key ?>" class="<?= $field->class ?>_input" 
				<?php
					if (in_array($key,$field->value)) {
				?>
					checked="checked" 
				<?php
					}
				?>
				/>
				<div class="<?= $field->class ?>_label"><?= $val ?></div>
				</div>
				<?php
				}
				?>
				</div>
			<?php	
			} else {
				$checked="";
				if ($field->value) {
					$checked='checked="checked"';
				}
	?>
	<input type="checkbox" name="<?= $field->tablename ?>_<?= $field->name ?>" value="1" class="<?= $field->class ?>" <?= $checked ?> />
	<?php
			}