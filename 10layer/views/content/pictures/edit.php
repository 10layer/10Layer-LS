<div id="create-content" class="boxed wide">
	
	<form id="picture-form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="action" value="submit" />
		<?php 
			$this->formcreator->drawFields();
		?>
		<br />
		<input type="submit" name="submit" value="Update Picture" class="button" />
		<br clear="both" />
		<a href="/delete/picture/<?= $urlid ?>" class="button">Delete Picture</a>
		<br clear="both" />
	</form>
</div>
<div id="sidebar">
	<div class="actions">
		<a id="img-rotate-cc" href="#" class="button" >Rotate Counter-Clockwise</a>
	</div>
	<img id="preview" src="/workers/picture/display/<?= $urlid ?>/scaleImage/260/0" />
	<br clear="both" />
</div>
<br clear="both" />
