var date_slider_options=new Array("Forever","2 years ago","1 year ago","6 months ago","2 months ago","1 month ago","2 weeks ago","1 week ago","2 days ago","1 day ago","1 hour ago","Now");
var max_slider=11;
var min_slider=0;
var def_max_slider=11;
var def_min_slider=8;

function updateTab(skipLoad) {
	var d1=date_slider_options[$("#date_slider").slider( "values", 0 )];
	var d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
	if (!d1) {
		d1=date_slider_options[def_min_slider];
	}
	if (!d2) {
		d2=date_slider_options[def_max_slider];
	}
	$("#date_slider_value").html(d1+" to "+d2);
	var tabIndex=$("#sectiontabs").tabs("option","selected");
	var searchstr="None";
	try {
		searchstr=$("#publishSearch").val();
		//console.log(searchstr);
	} catch(err) {

	}
	var section=$("#sectiontabs .ui-tabs-selected a").attr("section");
	var subsection=$("#sectiontabs .ui-tabs-selected a").attr("subsection");
	var url="/publish/worker/subsection/"+section+"/"+subsection+"/"+d1+"/"+d2+"/"+searchstr;
	//console.log("Create "+url);
	$("#sectiontabs").tabs("url", tabIndex, url);
	if (!skipLoad) {
		$("#sectiontabs").tabs("load", tabIndex);
	}
}

$(function() {
	/*$(".ajax-content .sectionrow").each(function() {
		$(this).html('<td><img src="/resources/images/ajax-loader.gif" /></td>');
		$(this).load("/workers/content/view/str/"+$(this).attr("urlid"));
	});*/
	
	$(".ajax_loader").each(function() {
		var section=$(this).attr("section");
		var zone=$(this).attr("zone");
		d1=date_slider_options[def_min_slider];
		d2=date_slider_options[def_max_slider];
		searchstr="";
		var url="/publish/worker/subsection/"+section+"/"+zone+"/"+d1+"/"+d2+"/"+searchstr
		$(this).attr("href",url);
	});
	
	$(".sortable").sortable({
		connectWith: "ul"
	});
	
	$(".sortable").disableSelection();
	
	$("#doUpdate").click(function() {
		$(".subsection").each(function() {
			var serial=$(this).sortable("serialize",{expression:/(.+)=(.+)/})+"&section_id="+$("#section_id").val()+"&zone_id="+$(this).attr("zone_id")+"&zone_name="+$(this).attr("zone_name");
			$.post(
				"/publish/worker/rank_section",
				serial,
				function(data) {
					$("#message").clone().html(data).removeAttr("id").insertAfter("#message").slideDown("slow").delay(2400).slideUp("slow", function() {
						$(this).remove();
					});
				}
			);

		});
		return false;
	});
	
	
	
	$("#sectiontabs").tabs({
		show: function(event, ui) {
	    	updateTab(false);
	    },
	    ajaxOptions: {
	    	
	    	complete: function() {
	    		$(".sortable").sortable({
	    			connectWith: "ul"
	    		});
	    	}
	    }
	});
	
	$("#date_slider").slider({
	    range: true,
	    min: min_slider,
	    max: max_slider,
	    values: [ def_min_slider, def_max_slider ],
	    stop: function(event, ui) {
	    	updateTab();
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
	
	$("#publishSearch").live("keyup",function() {
		clearTimeout($.data(this, 'timer'));
		var wait = setTimeout(updateTab, 1000);
		$(this).data('timer', wait);
	});
	
	
	//$("#subsections").tabs();
	/*$("#sectiontable").delegate(".showTooltip","hover",function() {
		$(this).prev().toggleClass("visible");
	});*/
	
	
});