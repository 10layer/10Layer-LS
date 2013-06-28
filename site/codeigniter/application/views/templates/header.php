<!DOCTYPE html> 
<html lang="en-gb" charset="UTF-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
	<title>10Layer</title> 
	<link rel="shortcut icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	<link rel="icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	
	<link rel="home" href="<?= base_url() ?>" title="Home" />
	
	<link rel="stylesheet" href="/resources/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	<link rel="stylesheet" href="/resources/chosen/chosen.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="<?= base_url() ?>resources/font-awesome/css/font-awesome.min.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="<?= base_url() ?>resources/css/style.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	<script type="text/javascript" src="/resources/jquery/jquery182.js"></script>
	<script type="text/javascript" src="<?= base_url() ?>resources/js/skrollr.js"></script>
	
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<ul id="menu1" class="nav">
					<li>
						<a href="<?= base_url() ?>#top">Home</a>
					</li>
					<li>
						<a href="<?=base_url() ?>#about" class="scroll">About</a>
					</li>
					<li>
						<a href="<?=base_url() ?>#built" class="scroll">Built on 10Layer</a>
					</li>
					<li>
						<a href="<?=base_url() ?>#news" class="scroll">News</a>
					</li>
					<li>
						<?= anchor("section/docs", "Docs") ?>
					</li>
					<li>
						<a href="https://github.com/10layer/10Layer-LS/archive/master.zip">Download</a>
					</li>
					<li>
						<a target="_blank" href="https://github.com/10layer/10Layer-LS">Github</a>
					</li>
					<li>
						<?= anchor("page/contact", "Contact") ?>
					</li>
					
				</ul>
			</div>
		</div>
	</div>
	
	<div class="row hero <?= ($this->uri->ruri_string() == "/home/index") ? 'hero-home' : '' ?>" id="<?= ($this->uri->ruri_string() == "/home/index") ? 'top' : '' ?>">
		<div class="hero-scroller hide" style="position: absolute; left: 50%; bottom: 20px; " >
		<a data-0="color:rgba(255,255,255,1)" data-300="color:rgba(255,255,255,0)" href="#jump_1" class="scroll" >
			<i class="icon-chevron-down icon-white icon-4x"></i>
		</a>
		</div>
		
	</div>
	
	
	<div class="main-container" id="jump_1">
	
	