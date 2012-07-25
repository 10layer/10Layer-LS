<div id="contentlist" class="<?= $contenttype ?>-list boxed wide">
	<div class="popupSearchContainer">
		<input type="text" class="popupSearch" value="Search..." />
		<span class="popupResultsCount"></span>
	</div>
	<?= $this->pagination->create_links(); ?>
	<table>
		<tr> 
			<th></th>
			<th></th>
			<th>Title</th>
			<th>Edit</th>
		</tr>
	<?php
		$class="odd";
		foreach($content as $item) {
	?>
	<tr class="<?= $class ?> <?= $contenttype ?>-item content-item">
		<td>
		<?php
			if ($multiple) {
		?>
		<input type="checkbox" name="<?= $contenttype ?>[]" value="<?= $item->content_id ?>" class="item-select multiselect">
		<?php
			} else {
		?>
		<input type="radio" name="<?= $contenttype ?>" value="<?= $item->content_id ?>" class="item-select singleselect">
		<?php
			}
		?>
		</td>
		<td>
			<img src="/workers/picture/display/<?= $item->urlid ?>/cropThumbnailImage/40/30" />
		</td>
		<td><?= $item->title ?></td>
		<td><a href="<?= base_url()."edit/$contenttype/".$item->urlid ?>">Edit</a></td>
	</tr>
	<?php
			if (empty($class)) {
				$class="odd";
			} else {
				$class="";
			}
		}
	?>
	</table>
	<?= $this->pagination->create_links(); ?>
</div>
<br clear="both" />