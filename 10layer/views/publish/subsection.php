<script language="javascript">
	document.domain=document.domain;
</script>

<br style="clear:both" />

<div id="informer" title="Prohibited Action" style="display:none;"> 
<div class="small_notice">
	This zone( <?= $zone->title ?> ) can have a minimum of <?= $zone->min_count ?> entries and a maximum of <?= $zone->max_count ?>. Please review this zone's settings and try again.
</div>

</div>

<input type="hidden" id="zone_name" value="<?= $zone->title ?>">
<input type="hidden" id="max_count" value="<?= $zone->max_count ?>">
<input type="hidden" id="min_count" value="<?= $zone->min_count ?>">
<input type="hidden" id="zone_id" value="<?= $zone->urlid ?>">
<input type="hidden" id="section_id" value="<?= $section_id ?>">


<?php if($all == "true"){ ?>
<div id="unselected_articles">

<?php }
if(sizeof($content) < 1 AND $zone->auto == 0)
{
	echo "<div class='big_error_message'> There are no results for your specified criteria, please refine it...</div>";
}

?>

<?php
	if($zone->auto == 0){
?>


	<ul id="unselected_items" class="simple_sortable_items sortable">
		<?php
			foreach($content as $item) {
		?>
			<li title="<?= ($item->title != '' ) ? $item->title : $item->urlid ?>" class="sectionrow" id="content=<?= $item->id ?>" contenttype="<?= $item->contenttype ?>" urlid="<?= $item->urlid ?>">
			
					

				<img style="float: left; margin-right: 5px" src="/workers/picture/display/<?= $item->id ?>/cropThumbnailImage/50/40" />
				
<div class="content-title content-workflow-<?= $item->major_version ?>">
	<?= clean_blurb(($item->title != '' ) ? $item->title : $item->urlid, 25) ?>
</div>
				

<br clear="both" />
<div class="content_type_label"><?= ucfirst($item->contenttype) ?></div>

<div class="content-tools" >
				

				<div  class="btn-edit ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Edit"><span class="ui-button-icon-primary ui-icon ui-icon-pencil"></span><span class="ui-button-text">Edit</span></div>
				<div  class="btn-workflowprev ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Revert Workflow"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthick-1-w"></span><span class="ui-button-text">Revert Workflow</span></div>
				<div  class="btn-workflownext ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Advance Workflow"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthick-1-e"></span><span class="ui-button-text">Advance Workflow</span></div>
				<div  class="btn-live ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="<?php echo ($item->live == 1) ? "Make Unlive" : "Make Live" ; ?>">
			<span class="ui-button-icon-primary ui-icon <?php echo ($item->live == 1) ? "ui-icon-close" : "ui-icon-check" ; ?>"></span>
			
			<span class="ui-button-text"><?php echo ($item->live == 1) ? "Make Unlive" : "Make Live" ; ?></span></div>
			
			<div class="move_over ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Move to the section list"><span class="ui-icon ui-icon-circle-arrow-e"></span><span class="ui-button-text">Move to the section list</span>
</div>
			
		</div>
<br clear="both">




			</li>
		<?php
			}
		?>
		</ul>
		
		<?php
	}else{
?>
	<div class="auto_note">This is an AUTO ZONE, The items in here cannot be edited</div>
<?php
	}
?>

		
		
		
<?php if($all == "true"){ ?>
</div>
<?php } ?>



<!--  =========================================== separation of concerns ========================== -->

<?php

 if($all == "true"){ ?>	

<div id="selected_articles" class="<?php echo $staged; ?>">
<div id="counter_display"> <span id="counter">0</span> <br> items </div>

		<ul id="selected_items" class="simple_sortable_items sortable">
				
			<?php
						
				if (is_array($published_articles)) {
					foreach($published_articles as $item) {		
			?>		
					<li title="<?= ($item->title != '' ) ? $item->title : $item->urlid ?>" class="sectionrow" id="content=<?= $item->id ?>" contenttype="<?= $item->content_type_urlid ?>" urlid="<?= $item->urlid ?>">
					
					<img style="float: left; margin-right: 5px" src="/workers/picture/display/<?= $item->urlid ?>/cropThumbnailImage/50/40" />
						
 <div class="content-title content-workflow-<?= $item->major_version ?>"><?= clean_blurb(($item->title != '' ) ? $item->title : $item->urlid, 25) ?></div>
<br clear="both" />					
<div class="content_type_label"><?= ucfirst($item->content_type_urlid) ?></div>
					
					<div class="content-tools" >
			<div  class="btn-edit ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Edit"><span class="ui-button-icon-primary ui-icon ui-icon-pencil"></span><span class="ui-button-text">Edit</span></div>
			<div class="btn-workflowprev ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Revert Workflow"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthick-1-w"></span><span class="ui-button-text">Revert Workflow</span></div>
			<div class="btn-workflownext ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Advance Workflow"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthick-1-e"></span><span class="ui-button-text">Advance Workflow</span></div>
			<div class="btn-live ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="<?php echo ($item->live == 1) ? "Make Unlive" : "Make Live" ; ?>">
			<span class="ui-button-icon-primary ui-icon <?php echo ($item->live == 1) ? "ui-icon-close" : "ui-icon-check" ; ?>"></span>
			
			<span class="ui-button-text"><?php echo ($item->live == 1) ? "Make Unlive" : "Make Live" ; ?></span></div>
			
			<?php if($zone->auto == 0){ ?>
<div class="move_back ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" title="Move out of the section list"><span class="ui-icon ui-icon-circle-arrow-w"></span><span class="ui-button-text">Move out of the section list</span>
</div>
<?php } ?>
					</li>
			<?php
						}
				}
			?>
			
		</div>

			</ul>

</div>

<?php } ?>



