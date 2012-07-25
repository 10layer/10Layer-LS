<div class="menuitem"><?= anchor("home","Home") ?></div>
<div class="menuitem"><?= anchor("create","Create") ?></div>
<div class="menuitem"><?= anchor("edit","Edit") ?></div>
<div class="menuitem"><?= anchor("publish","Publish") ?></div>
<!--<div class="menuitem"><?= anchor("stats","Stats") ?></div>-->
<div class="menuitem"><?= anchor("manage","Manage") ?></div>
<div class="menuitem"><?= anchor("user/logout","Logout") ?></div>
<?php
	$this->publications->draw_dropdown();
	$this->platforms->draw_dropdown();
?>