	<?php
			if (is_array($field->options)) {
				?>
				<div class="radiogroup">
				<?php
				foreach($field->options as $option=>$value) {
				$checked="";
				if ($field->value==$option) {
					$checked='checked="checked"';
				}
	?>
					<div class='radio'>
						<input type="radio" name="<?= $field->tablename ?>_<?= $field->name ?>" value="<?= $option ?>" class="<?= $field->class ?>" <?= $checked ?> /> 
						<div class="radio_label">
							<?= $value ?>
						</div>
					</div>
	<?php
				}
				?>
				</div>
				<?php
			}