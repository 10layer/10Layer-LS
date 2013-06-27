<?php
	$this->load->view("templates/header");
?>
<div class="row">
	<div class="span8 offset2">
		<h1><?= $content->title ?></h1>
	</div>
</div>
<div class="row">
	<div class="span8 offset2">
		<h4><?= $content->blurb ?></h4>
	</div>
	<div class="span8 offset2">
		<?= $content->body ?>
	</div>
</div>
<?php
	$this->load->view("templates/footer");
?>