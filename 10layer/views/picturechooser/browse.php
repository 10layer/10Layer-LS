<?php
	$contenttype="picture";
	$tablename="pictures";
	$name="picture";
	$segs=$this->uri->segment_array();
	
	$qs=$_SERVER["QUERY_STRING"];
	parse_str($qs, $parts);
	
?>
<script language="javascript">
	document.domain=document.domain;
	//console.log(window.top.opener.CKEDITOR);
	$(function() {
		
		$("#picselect").ajaxComplete(function() {
			cl.hide();
		});
		
		$("#picselect").ajaxStart(function() {
			cl.show();
		});
		
		$("#picselect").load("/list/picture/0",function() {
			search();
			populateCreate();
		});
			
		
		function search() {
			var s=$("#picselect").find(".popupSearch").val();
			$.getJSON("/list/picture/search/"+escape(s), function(data) {
				renderPopup(data, "picselect", "<?= $contenttype ?>");
			});
		}
		
		$("#picselect").delegate(".popupPagination a", "click", function() {
			var url=$(this).attr("href");
			$.getJSON(url, function(data) {
				renderPopup(data, "picselect", "<?= $contenttype ?>");
			});
			return false;
		});
		
		$("#picselect").delegate(".singleselect", "click", function() {
			$("#picselect").dialog("close");
		});
		
		$("#picselect").delegate(".item-select", "click", function() {
			
			val=$(this).val();
			title=$(this).parent().parent().find("content_title").html();
			//test();
			//opener.test();
			window.opener.CKEDITOR.tools.callFunction('<?= $parts["CKEditorFuncNum"] ?>', "/workers/picture/display/"+val+"/scaleImage/300/300/true",  function() {
				
				// Get the reference to a dialog window.
				var element, dialog = this.getDialog();
				// Check if this is the Image dialog window.
				if (dialog.getName() == 'image') {
					// Get the reference to a text field that holds the "alt" attribute.
					element = dialog.getContentElement( 'info', 'txtAlt' );
					// Assign the new value.
					if ( element )
						element.setValue(title);
				}
			});
			
			window.close();
			//location.href="/workers/picturechooser/edit/"+val;
		});
		
		function updateResults_<?= $tablename."_".$name ?>() {
			var id=$("#<?= $tablename."_".$name ?>_field").val();
			if (id) {
				$("#displayResults_<?= $tablename."_".$name ?>").empty();
				$("#displayResults_<?= $tablename."_".$name ?>").load("/list/<?= $contenttype ?>/item/"+id);
			}
		}
		
		$("#picselect").delegate(".popupSearch", "click", function() {
			if ($(this).val()=="Search...") {
				$(this).val("");
			}
		});
		
		$("#picselect").delegate(".popupSearch", "keypress", function() {
			clearTimeout($.data(this, 'timer'));
			var wait = setTimeout(search, 500);
			$(this).data('timer', wait);
		});
		
		
		
		function populateCreate() {
			$("#contentcreate").load(
				"/create/fullview/picture"
			);
		}
		
		$("#contentcreate").delegate("#contentform","submit",function() {
			$(this).ajaxSubmit({
				iframe: true,
				
				beforeSubmit: function(a,f,o) { 
					o.dataType = "json";
				},
				success: function(data) {
					if (data.id) {
						//console.log(data);
						val=data.data.urlid;
						title=data.data.title;
						window.opener.CKEDITOR.tools.callFunction('<?= $parts["CKEditorFuncNum"] ?>', "/workers/picture/display/"+val+"/scaleImage/300/300/true",
							function() {
								// Get the reference to a dialog window.
								var element, dialog = this.getDialog();
								// Check if this is the Image dialog window.
								if (dialog.getName() == 'image') {
									// Get the reference to a text field that holds the "alt" attribute.
									element = dialog.getContentElement( 'info', 'txtAlt' );
									// Assign the new value.
									if ( element )
										element.setValue(title);
								}
							});
						window.close();
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
					}
				}
			});
			return false;
		});
		
		updateResults_<?= $tablename."_".$name ?>();
		
		$(".dropdown-header").click(function() {
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
		if ($("#picselect").find(".popupSearch").val()) {
			$("#"+containerid).find(".popupResultsClear").html("<a href='/list/<?= $contenttype ?>/search'>Clear</a>").click(function() {
				$(".popupWorking").show();
				$("#picselect").find(".popupSearch").val("");
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
		    	.html("<a href='/workers/picturechooser/edit/"+doc.urlid+"'>Edit</a>")
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

<div id="msgdialog"></div>
<div class="dropdown-header" id="contentcreate_dropdown">
	Create <span class="ui-icon ui-icon-circle-triangle-e" style="float: right"></span>
</div>
<div id="contentcreate" style="display: none">

</div>
<div id="displayResults_<?= $tablename."_".$name ?>" class="linkResults">
	
</div>
<div id="picselect" >
	
</div>