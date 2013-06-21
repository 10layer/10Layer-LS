<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="en-gb" charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta charset="utf-8" />
	<title>10Layer CMS</title> 
	<link rel="shortcut icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	<link rel="icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	
	<link rel="home" href="<?= base_url() ?>" title="Home" />
	
	<link rel="stylesheet" href="/resources/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	<link rel="stylesheet" href="/resources/chosen/chosen.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="/resources/css/style.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="/resources/bootstrap-datepicker/css/datepicker.css" media="screen, projection" charset="utf-8" />
	
	<script type="text/javascript" src="/resources/jquery/jquery182-ck.js"></script>
	<script type="text/javascript" src="/resources/js/underscore-min.js"></script>
	<script type="text/javascript" src="/resources/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/resources/chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript" src="/resources/js/10layer.js"></script>
	
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<ul id="menu1" class="nav">
					<li id="logo" class="brand">
						<img width="40" height="40" alt="10Layer" style="width: 40px; height: 40px" src="/resources/images/logo_navbar.png" />
					</li>
					<?php
						$this->load->view_if_exists("menus/main/default");
					?>
				</ul>
			</div>
		</div>
	</div>
	
	<div class="container" style="margin-top: 60px">
	
		<div id="cookiecrumbs"></div>
		<?php
			if (!empty($msg)) {
		?>
		<div class="<?php if (!empty($msg["error"])) { print 'error '; } ?>message">
			<?= $msg["msg"] ?>
			<?php
				if (!empty($msg["info"])) {
					if (is_array($msg["info"])) {
						foreach($msg["info"] as $info) {
						?>
						<div class="info">
							<?= $info ?>
						</div>
						<?php
						}
					} else {
					?>
						<div class="info">
							<?= $msg["info"] ?>
						</div>
					<?php
					}
				}
			?>
		</div>
		<?php
			}
		?>
	