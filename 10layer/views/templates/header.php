<!DOCTYPE html> 
<html lang="en-gb" charset="UTF-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
	<title>10Layer CMS</title> 
	<link rel="shortcut icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	<link rel="icon" href="/resources/images/favicon.ico" type="image/x-icon" /> 
	
	<link rel="home" href="<?= base_url() ?>" title="Home" />
	
	<link rel="stylesheet" href="/resources/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" charset="utf-8" />
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
	
	<script type="text/javascript" src="/resources/jquery/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="/resources/bootstrap/js/bootstrap.min.js"></script>
	<script language="javascript">
		<?php
			if (empty($menu1_active)) {
				$menu1_active="";
			}
			if (empty($menu2_active)) {
				$menu2_active="";
			}
		?>
		$(function() {
			$("#menu2 div").each(function() {
				if ($(this).find("a").first().attr("href")=="<?= base_url().$menu2_active ?>") {
					$(this).addClass("active");
				} else {
					$(this).removeClass("active");
				}
			});
			if ($("#menu2_container").width() > 768) {
				
				$("#menu2_scrollr").show();
				
				var showMenu=false;
				$("#menu2_scrollr").click(function() {
					if (showMenu) {
						$("#menu2").animate({height:40}, 300, 
							function() {
								showMenu=false;
								$("#menu2_scrollr").button({
								icons: { primary:"ui-icon-triangle-1-s" },
							});
						});
						
					} else {
						$("#menu2").animate({height:80}, 300, 
						function() { 
							showMenu=true;
							$("#menu2_scrollr").button({
								icons: { primary:"ui-icon-triangle-1-n" },
							});
							
						});
					}
				});
			} else {
				
			}
			
			$("#menu2_container div a").each(function() { //Fix ridiculous Chrome bug
				if (!$(this).parent().hasClass("active")) {
					$(this).css("text-decoration", "none");
				}
				
			});
			
			
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
	
		<div id="cookiecrumbs"><?= cookiecrumb() ?></div>
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
	