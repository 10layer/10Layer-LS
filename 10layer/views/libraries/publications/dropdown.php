<script type="text/javascript">
	$(function() {
		$("#publication_select").change(function() {
			var id=$("#publication_select option:selected").first().val();
			$.get(
				"<?= base_url() ?>library/tenlayer_publications/change/"+id,
				function(data) {
					location.reload();
				}
			);
		});
	});
</script>
<div id='publications_dropdown'>
	Publication
	<select id='publication_select'>
	<?php
		foreach($publications as $publication) {
	?>
	<option value='<?= $publication->id ?>'
	
	<?php
		if ($this->publications->id()==$publication->id) {
	?>
	 selected='selected' 
	<?php
		}
	?>><?= $publication->name ?></option>
	<?php
		}
	?>
	</select>
	<a href="http://<?= $this->publications->base_url()?>" target="_blank">View</a>
</div>