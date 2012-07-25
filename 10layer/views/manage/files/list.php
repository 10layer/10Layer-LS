<form method="post">
	<input type="hidden" name="dobucketcreate" value="true" />
	<label>New bucket</label>
	<input type="text" name="bucketname" value="" />
	<br />
	<input type="submit" name="submit" value="Create" class="button" />
</form>
<br clear="both" />
<?php
	foreach($buckets as $bucket) {
	?>
	<div id="bucket">
		<a href="/manage/files/bucket/<?= rawurlencode($bucket["name"]) ?>">
		<?php if ($bucket["public"]) { ?>
		<img src="/tlresources/file/images/folder.png" /><br />
		<?php } else { ?>
		<img src="/tlresources/file/images/folder_private.png" /><br />
		<?php } ?>
		<?= $bucket["name"] ?></a>
		<br />
		<?= $bucket["count"] ?> items, <?= round($bucket["size"]/1024) ?>KB<br />
		<?= anchor("/manage/files/delete_bucket/".$bucket["name"],"Delete") ?> | 
		<?php
			if ($bucket["public"]) {
		?>
			<?= anchor("/manage/files/change_acl/private/".$bucket["name"],"Make Private") ?>
		<?php
			} else {
		?>
			<?= anchor("/manage/files/change_acl/public/".$bucket["name"],"Make Public") ?>
		<?php
			}
		?>
	</div>
	<?php
	}
?>