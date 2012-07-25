<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>The Daily Maverick<?php
		if (isset($title)) {
			print " :: $title";
		}
	?></title>

	<link rel="shortcut icon" href="<?= live_base_url() ?>images/favicon.ico" type="image/x-icon" />
	<link rel="icon" href="<?= live_base_url() ?>images/favicon.ico" type="image/x-icon" />
	
	<link rel="home" href="<?= live_base_url() ?>" title="Home" />

	
	<link rel="stylesheet" href="<?= live_base_url() ?>css/style.css" type="text/css" media="screen, projection" charset="utf-8" />
	<link rel="stylesheet" href="<?= live_base_url() ?>css/print.css" type="text/css" media="print" charset="utf-8" />
	<link type="text/css" href="<?= live_base_url() ?>js/jquery-ui-1.7.2.custom/css/smoothness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
	<link rel="alternate" type="application/rss+xml" title="The Daily Maverick" href="<?= live_base_url() ?>rss" />

	<?php
		if (isset($article)) {
	?>
	<meta name="title" content="<?= htmlentities($article->headline) ?>" />
	<meta name="description" content="<?= htmlentities(strip_tags($article->blurb)) ?>" />
	<link rel="image_src" href="<?= live_base_url()."photo/resize/{$article->photo_urlid}/130/90" ?>" />
	<meta name="medium" content="news" />
	<?php
		}
	?>
	<?php
		$this->load->view("publish/templates/includes/jquery");
	?>
	
</head>
<body>

	<div id="wrapper">
		<script language="JavaScript">
			$(function() {
				var showing=false;
				$("#dosearch").click(function() {
					if (showing) {
						$("#searchdialog").hide("slow");
						showing=false;
					} else {
						$("#searchdialog").show("slow");
						showing=true;
					}
					return false;
				});

				if($.browser.msie && $.browser.version=="6.0") {
					var winheight=$(window).height();
					$("#advert").css("height",winheight);
					var y = $(this).scrollTop();
					$("#advert").css("top",y);
				}
				
				$(window).scroll(function (event) {
					if($.browser.msie && $.browser.version=="6.0") {
						var y = $(this).scrollTop();
						$("#advert").css("top",y);
					}
				});
			});
		</script>
		<div id="header">
			<div class="menu">
				<div class='menu1'><a href='<?= live_base_url(); ?>'>home</a> | <a id="dosearch" href='#'>search</a></div>
				<div class='menu2'><?= live_anchor("page/contact-us","contact us"); ?> | <?= live_anchor("page/about-us","about us"); ?> | <?= live_anchor("page/advertise","advertise"); ?></div>
			</div>
			<div id="searchdialog" style="display:none; position: absolute; background-color: #FFF; padding-top: 13px; border: 1px #CCC solid; padding: 10px 10px 0px 10px">
				<form method="POST" action="<?= live_base_url() ?>search">
					
					<input type="text" name="searchstr" value="" style="width: 250px" />&nbsp;
					<input type="submit" name="submit" value="Search" class="button" />
				</form>
			</div>
			<div id="strapline"> &bull; <?= date("j F Y, H:i:s"); ?> (South Africa)</div>
			<div id="logo"><a href="<?= live_base_url(); ?>"><img src="<?= live_base_url() ?>images/masthead.png" /></a></div>
		</div>
		<div id="content">
