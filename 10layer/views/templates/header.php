<!DOCTYPE html> 
<html lang="en-gb" charset="UTF-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
	<title>10Layer CMS</title> 
	<link rel="shortcut icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	<link rel="icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	
	<link rel="home" href="<?= base_url() ?>" title="Home" />
	
	<link rel="stylesheet" href="/resources/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	<link rel="stylesheet" href="/resources/chosen/chosen.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="/resources/css/style.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	
	<?php
		if (isset($stylesheets) && is_array($stylesheets)) {
			foreach($stylesheets as $stylesheet) {
	?>
		<link rel="stylesheet" href="<?= $stylesheet ?>" type="text/css" media="screen, projection" charset="utf-8" />
	<?php
			}
		}
	?>
	
	<script type="text/javascript" src="/resources/jquery/jquery182.js"></script>
	<script type="text/javascript" src="/resources/js/underscore-min.js"></script>
	<script type="text/javascript" src="/resources/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/resources/chosen/chosen.jquery.js"></script>
	<script type="text/javascript" src="/resources/js/10layer.js"></script>
	
	<script language="javascript">
		<?php
			if (empty($menu1_active)) {
				$menu1_active="";
			}
			
		?>
		$(function() {
			$("#menu1 div").each(function() {
				if ($(this).find("a").first().attr("href")=="<?= base_url().$menu1_active ?>") {
					$(this).addClass("active");
				} else {
					$(this).removeClass("active");
				}
			});
			
		});
	</script>
	
	<?php print $this->autoloader->javascript(); ?>
	
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<ul id="menu1" class="nav">
					<li id="logo" class="brand">
						<img src="/resources/images/logo_navbar.png" />
					</li>
					<?php
						if (isset($menu1)) {
							$this->load->view_if_exists("menus/main/".$menu1);
						} else {
							$this->load->view_if_exists("menus/main/default");
						}
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
	