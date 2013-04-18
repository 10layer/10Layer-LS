<?php
	$content_types=$this->model_content->get_content_types();
	$this->load->library("tluserprefs");
	$usermenus=$this->tluserprefs->get_menus_order();
	if (empty($usermenus)) {
		$usermenus=array();
		foreach($content_types as $ct) {
			$usermenus[]=$ct->_id;
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
				if ($ct->_id==$usermenu) {
					$content_type=$ct;
				}
			}
			if (isset($content_type->_id)) {
	?>
		<li class="menuitem" id='create_menuitem_<?= $content_type->_id ?>'><?= anchor("create/".$content_type->_id,$content_type->name) ?></li>
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
			if ($ct->_id==$usermenu) {
				$content_type=$ct;
			}
		}
		if (isset($content_type->_id)) {
	?>
	<li class="menuitem"><?= anchor("edit/".$content_type->_id,$content_type->name) ?></li>
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
	$collections=$this->model_collections->get_all();
	foreach($collections as $collection) {
		$options=$this->model_collections->get_options($collection->_id);

		// //Here we check if we have nested collections
		// $tmp=array();
		// foreach($options as $option) {
		// 	if (!isset($option->{$collection->_id})) {
		// 		$tmp[$option->_id]=$option;
		// 	}
		// }
		// foreach($options as $option) {
		// 	if (isset($option->{$collection->_id}) && is_string($option->{$collection->_id})) {
		// 		if (array_key_exists($option->{$collection->_id}, $tmp)) {
		// 			$tmp[$option->{$collection->_id}]->submenu[]=$option;
		// 		}
		// 	}
		// }
		// $options=$tmp;
		//print_r($tmp);
	?>
		<li class="dropdown-submenu">
			<a href="#"><?= $collection->name.'s' ?></a>
			<ul class="dropdown-menu">
			<?php
			foreach($options as $option) {
			?>
				<li><?= anchor("/publish/".$collection->_id."/".$option->_id, $option->title) ?></li>
			<?php
			}
			?>
			</ul>
		</li>
	<?php
	//die();
	}
	?>
	</ul>
</li>
<li class="menuitem dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Manage <b class="caret"></b></a>
	<ul class="dropdown-menu">
		<!--<li class="menuitem"><a href="<?= base_url() ?>setup/healthchecks">Health Check</a></li>-->
		<li class="menuitem"><a href="<?= base_url() ?>setup/urlids">Manage Url IDs</a></li>
		<li class="menuitem"><a href="<?= base_url() ?>setup/redirects">Manage Redirects</a></li>
		<li class="menuitem"><a href="<?= base_url() ?>setup/users">Users</a></li>
		<li class="menuitem"><a href="<?= base_url() ?>setup/content_types">Content Types</a></li>
		<!--<li class="menuitem"><a href="<?= base_url() ?>setup/security">Security</a></li>-->
	</ul>
</li>
<li class="menuitem"><?= anchor("user/logout","Logout") ?></li>
