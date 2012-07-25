<div id="contentlist" class="<?= $contenttype ?>-list boxed wide">
	<div class="popupSearchContainer">
		<input type="text" id="popupSearch" value="<?php
		if (empty($search)) {
		?>Search...<?php
		} else {
			print $search;
		}
		?>" />
		<span class="popupResultsCount"></span>
	</div>
	<div class="pagination"><?= $this->pagination->create_links(); ?></div>
	<br />
	<table>
		<tr> 
			<th>Title</th> 
			<th>Edit</th>
		</tr>
	<?php
		$class="odd";
		foreach($content as $item) {

	?>
	<tr class="<?= $class ?> <?= $contenttype ?>-item content-item">
		<td><div class="link fireaction2" urlid="<?= $item->urlid ?>"><?= $item->title ?></div></td>
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