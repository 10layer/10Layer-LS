<?php
	link_js("/tlresources/file/js/publish/section_manager.js");
?>

  
<div id="msgdialog"></div>

<?php

if(isset($zones[0])){

?>

<div id="controlls">

<input type="hidden" id="active_zone" value="/publish/worker/subsection/<?= $section_urlid ?>/<?= $zones[0]->urlid ?>">

<div id="btn_submit">

<div id="config_container">
<!-- ===================================== -->
<div id="config_section_container">

	<a id="config_section"> Publishing to <?php echo $section_data->title; ?> </a> > 
	<span id="active_zone_display" class="auto_<?= $zones[0]->auto ?>"><?php echo $zones[0]->title; ?></span> 
	
	<span style="height:12px; width:12px;" id="config_section_options">Options</span>
	
</div>



<div id="section_config" class="shadow">

 <div id="section_automator">
 	Automate -> <a option="all" class="mass_selector">All</a> | <a option="none" class="mass_selector">None</a>
 </div>

	<div id="zone_automators">
	
	<table class="small_table">
		<tr>
			<th>Automate</th>
			<th>Zone</t>
		</tr>
		
		<?php 
		foreach($zones as $zone){
		?>
			
		<tr>
			<td align="center"><input type="checkbox" class="zone_automator" <?php echo ($zone->auto == "0") ? "" : "checked='checked'" ?> id="<?= $zone->content_id ?>"></td>
			<th><a class="zone_selector auto_<?= $zone->auto ?>" href="/publish/worker/subsection/<?= $section_urlid ?>/<?= $zone->urlid ?>"><?= $zone->title ?></a></t>
		</tr>
			
				
		<?php
		}
			
		?>
	
	</table>
	
	</div>


 </div>

</div>
<!-- ===================================== -->


</div>
	
	
	
	<div id="date_slider_container">
		<div id="date_slider_value"></div>
		<div id="date_slider"></div>
	</div>
	<div id="search">
		<input style="padding-left:3px;float:left;border: 1px solid #757474;height:20px;" type="text" id="publishSearch" value="Search..." title="Hit Enter key to search" />
		<span id="loading_icon" style="float:left; margin:5px 10px; display:none;">
			<img src="/tlresources/file/images/loader.gif" />
		</span>
		<span style="width:20px;height:20px;margin-left:10px;" id="reset_search">Reset</span>

	</div>
	
</div>
<br clear="both" />
<br />

<div id="the_display_panel">

	
	
</div>

<div class="message_box" id="message_box">  </div>

<div id="btn_submit">
		<button aria-disabled="false" role="button" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " id="doRevert"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-w"></span>Revert</span></button>
		
		<button aria-disabled="false" role="button" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " id="doUpdate"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-check"></span>Update</span></button>
	</div>

<form method="post" id="update_form">
	<input type="hidden" id="section_id" name="section_id" value="<?= $section_id ?>" />
</form>



<?php

}else{
?>

<div class="big_error_message">
	The  system has found that there are no zones defined for the selected section, Please contact the System Administrator about this issue.
</div>

<?php

}

?>

