<input type="file" name="<?= $field->tablename ?>_<?= $field->name ?>" class="file_upload <?= $field->class ?>" value="<?= $field->value ?>" />
<input type="hidden" name="<?= $field->tablename ?>_<?= $field->name ?>" value="<?= $field->value ?>" />
<?php
	if (isset($urlid)) {
?>
<div class="preview-image">
<img src="/workers/picture/display/<?= $urlid ?>/cropThumbnailImage/500/300?<?= rand(0,10000) ?>" />
</div>
<?php
	}
?>