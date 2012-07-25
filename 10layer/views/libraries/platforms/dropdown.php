<script type="text/javascript">
	$(function() {
		$("#platform_select").change(function() {
			var id=$("#platform_select option:selected").first().val();
			$.get(
				"<?= base_url() ?>library/tenlayer_platforms/change/"+id,
				function(data) {
					location.reload();
				}
			);
		});
	});
</script>
<div id='platforms_dropdown'>
	Platform
	<select id='platform_select'>
	<?php
		foreach($platforms as $platform) {
	?>
	<option value='<?= $platform->id ?>'
	
	<?php
		if ($this->platforms->id()==$platform->id) {
	?>
	 selected='selected' 
	<?php
		}
	?>><?= $platform->name ?></option>
	<?php
		}
	?>
	</select>
	<a href="http://<?= $this->platforms->base_url()?>" target="_blank">View</a>
</div>