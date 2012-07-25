<div id="home">
	<div id="home-body" class="">
		<?php 
			$this->templater->setType("article")->setName("first-article")->start();
		?>
		<div class="first-article single-article" id="article_<?= $this->templater->content('id')->draw() ?>">
			<div class="headline" id="headline-1"><?= $this->templater->link()->content("headline")->draw() ?></div>
			<div class="photo" id="photo-1"><?= $this->templater->link()->photo(448,300)->draw() ?></div>
			<div class="blurb" id="blurb-1"><?= $this->templater->link()->content("blurb")->draw() ?></div>
		</div>
		<?php
			$this->templater->end();
		?>
		
		<div id="lowerarticles">
		<?php
			$this->templater->setAlternates(array("left","right"));
			$this->templater->setType("article")->repeat(10)->setName("lower-articles")->start();
		?>
				<div class="article <?= $this->templater->alternate() ?>" id="article_<?= $this->templater->content("id")->draw() ?>">
					<div class="photo" id="photo-<?= $this->templater->content("id")->draw() ?>"><?= $this->templater->link()->photo(218,130)->draw() ?></div>
					<div class="blurb" id="blurb-<?= $this->templater->content("id")->draw() ?>"><?= $this->templater->link()->content("headline")->draw() ?></div>
				</div>
		<?php
			$this->templater->end();
		?>
		</div>
	</div>
</div>