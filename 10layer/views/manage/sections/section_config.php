<?php
	$section_title=$section->getData()->title;
	link_js("/tlresources/file/jquery/jquery.form.js");
?>
<script>
	$(function() {
		$(".zone_config_show_button").button({
			icons: { 
				primary: "ui-icon-gear", 
				secondary: "ui-icon-triangle-1-s"
			},
		})
		
		$("#section_main").delegate(".zone_config_show_button", "click", function() {
			$(this).parent().next().slideToggle();
			return false;
		});
		
		$("#add_zone").button({
			icons: {
				primary: "ui-icon-circle-plus",
			}
		}).click(function() {
			var count=1;
			$(".zone").each(function() {
				count++;
			});
			$("#first_zone").clone().attr("id","").find(".content_type").remove().end().find(".title").val("<?= $section_title ?> Zone "+count).end().appendTo("#section_main");
			makeSortable();
		});
		
		$("#section_automatic").button({
			icons: {
				primary: "ui-icon-refresh",
			}
		}).click(function() {
			$(".zone_automatic").attr("checked", "checked");
		});
		
		$("#save").button({
			icons: {
				primary: "ui-icon-circle-check",
			}
		}).click(function() {
			$(".zone_content_types").each(function() {
				var s=$(this).sortable("toArray");
				//alert(s);
				$(this).next().val(s.join(","));
				
			});
			$("#mainform").submit();
		});
		
		$(".zone_remove_button").button({
			icons: {
				primary: "ui-icon-circle-minus",
			},
			text: false,
		});
		
		$("#section_main").delegate(".zone_remove_button","click",function() {
			$(this).parent().parent().find(".content_type").each(function() {
				$(this).detach().prependTo("#section_content_types");
			});
			$(this).parent().parent().remove();
		});
		
		$("#section_config").delegate("#mainform","submit", function() {
			$(this).ajaxSubmit({
				iframe: true,
				dataType: "json",
				beforeSubmit: function(a,f,o) { 
				    o.dataType = "json";
				},
				success: function(data) {
				    if (data.error) {
						//console.log(data);
						$("#msgdialog").html("<div class='ui-state-error' style='padding: 5px'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span><strong>"+data.msg+"</strong><br /><br /> "+data.info+"</p></div>");
						$("#msgdialog").dialog({
							modal: true,
							buttons: {
								Ok: function() {
									$( this ).dialog( "close" );
								}
							}
						});
					} else {
						$("#msgdialog").html("<div class='ui-state-highlight' style='padding: 5px'><p><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span><strong>Saved</strong></p></div>");
						$("#msgdialog").dialog({
							modal: true,
							buttons: {
								Ok: function() {
									$( this ).dialog( "close" );
								}
							}
						});
					}
				}						
			});
			return false;
		});
		
		function makeSortable() {
			$( ".zone_content_types" ).sortable({
				connectWith: ".connectedSortable",
			}).disableSelection();
			$("#section_content_types").sortable({
				connectWith: ".connectedSortable",
				 remove: function(event, ui) {
		            $(ui.item).clone().prependTo(event.target);
        		},
			});
		}
		
		makeSortable();
	});
</script>
<div id="msgdialog"></div>
<div id="section_config">
	<form id="mainform" method="POST" action="/manage/sections/dosave/<?= $section->urlid ?>">
	<div id="section_main">
		
		<div class="title"><?= $section_title ?></div>
		<div class="actionbar">
			<div id="add_zone">Add Zone</div>
			<div id="section_automatic">Make Section Automatic</div>
			<div id="save">Save</div>
		</div>
		<br clear="both" />
		<?php
			
			if (empty($zones)) {
		?>
		<div class="zone" id="first_zone">
			<input class="title" name="content_title[]" type="text" value="<?= $section_title ?> Zone 1" />
			<div class="zone_remove">
				<button class="zone_remove_button">Remove</button>
			</div>
			<div class="zone_config_show">
				<button class="zone_config_show_button ui-icon-gear">Config</button>
			</div>
			<div class="zone_config">
				
					<label>Automatic</label>
					<select name="section_zones_automatic[]" class="zone_automatic">
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select><br>
					<!-- <input type="checkbox" name="section_zones_automatic[]" class="zone_automatic" /><br /> -->
					<label>Max Items</label>
					<input type="text" name="section_zones_max_count[]" /><br />
					<label>Min Items</label>
					<input type="text" name="section_zones_min_count[]" /><br />
					<label>Where Statement</label>
					<input type="text" name="section_zones_auto_where[]" /><br />
					<label>Limit</label>
					<input type="text" name="section_zones_auto_limit[]" /><br />
					<label>Order By</label>
					<input type="text" name="section_zones_auto_order_by[]" /><br />
				
			</div>
			<div class="zone_content_types_header">Content Types</div>
			<div class="zone_content_types connectedSortable">
			</div>
			<input type="hidden" name="section_zones_content_types[]" value="" />
		</div>
		<?php
			} else {
				$first=true;
				foreach($zones as $zone) {
		?>
		<div class="zone" <?php if ($first) { ?> id="first_zone"<?php $first=false; } ?> >
			<input class="title" name="content_title[]" type="text" value="<?= $zone->title ?>" />
			<div class="zone_remove">
				<button class="zone_remove_button">Remove</button>
			</div>
			<div class="zone_config_show">
				<button class="zone_config_show_button ui-icon-gear">Config</button>
			</div>
			<div class="zone_config">
				
					<label>Automatic</label>
					
					<select name="section_zones_auto[]" class="zone_auto">
						<option <?php echo ($zone->auto == 1) ? "selected='selected'" : ""; ?> value="1">Yes</option>
						<option <?php echo ($zone->auto == 0) ? "selected='selected'" : ""; ?> value="0">No</option>
					</select>
					<!-- <input type="checkbox" name="section_zones_automatic[]" class="zone_automatic" /> --><br />
					<label>Max Items</label>
					<input type="text" name="section_zones_max_count[]" value="<?= $zone->max_count ?>" /><br />
					<label>Min Items</label>
					<input type="text" name="section_zones_min_count[]" value="<?= $zone->min_count ?>" /><br />
					<label>Where Statement</label>
					<input type="text" name="section_zones_auto_where[]" value="<?= $zone->auto_where ?>" /><br />
					<label>Limit</label>
					<input type="text" name="section_zones_auto_limit[]" value="<?= $zone->auto_limit ?>" /><br />
					<label>Order By</label>
					<input type="text" name="section_zones_auto_order_by[]" value="<?= $zone->auto_order_by ?>" /><br />
				
			</div>
			<div class="zone_content_types_header">Content Types</div>
			<div class="zone_content_types connectedSortable">	
				<?php
					$cts=explode(",",$zone->content_types);
					foreach($cts as $ct) {
						foreach($content_types as $content_type) {
							if ($content_type->urlid==$ct) {
						
				?>
							<div class="content_type" id="<?= $content_type->urlid ?>"><?= $content_type->name ?></div>	
				<?php
							}
						}
					}
				?>
			</div>
			<input type="hidden" name="section_zones_content_types[]" value="<?= $zone->content_types ?>" />
		</div>
		<?php
				}
			}
		?>
	</div>
	</form>
	<div id="section_content_types" class="connectedSortable">
		<?php
			foreach($content_types as $content_type) {
		?>
		<div class="content_type" id="<?= $content_type->urlid ?>"><?= $content_type->name ?></div>
		<?php
			}
		?>
	</div>
</div>