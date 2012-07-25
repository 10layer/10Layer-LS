<div id="section_manager">
	<div id="section_list">
<?php
	foreach($collections as $collection) {
?>
		<div class="section_choice"><?= anchor("manage/collections/collection/".$collection->urlid, $collection->name) ?></div>
<?php
	}
?>
	</div>
</div>