		<?php

		if ($this->exists->view("content/".$field->tablename."/select")) {
				$this->load->view("content/".$field->tablename."/select", array("field"=>$field));
			} else {
				$this->load->view("content/default/select", array("field"=>$field));
			}
			if ($field->multiple && is_array($field->value)) {
				foreach($field->value as $value) {
				?>
					<input type="hidden" id="<?= $field->tablename."_".$field->name ?>_field" name="<?= $field->tablename ?>_<?= $field->name ?>[]" value="<?= $value ?>" />
				<?php
				}
			} else {
			?>
				<input type="hidden" id="<?= $field->tablename."_".$field->name ?>_field" name="<?= $field->tablename ?>_<?= $field->name ?>" value="<?= $field->value ?>" />
			<?php
			}