<select name="<?= $field->tablename ?>_<?= $field->name ?>">
<?php
	$sections=$this->db->order_by("order ASC")->get("sections")->result();
	foreach($sections as $section) {
?>
		<option value="<?= $section->id ?>" <?php
			if ($field->value==$section->id) {
		?>
		selected="selected"
		<?php
			}
		?>><?= $section->title ?></option>
<?php
	}
?>
</select>