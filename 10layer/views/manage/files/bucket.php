<form enctype="mixed/multipart" method="post">
	<input type="hidden" name="doupload" value="true" />
	<label>Upload file</label>
	<input type="file" name="uploadfile" value="" />
	<br />
	<input type="submit" name="submit" value="Upload" class="button" />
</form>
<br clear="both" />
<?php
	foreach($objects as $object) {
?>
<div class="object">
	<div class="objname"><a href="<?= $object["url"] ?>"><?= $object["name"] ?></a></div>
	<div class="objsize"><?= round($object["size"]/1024) ?>KB</div>
	<div class="objtime"><?= date("Y-m-d H:i:s", $object["last_modified"]) ?></div>
	<div class="objactions"><?= anchor("/manage/files/delete_object/".$bucket."/".$object["name"],"Delete") ?> | <?= anchor("/manage/files/output_object/".$bucket."/".$object["name"],"Output") ?></div>
</div>
<?php
	}
?>
