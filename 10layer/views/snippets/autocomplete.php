	<input id="autocomplete_view_<?= $field->tablename ?>_<?= $field->name ?>" type="text" tablename="<?= $field->tablename ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" class="autocomplete <?php if ($field->multiple) { ?>multiple<?php } ?> <?= $field->class ?>" value="<?php if (!$field->multiple) { print $field->data->fields["title"]->value; } ?>" <?php if ($field->contenttype=='mixed') { ?> mixed='mixed' contenttypes='<?= implode(",",$field->contenttypes) ?>' <?php } ?> />
	<br clear="both" />
	
	<div class="aligner">
	<ul class="items_container">
	
	<?php
		if (isset($field->data) && is_array($field->data)) {
			$x=0;
			foreach($field->data as $data) {
				$value=$data->content_id;
				$title=$data->fields["title"]->value;
	?>
	
	<li class="autocomplete_item">
		<span class="ui-icon ui-icon-arrowthick-2-n-s float-left" style="margin:10px;"></span>
		<span class="remover ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button" aria-disabled="false">
			
			<span class="ui-button-icon-primary ui-icon ui-icon-circle-close"></span>
			<span class="ui-button-text">
				<?= $title ?>
			</span></span>
	<input id="autocomplete_<?= $field->contenttype ?>_<?= $field->name ?>_<?= $value ?>" type="hidden" name="<?= $field->tablename ?>_<?= $field->name ?><?php if ($field->multiple) { ?>[]<?php } ?>" value="<?= $value ?>"  />
	</li>
	
<?php
			}
		}
?>

	</ul>
	</div>

<?php
		if ($field->external) {
	?>
	<br clear="both"><br clear="both">
	<button style="margin-left: 110px" id="add_relation_<?= $field->tablename ?>_<?= $field->name ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" tablename="<?= $field->tablename ?>" class="add-relation ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>New</span></button>
	<br clear="both">
	<?php
		}
	?>