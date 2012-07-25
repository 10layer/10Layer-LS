<?php
	//DON'T USE THIS - EXPERIMENTAL
	if ($field->multiple) {
?>
	<script>
		$(function() {
			$("#<?= $field->contenttype ?>_embed").load("<?= base_url()."create/embed/".$field->contenttype ?>", function() {
				if ($(".richedit").length) {
					initCKEditor();
				}
				$(".datepicker").datepicker({dateFormat:"yy-mm-dd"});
				
			});
		});
	</script>
	<div id="<?= $field->contenttype ?>_embed" class="embed"></div>
<?php
	} else {
?>
	<script>
		$(function() {
			$("#<?= $field->contenttype ?>_embed").load("<?= base_url()."create/embed/".$field->contenttype ?>", function() {
				$(".datepicker").datepicker({dateFormat:"yy-mm-dd"});
			});
		});
	</script>
	<div id="<?= $field->contenttype ?>_embed" class="embed"></div>
<?php
	}
?>