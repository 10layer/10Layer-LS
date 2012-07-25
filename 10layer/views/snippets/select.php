	<select
		<?php
			if ($field->multiple) {
		?>
	multiple='multiple' 
		<?php
			}
		?>
	name="<?= $field->tablename ?>_<?= $field->name ?><?php
			if ($field->multiple) {
		?>[]
		<?php
			}
		?>" class="<?= $field->contenttype ?>_<?= $field->name ?> <?= $field->class ?> <?php if ($field->multiple) {
		?>multiple
		<?php
		}
		?>">
		<?php
			if (!$field->multiple) { //We only show no option for single selects
		?>
			<option value="0"></option>
		<?php
			}
			//We don't want to have a key of zero, which is what will happen if we get an array without explicit keys
			$keyadjust=0;
			foreach($field->options as $key=>$val) {
				if ($key==0) {
					$keyadjust=1;
				}
		?>
		<option value="<?= $key+$keyadjust ?>"
		<?php
			if (!is_array($field->value)) {
				if ($field->value==($key+$keyadjust)) {
		?>
			selected="selected"
			<?php
				}
			?>
		<?php
			} else {
				if (in_array(($key+$keyadjust),$field->value)) {
				?>
			selected="selected"
				<?php
				}
			}
		?>><?= $val ?></option>
		<?php
			}
		?>
	</select>
	<br clear="both" />
	<?php
		if ($field->external) {
	?>
	<button style="margin-left: 110px" id="add_relation_<?= $field->tablename ?>_<?= $field->name ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" tablename="<?= $field->tablename ?>" class="add-relation ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>New</span></button>
	<?php
		}
	?>
