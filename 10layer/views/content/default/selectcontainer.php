<div class="dropdown-header" id="contentcreate_dropdown">
	Create <span class="ui-icon ui-icon-circle-triangle-e" style="float: right"></span>
</div>

<div id="contentcreate" class="<?= $contenttype ?>-create" style="display: none">
</div>
<div id="contentlist" class="<?= $contenttype ?>-list boxed wide">
	<div class="popupSearchContainer">
		<input type="text" class="popupSearch" value="" />
		<span class="popupWorking hidden"><img src="/tlresources/file/images/ajax-loader.gif" /></span>
		<span class="popupResultsCount"></span>
		<span class="popupResultsClear"></span>
	</div>
	<div class="popupPagination"></div>
	<table class="<?= $contenttype ?>-content">
		<tr class='table-header'>
			<th></th>
			<th></th>
			<th>Title</th>
			<th>Edit</th>
		</tr>
		<tr class="<?= $contenttype ?>-item content-item template">
			<td class='select'>
			<?php
				if ($multiple) {
			?>
			<input type="checkbox" class="item-select" name="<?= $contenttype ?>[]" value="">
			<?php
				} else {
			?>
			<input type="radio" class="singleselect item-select" name="<?= $contenttype ?>" value="">
			<?php
				}
			?>
			</td>
			<td class='content_img'></td>
			<td class='content_title'></td>
			<td class='content_editlink'></td>
		</tr>
	</table>
	<div class="popupPagination"></div>
</div>
<br clear="both" />