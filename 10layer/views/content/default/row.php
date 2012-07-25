<?php
	$version=$this->versions->get_version($item->urlid);
	$major_version=$this->versions->get_major_version($item->urlid);
?>

		<td>
			<img src="/workers/picture/display/<?= $item->urlid ?>/cropThumbnailImage/50/40" />
		</td>
		<td class="content-workflow-<?= $major_version ?>"><a href="<?= base_url()."edit/$contenttype/".$item->urlid ?>"><?= $item->title ?></a></td>
		<td></td>
		<td><?= $version ?></td>
		<td class="lock_container">
			<span class="ui-icon ui-icon-locked" <?php 
			if (!$this->checkout->check($item->urlid)) {
			?> style="display:none" <?php
			}
		?>></span>
			</td>