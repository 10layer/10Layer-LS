<!DOCTYPE html> 
<html lang="en-gb" charset="UTF-8">
<head> 
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
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