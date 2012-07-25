<?php
	if ($field->type!="hidden") {
?>
	<label class="<?= $field->label_class ?>"><?= ucwords($field->label) ?></label>
<?php
	}
	if ($this->exists->view("snippets/".$field->type)) {
		$this->load->view("snippets/".$field->type);
	} else {
		$this->load->view("snippets/text");
	}
	if ($field->type!="hidden") {
?>
	<br clear="both" />
<?php
	}
?>