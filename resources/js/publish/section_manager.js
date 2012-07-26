var date_slider_options=new Array("Forever","2 years ago","1 year ago","6 months ago","2 months ago","1 month ago","2 weeks ago","1 week ago","2 days ago","1 day ago","8 hours ago","1 hour ago","Now");
var max_slider=12;
var min_slider=0;
var def_max_slider=12;
var def_min_slider=8;



function update_counter(){
	var count = $("#selected_items").children('li').length;
	$("#counter").html(count);
}

function can_add_more(){
	if($("#max_count").val() != 0){
		if($("#selected_items li").size() < $("#max_count").val()){
			return true;
		}else{
			return false;
		}
	}else{
		return true;
	}
	
}

function can_remove_more(){
	if($("#min_count").val() != 0){
		if($("#selected_items li").size() > $("#min_count").val()){
			return true;
		}else{
			return false;
		}
	}else{
		return true;
	}
}


function get_selected(){
	selecteds = [];
	if ($("#selected_items").length > 0){
  		$('#selected_items').children('li').each(function(idx, elm) {
  			selecteds.push(elm.id.split('=')[1])
		});
		return selecteds;
	}
	else{
		return "";
	}
	
}

function update_panel(all){
	d1=date_slider_options[$("#date_slider").slider( "values", 0)];
	d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
		
	if($("#active_zone").val() != ""){
		var searchstr = ($("#publishSearch").val() == "Search...") ? "" : $("#publishSearch").val() ;
		var selecteds = get_selected();
		var url = $("#active_zone").val()+"/"+d1+"/"+d2+"/"+searchstr;
		var params = {'selecteds[]': selecteds, "all":all}
		
		$("#loading_icon").show();
		$.get(url,params, function(data) {
			if ($("#selected_items").length > 0){
				$("#unselected_articles").html(data);
			}
			else{
				$("#the_display_panel").html(data);
			}
			$( "#selected_items" ).sortable({stop:function(event,ui){
				$(this).parent().addClass("staged");
				stage_changes();
			}});
			$( "#selected_items" ).disableSelection();
  			$("#loading_icon").hide();
  			update_counter();
		});

	}else{
		$("#the_display_panel").html("<h3 align='center'>Please select the zone...</h3>");
	}
		
}

function stage_changes(){
	update_counter();
	var post_data = {"content":get_selected(), "zone_name":$("#zone_name").val(), "zone_id":$("#zone_id").val(),}
		
		$.post("/publish/worker/stage_rank_section",post_data,function(data) {
				//$("#message_box").html(data).delay(2400);
				$("#message_box").html("You have staged changes, click update to save them");
		});

}


function search(){
		if ($("#selected_items").length > 0){
			update_panel(false);
		}else{
			update_panel(true);		
		}
	}
	
function reset(){
	$("#date_slider").slider("values",0,def_min_slider);
	$("#date_slider").slider("values",1,def_max_slider);
	$("#publishSearch").val("Search...");
	update_panel(false);
}


