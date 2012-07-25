<script>document.domain=document.domain;</script>
<script language="javascript">
	$(function() {
		$( ".rich_overlay_remove" ).button({
            icons: {
                primary: "ui-icon-trash"
            },
            text: false
		}).click(function() {
			$(this).parent().parent().next().val("");
			$(this).parent().parent().empty();
		});
		$( ".rich_overlay_edit" ).button({
            icons: {
                primary: "ui-icon-pencil"
            },
            text: false
		});
	});
</script>
<div class="rich_overlay">
	<div class="rich_overlay_remove">Remove</div>
	<div href="/edit/picture/<?= $item->urlid ?>" target="_blank" class="rich_overlay_edit new-window">Edit</div>
</div>
<div class="selectitem">
	<div class="selectitem-image"><img src="/workers/picture/display/<?= $item->urlid ?>/cropThumbnailImage/400/300" /></div>
	<div class="selectitem-title"><?= $item->getData()->title ?></div>
</div>