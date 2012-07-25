<script language="javascript">
	$(function() {
		$(".button").each(function() {
			$(this).button();
		});
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").dialog({
			autoOpen: false,
			height: 600,
			width: 700,
			modal: true,
		});
		
		$(".btnContentSelect").click(function() {
			
			$("#contentselect_<?= $field->tablename."_".$field->name ?>").load("/list/<?= $field->contenttype ?>/<?php ($field->multiple)? print "1" : print "0" ?>",function() {
				search();
				populateCreate("<?= $field->contenttype ?>");
			});
			
			$("#contentselect_<?= $field->tablename."_".$field->name ?>").dialog("open");
		});
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate("a", "click", function() {
			var url=$(this).attr("href");
			$.getJSON(url, function(data) {
				renderPopup(data, "contentselect_<?= $field->tablename."_".$field->name ?>", "<?= $field->contenttype ?>");
			});
			return false;
		});
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate(".singleselect", "click", function() {
			$("#contentselect_<?= $field->tablename."_".$field->name ?>").dialog("close");
		});
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate(".item-select", "click", function() {
			if ($(this).hasClass("singleselect")) {
				$("#<?= $field->tablename."_".$field->name ?>_field").val($(this).val());
				
				updateResults_<?= $field->tablename."_".$field->name ?>();
			}
		});
		
		function updateResults_<?= $field->tablename."_".$field->name ?>() {
			var id=$("#<?= $field->tablename."_".$field->name ?>_field").val();
			if (id) {
				$("#displayResults_<?= $field->tablename."_".$field->name ?>").empty();
				$("#displayResults_<?= $field->tablename."_".$field->name ?>").load("/list/<?= $field->contenttype ?>/item/"+id);
				
			}
		}
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate(".popupSearch", "click", function() {
			if ($(this).val()=="Search...") {
				$(this).val("");
			}
		});
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate(".popupSearch", "keypress", function() {
			clearTimeout($.data(this, 'timer'));
			var wait = setTimeout(search, 500);
			$(this).data('timer', wait);
		});
		
		function search() {
			$(".popupWorking").show();
			var s=$("#contentselect_<?= $field->tablename."_".$field->name ?>").find(".popupSearch").val();
			$.getJSON("/list/<?= $field->contenttype ?>/search/"+escape(s), function(data) {
				renderPopup(data, "contentselect_<?= $field->tablename."_".$field->name ?>", "<?= $field->contenttype ?>");
			});
		}
		
		function populateCreate(type) {
			$("#contentselect_<?= $field->tablename."_".$field->name ?>").find("#contentcreate").load(
				"/create/fullview/"+type+"/embed"
			);
		}
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate("#contentform","submit",function() {
			var this_container = $(this).parent().parent();
			$(this).ajaxSubmit({
				iframe: true,
				
				beforeSubmit: function(a,f,o) { 
					o.dataType = "json"; 
					
				},
				success: function(data) {
					if (data.id) {
						$("#displayResults_<?= $field->tablename."_".$field->name ?>").empty();
						$("#displayResults_<?= $field->tablename."_".$field->name ?>").load("/list/<?= $field->contenttype ?>/item/"+data.id);
						$("#<?= $field->tablename."_".$field->name ?>_field").val(data.id);
						$("#contentselect_<?= $field->tablename."_".$field->name ?>").dialog("close");
					}
					if (data.error) {
						//console.log(data);
						$("#msgdialog").html("<div class='title'>"+data.msg+"</div> "+data.info);
						$("#msgdialog").dialog({
							modal: true,
							buttons: {
								Ok: function() {
									$( this ).dialog( "close" );
								}
							}
						});
					}else{
						//console.log(data);
						$("#msgdialog").html("<div class='title'>"+data.msg+"</div> "+data.info);
						$("#msgdialog").dialog({
							modal: true,
							buttons: {
								Ok: function() {
									$( this ).dialog( "close" );
								}
							}
						});
						
						this_container.slideToggle("fast", function() {
							//console.log(this_container.attr("id"));
							
							/*
if (this_container.parent().is(":visible")) {
								//console.log("Visible");
								this_container.prev().find(".ui-icon").removeClass("ui-icon-circle-triangle-e").addClass("ui-icon-circle-triangle-s");
							} else {
								//console.log("Invisible");
								this_container.prev().find(".ui-icon").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-e");
							}
*/
						});		

						
					}
					
					
					
					
					
					
					
				}
			});
			return false;
		});
		
		updateResults_<?= $field->tablename."_".$field->name ?>();
		
		$("#contentselect_<?= $field->tablename."_".$field->name ?>").delegate(".dropdown-header","click", function() {
			$(this).next().slideToggle("fast", function() {
				if ($(this).is(":visible")) {
					//console.log("Visible");
					$(this).prev().find(".ui-icon").removeClass("ui-icon-circle-triangle-e").addClass("ui-icon-circle-triangle-s");
				} else {
					//console.log("Invisible");
					$(this).prev().find(".ui-icon").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-e");
				}
			});
		});
		
	});
	
	function renderPopup(data, containerid, contenttype) {
		$("#"+containerid).find(".popupResultsCount").html("Found "+data.count+" results");
		if ($("#contentselect_<?= $field->tablename."_".$field->name ?>").find(".popupSearch").val()) {
			$("#"+containerid).find(".popupResultsClear").html("<a href='/list/<?= $field->contenttype ?>/search'>Clear</a>").click(function() {
				$(".popupWorking").show();
				$("#contentselect_<?= $field->tablename."_".$field->name ?>").find(".popupSearch").val("");
				$(this).html("");
			});
		}
		$("#"+containerid).find(".popupPagination").html(data.pagination);
		$("#"+containerid).find(".content_row").remove();
		for(x=0;x<data.docs.length;x++) {
		    doc=data.docs[x];
		    
		    $("#"+containerid)
		    	.find("tr.template")
		    	.clone()
		    	.appendTo("."+contenttype+"-content")
		    	.removeClass("template")
		    	.addClass('content_row_'+x)
		    	.addClass("content_row")
		    	.find(".item-select").val(doc.id).end()
		    	.find(".content_editlink")
		    	.html("<a href='/edit/"+contenttype+"/"+doc.urlid+"' target='blank'>Edit</a>")
		    	.end()
		    ;
		    for (var i in doc) {
		    	$("#"+containerid).find(".content_row_"+x).find("."+i).html(doc[i]);
		    	$("#"+containerid).find(".content_row_"+x).find(".content_"+i).html(doc[i]);
		    }
		    $("#"+containerid).find(".content_row_"+x).find(".content_img").html(
		    	"<img src='/workers/picture/display/"+doc.urlid+"/cropThumbnailImage/40/30' />"
		    );
		    if (x%2==0) {
		    	$("#"+containerid).find(".content_row_"+x).addClass("odd");
		    }
		}
		$(".popupWorking").hide();
		
		
	}
	
</script>