$(function() {

	$("#date_slider").slider({
	    range: true,
	    min: min_slider,
	    max: max_slider,
	    values: [ def_min_slider, def_max_slider ],
	    stop: function(event, ui) {
	    	if ($("#selected_items").length > 0){
				update_panel(false);
			}else{
				update_panel(true);		
			}
	    },
	    slide: function(event, ui) {
	    	$("#date_slider_value").html(date_slider_options[ui.values[0]]+" to "+date_slider_options[ui.values[1]]);
	    }
	});
	
	var d1=date_slider_options[$("#date_slider").slider( "values", 0 )];
	var d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
	try {
		$("#date_slider_value").html(d1+" to "+d2);
	} catch(err) {

	}
	
	$( "#reset_search" ).button({
            icons: {
                primary: "ui-icon-refresh"
            },
            text: false
     }).click(function(){
     	reset();
     });

	update_panel(true);
		
	
	$("#publishSearch").live("keyup", function(e){
		if(e.keyCode == '13'){
			search();
		}
		//var wait = setTimeout(search, 1000);
	});
	
	$("#publishSearch").live("focus", function(){
		if($(this).val() == "Search..."){
			$(this).val("");
		}
	});

	
	
	
	$('.move_over').live('click',function(){
		var pointer = $(this);
		if(can_add_more()){
			var content_type = $(this).parent().prev().html().toLowerCase();
			var content_id = $(this).parent().parent().attr("id").split('=')[1];
			
			$.get("/publish/worker/validate/"+content_type+"/"+content_id,function(data){
				if(data == "passed"){
					pointer.attr("title", "Move out of Section List");
					pointer.children(":first").removeClass("ui-icon-circle-arrow-e").next().html("Move out of Section List");
					pointer.children(":first").addClass("ui-icon-circle-arrow-w");
					pointer.removeClass("move_over");
					pointer.addClass("move_back");
    				$("#selected_items").prepend(pointer.parent().parent());
    				pointer.parent().parent().effect("pulsate", { times:3 }, 500);
    				if(!pointer.parent().parent().parent().hasClass("staged")){
						pointer.parent().parent().parent().parent().addClass("staged");
					}
    				stage_changes();
				}else{
					
					
					content = '<div style="padding: 5px" class="ui-state-error"><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Failed to publish content...</strong></p><p>'+data+'</p></div>';
					
					
					$("#msgdialog").html(content);
					$( "#msgdialog:ui-dialog" ).dialog( "destroy" );
					$( "#msgdialog" ).dialog({
							modal: true,
							height:350,
							width:700,
							buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
			});
			
		}else{
			$( "#informer:ui-dialog" ).dialog( "destroy" );
	
			$( "#informer" ).dialog({
					modal: true,
					height:200,
					width:400,
					buttons: {
					Ok: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	
	});
	
	$('.move_back').live('click',function(){
	
		if(can_remove_more()){
			if(!$(this).parent().parent().parent().hasClass("staged")){
				$(this).parent().parent().parent().parent().addClass("staged");
			}
			$(this).attr("title", "Move to Section List");
			$(this).children(":first").removeClass("ui-icon-circle-arrow-w").next().html("Move to Section List");
			$(this).children(":first").addClass("ui-icon-circle-arrow-e");
			$(this).removeClass("move_back");
			$(this).addClass("move_over");
    		$("#unselected_items").prepend($(this).parent().parent());
    		$(this).parent().parent().effect("pulsate", { times:3 }, 500);
    		stage_changes();
		}else{
			$( "#informer:ui-dialog" ).dialog( "destroy" );
	
			$( "#informer" ).dialog({
					modal: true,
					height:200,
					width:400,
					buttons: {
					Ok: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}

	});
	
	
	$("#doUpdate").click(function() {
		
		var post_data = {"content":get_selected(), "zone_name":$("#zone_name").val(), "zone_id":$("#zone_id").val(),}
		
		$.post("/publish/worker/rank_section",post_data,function(data) {
					content = "";
					console.log(data.indexOf('Updated'));
					if(data.indexOf('Updated') == -1){
						content = '<div style="padding: 5px" class="ui-state-error"><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Publish Results...</strong></p><p>'+data+'</p></div>';
					}else{
						content = '<div style="padding: 5px" class=""><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Publish Results...</strong></p><p>'+data+'</p></div>';
					}
					
					
					
					$("#msgdialog").html(content);
					$( "#msgdialog:ui-dialog" ).dialog( "destroy" );
					$( "#msgdialog" ).dialog({
							modal: true,
							height:200,
							width:450,
							buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
							}
						}
					});	
								
				//$("#message_box").html(data.msg).delay(2400).html();
				$("#selected_items").parent().removeClass("staged");
					
		});
	});
	
	$("#doRevert").click(function() {
		
		var post_data = {"zone_name":$("#zone_name").val(), "zone_id":$("#zone_id").val(),}
		
		$.post("/publish/worker/revert",post_data,function(data) {
				$("#selected_items").html(data).delay(2400).html();
				$("#selected_items").parent().removeClass("staged");
				$("#message_box").html('You have reverted this zone to the old list').delay(2400).html();
					
		});
	});
	
	
	$(".btn-edit").live("click", function(){
		window.open("/edit/"+$(this).parent().parent().attr("contenttype")+"/"+$(this).parent().parent().attr("urlid"));
	});
	
	$(".btn-workflowprev").live("click", function(){
		
		var element = $(this).parent().prev().prev().prev();
	  $.getJSON("/workflow/change/revert/"+$(this).parent().parent().attr("contenttype")+"/"+$(this).parent().parent().attr("urlid"), function(result) {
			remove_class = "content-workflow-"+(result.major_version + 1);
			add_class = "content-workflow-"+result.major_version;
			element.removeClass(remove_class);
			element.addClass(add_class);
			//to do something to reflect the change
	  });
	});
	
	$(".btn-workflownext").live("click", function(){
		
		var element = $(this).parent().prev().prev().prev();
	  $.getJSON("/workflow/change/advance/"+$(this).parent().parent().attr("contenttype")+"/"+$(this).parent().parent().attr("urlid"), function(result) {
			remove_class = "content-workflow-"+(result.major_version - 1);
			add_class = "content-workflow-"+result.major_version;
			element.removeClass(remove_class);
			element.addClass(add_class);
			
			//to do something to reflect the change

	  });
	});
	
	$(".btn-live").live("click", function(){
	
	var element = $(this).children(":first");
	//console.log(element.attr("class"));	
	$.getJSON("/workflow/change/togglelive/"+$(this).parent().parent().attr("contenttype")+"/"+$(this).parent().parent().attr("urlid"), function(result) {
			if(result.live == false){
				if(element.hasClass("ui-icon-check")){
					element.removeClass("ui-icon-check");
					element.addClass("ui-icon-close");
				}else{
					element.removeClass("ui-icon-close");
					element.addClass("ui-icon-check");
				}
				
			}
			if(result.live == true){
				if(element.hasClass("ui-icon-close")){
					element.removeClass("ui-icon-close");
					element.addClass("ui-icon-check");
				}else{
					element.removeClass("ui-icon-check");
					element.addClass("ui-icon-close");
				}
			}
			


			
	  });
	});
	
	
	//this.$(".options_close")
	
	
	$("#config_section_options").button({
		icons: {
        	primary: "ui-icon-triangle-1-s",
		},
        text: false,
	}).live("click", function(){
		$("#section_config").slideToggle();
	});
	
	
	
	$(".zone_selector").live("click", function(){
		$("#the_display_panel").html("");
		$("#section_config").slideToggle();
		var dest_class = ($(this).hasClass("auto_0")) ? "auto_0" : "auto_1";
		var rem_class = ($("#active_zone_display").hasClass("auto_0")) ? "auto_0" : "auto_1";
		$("#active_zone").val($(this).attr("href"));
		$("#active_zone_display").html($(this).html()).removeClass(rem_class).addClass(dest_class);
		update_panel(true);
		return false;
	});
	
	$('input[type="checkbox"][class="zone_automator"]').change(function() {
		
		var zone = $(this).attr("id");
		
		var automator = $(this);
		
     	if(this.checked) {
     	    //automate a zone
     	    var pointer = this;
     	    
     	   	
     	   	$( "#msgdialog" ).html("Are you sure you want to automate this zone?");
     	    $( "#msgdialog" ).dialog({
					resizable: false,
					height:140,
					width:380,
					modal: true,
					buttons: {
						"Automate": function() {
							$("#the_display_panel").html("");
							$( this ).dialog( "close" );
							$.get("/publish/worker/automate_zone/"+$("#section_id").val()+"/"+zone,function(data) {
	 							$("#message_box").html(data);
	 		 				});			
	 		 				automator.parent().next().children(":first").removeClass("auto_0");
	 		 				automator.parent().next().children(":first").addClass("auto_1");
	 		 				update_panel(true);

						},
						Cancel: function() {
							$( this ).dialog( "close" );
							pointer.checked = false;
						}
					}
			});			
     	    
     	    
     	    	 		 
	 		 
     	}else{
     		var pointer = this;
     		$( "#msgdialog" ).html("Are you sure you want to de-automate this zone?");
     	    $( "#msgdialog" ).dialog({
					resizable: false,
					height:140,
					width:380,
					modal: true,
					buttons: {
						"De-automate": function() {
							$("#the_display_panel").html("");
							$( this ).dialog( "close" );
							$.get("/publish/worker/de_automate_zone/"+zone,function(data) {
	 							$("#message_box").html(data);
	 		 				});
	 		 
	 		 				automator.parent().next().children(":first").removeClass("auto_1");
	 		 				automator.parent().next().children(":first").addClass("auto_0");
	 		 				update_panel(true);

						},
						Cancel: function() {
							$( this ).dialog( "close" );
							pointer.checked = true;
						}
					}
			});			
     	
     	}
     	
     	
     	     
 	}); 	

	$(".mass_selector").live("click", function(){
     	$("#the_display_panel").html("");
     	//get all checkboxes
     	if($(this).attr("option") == "all"){
     		$(".zone_automator").each(function(){
     			$(this).attr("checked", "checked");
     			$(this).parent().next().children(":first").removeClass("auto_0");
	 		 	$(this).parent().next().children(":first").addClass("auto_1");
     		});
     		//then make section auto
     		$.get("/publish/worker/automate_section/"+$("#section_id").val(),function(data) {
				$("#message_box").html(data);
		 	});
     		
     	}else{
     		$(".zone_automator").each(function(){
     			$(this).removeAttr("checked");
     			$(this).parent().next().children(":first").removeClass("auto_1");
	 		 	$(this).parent().next().children(":first").addClass("auto_0");

     		});
     		//then make section not auto
     		$.get("/publish/worker/de_automate_section/"+$("#section_id").val(),function(data) {
				$("#message_box").html(data);
		 	});
     	}
     	update_panel(true);
     });

	

	
});