<?php
	$content_types=$this->model_content->get_content_types();
	$this->load->library("tluserprefs");
	$usermenus=$this->tluserprefs->get_menus_order();
	if (empty($usermenus)) {
		$usermenus=array();
		foreach($content_types as $ct) {
			$usermenus[]=$ct->urlid;
		}
	}
?>
<li class="menuitem"><?= anchor("home","Home") ?></li>
<li class="menuitem dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Create <b class="caret"></b></a>
	<ul class="dropdown-menu">
	<?php
		foreach($usermenus as $usermenu) {
			$content_type=false;
			foreach($content_types as $ct) {
				if ($ct->urlid==$usermenu) {
					$content_type=$ct;
				}
			}
			if (isset($content_type->urlid)) {
	?>
		<li class="menuitem" id='create_menuitem_<?= $content_type->urlid ?>'><?= anchor("create/".$content_type->urlid,$content_type->name) ?></li>
	<?php
			}
		}
	?>
	</ul>
</li>
<li class="menuitem dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Edit <b class="caret"></b></a>
	<ul class="dropdown-menu">
	<?php
	foreach($usermenus as $usermenu) {
		$content_type=false;
		foreach($content_types as $ct) {
			if ($ct->urlid==$usermenu) {
				$content_type=$ct;
			}
		}
		if (isset($content_type->urlid)) {
	?>
	<li class="menuitem" id='menuitem_<?= $content_type->urlid ?>'><?= anchor("edit/".$content_type->urlid,$content_type->name) ?></li>
	<?php
		}
	}
?>
	</ul>
</li>
<li class="menuitem dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Publish <b class="caret"></b></a>
	<ul class="dropdown-menu">
	<?php
	$collections=$this->model_collections->getAll();
	foreach($collections as $collection) {
	?>
		<li class="menuitem"><?= anchor("publish/collection/".$collection->urlid,$collection->name) ?></li>
	<?php
	}
	?>
	</ul>
</li>
<li class="menuitem"><?= anchor("manage","Manage") ?></li>
<li class="menuitem"><?= anchor("user/logout","Logout") ?></li>
