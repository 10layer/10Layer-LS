<script language="javascript">
	var contentType="<?= $contenttype ?>";
	var urlId="<?= $urlid ?>";
	var contentId="<?= $id ?>";
	var contentTypeId="<?= $contenttype_id ?>";
</script>
<div id="create-content" class="boxed wide">
<h2><?php echo $heading; ?></h2>
	
	<form id="contentform" method="post" enctype="multipart/form-data" action="/edit/ajaxsubmit/<?= $contenttype ?>/<?= $urlid ?>">
		<input type="hidden" name="action" value="submit" />
		<?php 
			$this->formcreator->drawFields();
		?>
		<br />
		<br clear="both" />
	</form>
</div>
<div id="sidebar" class="pin">
	<script type="text/javascript" src="/tlresources/file/jquery/jquery-ui-1.8.14.custom/development-bundle/ui/jquery.ui.accordion.js"></script>
	<script type="text/javascript">
		$(function() {
			$("#dosubmit_right").click(function() {
				$("#contentform").submit();
				$("#autosave").slideUp();
			});
			$("#dodone_right").click(function() {
				$("#contentform").submit();
				$.ajax({ type: "GET", url: "<?= base_url() ?>/workflow/change/advance/<?= $contenttype ?>/<?= $urlid ?>", async:false});
				location.href="/workers/content/unlock/<?= $contenttype ?>/<?= $urlid ?>";
			});
			$("#dodelete_right").click(function() {
				$("#dialog-delete-confirm").dialog({
					resizable: false,
					height:140,
					modal: true,
					buttons: {
						"Delete": function() {
							location.href="/delete/<?= $contenttype ?>/<?= $urlid ?>";
						},
						Cancel: function() {
							$(this).dialog("close");
						}
					}
				});
			});
			$(window).unload(function() {
				$.ajax({ type: "GET", url: "<?= base_url() ?>/workers/content/unlock/<?= $contenttype ?>/<?= $urlid ?>", async:false});
			});
			$("#workflows").load("/workflow/change/status/<?= $contenttype ?>/<?= $urlid ?>");
			$("#workflow_next").live("click", function() {
				$.getJSON("/workflow/change/advance/<?= $contenttype ?>/<?= $urlid ?>", function() {
					$("#workflows").load("/workflow/change/status/<?= $contenttype ?>/<?= $urlid ?>");
				});
			});
			$("#workflow_revert").live("click", function() {
				$.getJSON("/workflow/change/revert/<?= $contenttype ?>/<?= $urlid ?>", function() {
					$("#workflows").load("/workflow/change/status/<?= $contenttype ?>/<?= $urlid ?>");
				});
			});
			
			$("#sidebar_accordian").accordion({
				autoHeight: false
			});
			
			$("#dofork_right").click(function() {
				$("#link_action").val("fork");
				$("#link_targets").dialog();
			});
			
			$("#dolink_right").click(function() {
				$("#link_action").val("link");
				$("#link_targets").dialog();
			});
			
			$("#revert_original").click(function() {
				$.ajax({ type: "GET", url: "<?= base_url()."edit/clear_autosave/$contenttype/$urlid" ?>", async: true});
				$("#dyncontent").load("<?= base_url()."edit/fullview/$contenttype/$urlid" ?>", function() {
					$(".datepicker").datepicker({dateFormat:"yy-mm-dd"});
					if ($(".richedit").length) {
						init_tinymce();
					}
				});
			});
			
			$("#dolink_submit").click(function() {
				$("#link_form").ajaxSubmit({
					beforeSubmit: function(a,f,o) { 
						o.dataType = "json";
					},
					success: function(data) {
						//console.log(data);
						$("#msgdialog").html("<div class='ui-state-error' style='padding: 5px'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span><strong>"+data.msg+"</strong><br /><br /> "+data.info+"</p></div>");
						$("#msgdialog").dialog({
							modal: true,
							buttons: {
								Ok: function() {
									$(this).dialog("close");
								}
							}
						});
					}						
				});
				$("#link_targets").dialog("close");
				return false;
			});
		});
	</script>
<?php
	link_js("/tlresources/file/js/forms.js");
?>
	<div id="dialog-delete-confirm" title="Confirm Delete" style="display: none">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>
	</div>
	<div id="link_targets" style="display: none">
		<form id="link_form" action="/workflow/fork/dofork" method="POST">
			<input id="link_action" type="hidden" name="action" value="" />
			<input type="hidden" name="content_id" value="<?= $id ?>" />
			<?php
				foreach($this->publications->getAll() as $publication) {
				?>
				<div class="bold"><?= $publication->name ?></div>
				<?php
					$platforms=$this->model_platforms->get_all($publication->id);
					foreach($platforms as $platform) {
					?>
						<input name="platform[]" style="margin-top: 4px" type="checkbox" value="<?= $platform->id ?>" /><?= $platform->name ?><br />
					<?php
					}
				}
			?>
			<br />
			<button id="dolink_submit" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-check"></span>Done</button><br />
		</form>
		<br />
	</div>
	<div id="sidebar_accordian">
		<h3><a href="#">Actions</a></h3>
		<div>
			<button id="dodone_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-check"></span>Done</button><br />
			<br />
			<button id="dosubmit_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span>Save</button><br />
			<br />
			<!--<button id="dodelete_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-trash"></span>Delete</button><br />-->
			
		</div>
		<h3><a href="#">Versions</a></h3>
		<div>
			<button id="dofork_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthickstop-1-n"></span>Fork</button><br />
			<br />
			<button id="dolink_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-link"></span>Link</button><br />
			<?= $this->versions->get_version(); ?><br />
			
		</div>
		<h3><a href="#">Workflow</a></h3>
		<div id="workflows">
	
		</div>
	</div>
	<?php
		$autosavestyle="";
		if (!$autosaved) {
			$autosavestyle="style='display:none'";
		}
	?>
	<div id="autosave" <?= $autosavestyle ?>>
		<h4>This document has been autosaved.</h4>
		<button id="revert_original" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-w"></span>Revert to original</button><br />
		
	</div>
</div>
<br clear="both" />
