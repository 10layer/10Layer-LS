<script language="javascript">
	var contenttype="<?= $contenttype ?>";
</script>
<?php
	link_js("/tlresources/file/js/forms.js");
	link_js("/tlresources/file/jquery/jquery.form.js");
	link_js("/tlresources/file/js/forms/default.js");
?>
<div id="create-content" class="boxed wide">
<h2><?php echo $heading; ?></h2>
	<form id="contentform" method="post" enctype="multipart/form-data" action="/create/ajaxsubmit/<?= $contenttype ?>">
		<input type="hidden" name="action" value="submit" />
		<?php 
			$this->formcreator->drawFields();
		?>
		
		<br clear="both" />
		<?php
			$embed=$this->uri->segment($this->uri->total_segments());
			if ($embed=="embed") {
		?>
			<input type="submit" class="button" name="btn_submit" value="Create" />
		<?php
			}
		?>
	</form>
	
</div>
<?php
	if ($embed!="embed") {
?>
<div id="sidebar" class="pin">
		<script type="text/javascript" src="/tlresources/file/jquery/jquery-ui-1.8.14.custom/development-bundle/ui/jquery.ui.accordion.js"></script>
		<script type="text/javascript">
		$(function() {
			$("#dodone_right").click(function() {
				$("#contentform").submit();
			});
			
			$("#sidebar_accordian").accordion({
				autoHeight: false
			});
		});
		</script>
		<div id="sidebar_accordian">
			<h3><a href="#">Actions</a></h3>
			<div>
				<button id="dodone_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-check"></span>Create</button><br />
				<br />
			</div>
		</div>
	</div>
<?php
	}
?>