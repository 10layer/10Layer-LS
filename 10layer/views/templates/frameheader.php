<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
	<title>10Layer CMS</title> 
 
	<link rel="shortcut icon" href="/tlresources/file/images/favicon.ico" type="image/x-icon" /> 
	<link rel="icon" href="/tlresources/file/images/favicon.ico" type="image/x-icon" /> 
	
	<link rel="home" href="<?= base_url() ?>" title="Home" />
 
	
	<link rel="stylesheet" href="/tlresources/file/css/style.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="/tlresources/file/jquery/jquery-ui-1.8.14.custom/css/smoothness/jquery-ui-1.8.14.custom.css" type="text/css" media="screen, projection" charset="utf-8" />
	
	<?= $this->autoloader->stylesheet(); ?>
	
	<script type="text/javascript" src="/tlresources/file/jquery/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="/tlresources/file/jquery/jquery.tools.min.js"></script>
	<script type="text/javascript" src="/tlresources/file/jquery/jquery-ui-1.8.14.custom/development-bundle/ui/jquery-ui-1.8.14.custom.js"></script>
	<script type="text/javascript" src="/tlresources/file/js/heartcode-canvasloader-min-0.9.js"></script>

	
	<script type="text/javascript" src="/tlresources/file/js/default.js"></script>
	<script type="text/javascript" src="http://<?= $this->config->item("comet_server") ?>:<?= $this->config->item("comet_port") ?>/static/Orbited.js"></script>
	<script type="text/javascript" src="http://<?= $this->config->item("comet_server") ?>:<?= $this->config->item("comet_port") ?>/static/protocols/stomp/stomp.js"></script>
	<script>  
		document.domain = document.domain; 
	</script>
	<script type="text/javascript">
		Orbited.settings.port = 8000;
		TCPSocket = Orbited.TCPSocket;
	</script>
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
			
			$("#menu1 div").each(function() {
				if ($(this).find("a").first().attr("href")=="<?= base_url().$menu1_active ?>") {
					$(this).addClass("active");
				} else {
					$(this).removeClass("active");
				}
			});
		});
	</script>
	<?= $this->autoloader->javascript(); ?>
</head> 
<body>
	<div id="container">
		<div id="canvasloader-container" class="wrapper"></div>
		<script type="text/javascript" src="/tlresources/file/js/loader.js"></script>
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