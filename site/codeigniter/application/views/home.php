<?php
	$this->load->view("templates/header");
?>
<div class="bg_1">
	<div data-bottom="opacity:1;" data-center="opacity:1;" class="row text-center slogan">
		<div class="span8 offset2 slogan-text">
			<h1>10Layer LS, next-generation CMS</h1>
			<p class="lead">10Layer is a next-generation content management system. Extensible, fast, powerful, ass-kicking, name-taking and open source.</p>
		</div>
	</div>
</div>

<div id="about" class="zone-text bg_3">
	<h2>About 10Layer</h2>
	<?php
		$x = 0;
		foreach($zones->promo as $promo) {
	?>
<div class="row-fluid">
	<div class="span6 offset3">
		<h3><?= $promo->title ?></h3>
		<?php
		if (!empty($promo->picture)) {
			$this->tenlayer->image($promo->picture);
		?>
		<div class="thumbnail" data-bottom="opacity:0.3;" data-center="opacity:1;">
			<img  src="<?= $this->tenlayer->image($promo->picture) ?>" />
		</div>
		<?php
		}
		?>
		
		<p><?= $promo->body ?></p>
	</div>
</div>
	<?php
		}
	?>
</div>

<div id="built" class="zone-text bg_7">
	<div class="container">
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
	</div>
</div>
<div id="news" class="zone-text bg_9">
	<div class="container">
		<h2>News</h2>

			<?php
				foreach($zones->articles as $article) {
			?>
		<div class="row ">
			<div class="span6 offset3">
				<h3><?= anchor("article/".$article->_id, $article->title) ?></h3>
				<p><?= $article->blurb ?></p>
			</div>
		</div>
			<?php
				}
			?>
	</div>
</div>

<?php
	$this->load->view("templates/footer");
?>