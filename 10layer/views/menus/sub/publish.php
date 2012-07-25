<?php
	
	$collections=$this->model_collections->getAll();
	foreach($collections as $collection) {
	?>
		<div class="menuitem"><?= anchor("publish/collection/".$collection->urlid,$collection->name) ?></div>
	<?php
	}
	/*if ($this->uri->segment(2)!="specialreport") {
		$sections=$this->sections->getAll();
		foreach($sections as $section) {
		?>
			<div class="menuitem"><?= anchor("publish/section/".$section->urlid,$section->title) ?></div>
		<?php
		}
	} else {
		$this->load->model("model_specialreports");
		$sections=$this->model_specialreports->getAll();
		foreach($sections as $section) {
		?>
			<div class="menuitem"><?= anchor("publish/specialreport/".$section->urlid,$section->title) ?></div>
		<?php
		}
	}*/
?>