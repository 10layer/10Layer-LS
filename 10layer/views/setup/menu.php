	<?php
	$pg = $this->uri->segment(2);
	?>
	<div class="span2">
		<ul class="nav nav-pills nav-stacked">
			<li class="<?= ($pg=="admin") ? "active" : "" ?>"><a href="/setup/admin">Administrator</a></li>
			<li class="<?= ($pg=="users") ? "active" : "" ?>"><a href="/setup/users">Users</a></li>
			<li class="<?= ($pg=="content_types") ? "active" : "" ?>"><a href="/setup/content_types">Content Types</a></li>
			<li class="<?= ($pg=="api_keys") ? "active" : "" ?>"><a href="/setup/api_keys">API Keys</a></li>
		</ul>
	</div>