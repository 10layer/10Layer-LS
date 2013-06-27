<?php
	$this->load->view("templates/header");
?>

	<div data-bottom="opacity:1;" data-center="opacity:1;" class="row text-center slogan">
		<div class="span8 offset2 slogan-text">
			<h1>10Layer LS, next-generation CMS</h1>
			<p class="lead">10Layer is a next-generation content management system. Extensible, fast, powerful, ass-kicking, name-taking and open source.</p>
		</div>
	</div>

<div class="row-fluid">
	
	<?php
		$x = 0;
		foreach($zones->promo as $promo) {
	?>
	<div class="span4">
		<?php
		if (!empty($promo->picture)) {
			$this->tenlayer->image($promo->picture);
		?>
		<div class="thumbnail" data-bottom="opacity:0;" data-center="opacity:1;">
			<img  src="<?= $this->tenlayer->image($promo->picture) ?>" />
		</div>
		<?php
		}
		?>
		<h3><?= $promo->title ?></h3>
		<p><?= $promo->body ?></p>
	</div>
	<?php
			$x++;
			if (($x % 3) == 0) {
	?>
</div>
<div class="row-fluid">
	<?php
			}
		}
	?>
</div>
<h2>Built on 10Layer</h2>
<div class="row-fluid">
	
	<?php
		$x = 0;
		foreach($zones->sites as $site) {
	?>
	<div class="span4">
		<?php
		if (!empty($site->picture)) {
			$this->tenlayer->image($site->picture);
		?>
		<div class="thumbnail" data-bottom="opacity:0;" data-center="opacity:1;">
			<img  src="<?= $this->tenlayer->image($site->picture) ?>" />
		</div>
		<?php
		}
		?>
		<h3><?= $site->title ?></h3>
		<p><?= $site->body ?></p>
	</div>
	<?php
			$x++;
			if (($x % 3) == 0) {
	?>
</div>
<div class="row-fluid">
	<?php
			}
		}
	?>
</div>

<h2>News</h2>
<div class="row well">
	<div class="span3">
		
	</div>
	<?php
		foreach($zones->articles as $article) {
	?>
	<div class="span12">
		<h3><?= anchor($article->content_type."/".$article->_id, $article->title) ?></h3>
		<p><?= $article->blurb ?></p>
	</div>
	<?php
		}
	?>
</div>
<?php
	$this->load->view("templates/footer");
?>