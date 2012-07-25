<?php
						
					foreach($items as $item) {		
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

			?>