<?php
	$this->load->view("templates/header");
?>
<div class="row">
	<div class="span8 offset2">
		<h1><?= $section->title ?></h1>
	</div>
</div>
<div class="row">
	<div class="span8 offset2">
<?php
	foreach($section->zone as $zone) {	
		$articles = $this->tenlayer->zone($section->_id, $zone->zone_urlid);
?>
	
	<div class="span2">
		<h2><?= $zone->zone_name ?></h2>
		<ul>
		<?php
			foreach($articles as $article) {
		?>
		<li><?= anchor($article->content_type."/".$article->_id, $article->title) ?></li>
		<?
			}
		?>
		</ul>
	</div>
<?php
	}
?>
	</div>
</div>
<?php
	$this->load->view("templates/footer");
?>