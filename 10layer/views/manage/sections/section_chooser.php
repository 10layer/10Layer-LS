<div id="section_manager">
	<div id="section_list">
<?php
	foreach($sections as $section) {
		//$sectionobj=$this->content->getByIdORM($section->urlid)->getData();
		//print_r($sectionobj);
		//if (empty($sectionobj->parent_section)) {
?>
			<div class="section_choice"><?= anchor("manage/collections/section/".$collectionurlid."/".$section->urlid, $section->title) ?></div>
<?php
		//}
	}
?>
	</div>
</div>