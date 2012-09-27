
var xhr_reqs = [];

function create_autocomplete_item(item, content_type, multiple_status, field_name){
	console.log(item);

	var newel = _.template($('#field-autocomplete-item').html(), { title:item.title, field: { multiple:multiple_status, contenttype: content_type, name: field_name, value: item._id }});
	return newel;
}


function update_rich(sender) {
	var content_type = $(sender).attr('contenttype');
	var fieldname = $(sender).attr('fieldname');
	var urlid = $(sender).attr('urlid');
	var tablename = $(sender).attr('tablename');
	var content_id = $(sender).val();
	$('#contentselect_'+content_type+'_'+fieldname).dialog('close');
	update_content({ template: 'field-rich-item', element: 'link_results_'+content_type+'_'+fieldname, urlid: urlid, content_id: content_id, contenttype: content_type, fieldname: fieldname, tablename: tablename });
	rich_overlay();
}

function update_content(data) {
	var results_data=_.template($('#'+data.template).html(), data);
	$('#'+data.element).html(results_data);
}

function leadingZeros(s) {
	if (s<10) {
		return '0'+s;
	}
	return s;
}


function load_over_lay(content){
	$("#over_lay").html(content).animate({
		    width: "70%",
		    opacity: 0.4,
		    marginLeft: "0.6in",
		    fontSize: "3em",
		    borderWidth: "10px"
		  }, 1500 );
	
}

$(function() {


	$(".result_container div a.close").live('click', function(){
		$(this).parent().remove();
	});
	
	$("input, textarea").keyup(function() {
		var reqs=checkreqs();
	});
	
	$("#submit").click(function() {
		if (checkreqs()) {
			return true;
		}
		return false;
	});
	
	checkreqs();
	
	$("#title").keyup(function() {
		$("#smarturl-preview").load("/workers/ajax_edit/smarturl", {"url": $("#title").val()}, 
			function(response, status, xhr) {
				$("#urlid").val(response);
			}
		);
	});
	
	$('<div class="wordcount_container">Word count:&nbsp;<div class="wordcount_result"></div></div>').insertAfter(".wordcount");
	
	$(".wordcount").keyup(function() {
		get_wordcount($(this));
	});
	
	$(".wordcount").each(function() {
		get_wordcount($(this));
	});
			
	$(".autocomplete_add").live("click",function() {
		var viewfield=$("#autocomplete_view_"+$(this).attr("contenttype")+"_"+$(this).attr("fieldname"));
		alert(viewfield.val());
		return false;
	});
		
	

	$(".btn_new").live("click",function() {

		load_over_lay('testing');
	});
			
	$(".remover").live("click", function() {
		$(this).parent().remove();
		return false;
	});
	
	
	
	$(document).unbind('keydown').bind('keydown', function (event) {
		var doPrevent = false;
		if (event.keyCode === 8) {
			var d = event.srcElement || event.target;
			if ((d.tagName.toUpperCase() === 'INPUT' && d.type.toUpperCase() === 'TEXT') || d.tagName.toUpperCase() === 'TEXTAREA') {
            	doPrevent = d.readOnly || d.disabled;
        	} else {
            	doPrevent = true;
        	}
    	}

	    if (doPrevent) {
        	event.preventDefault();
    	}
	});
	
	$(".deepsearch_input").live("keypress",function(e) { 
    	if (e.which == 13) { 
      		return false; 
    	} 
  	});
  	
  	$(".deepsearch_input").live("keypress",function(e) {
  		if (e.which == 13) { 
			var resultdiv=$(this).next().next();
			var selected_container = resultdiv.next();
			var selected_items = selected_container.children('div');
			var items = new Array();
			var content_type=$(this).attr("contenttype");
			selected_items.each(function(index) {
				items[index] = $(this).children(":first").val();
			});
		
			var val = $(this).val();
      		$.getJSON("/list/"+content_type+"/deepsearch?term="+escape(val), {"selected[]":items}, function(result) {
				resultdiv.html("");
				for(x=0; x<result.length; x++) {
					resultdiv.append("<div class='deepsearch_item' id='"+result[x].id+"'>"+result[x].value+" ("+result[x].start_date+")</div>");
				}
			});	
    	}		
	});
	
	$(".deepsearch_item").live("click", function(){
		var selected_set = $(this).parent().next();
		var used_element = $(this).parent().prev().prev();
		var label = $(this).html();
		var id = this.id;
		var newdisp="<div class='deepsearch_selected_item'>"+"<input type='hidden' value='"+id+"' name='"+used_element.attr("tablename")+"_"+used_element.attr("fieldname")+"[]' value='' /><span class='label'>"+
		label+"</span></div>";
		selected_set.append(newdisp);
		$(this).remove();
		return false;
	});
		
	$(".deepsearch_selected_item").live("click", function(){
		var search_result_set = $(this).parent().prev();
		var label = $(this).text();
		var id = $(this).children(":first").val();
		var newdisp="<button class='autocomplete_item'>"+label+"</button>";
		var newdisp="<div class='deepsearch_item' id='"+id+"'>"+label+"</div>";
		search_result_set.append(newdisp);
		$(this).remove();
		return false;
	});
	
	$("#workflow_next").live("click", function() {
		$.getJSON("/workflow/change/advance/"+content_type+"/"+urlid, function() {
			$("#workflows").load("/workflow/change/status/"+content_type+"/"+urlid);
		});
	});
	
	$("#workflow_revert").live("click", function() {
		$.getJSON("/workflow/change/revert/"+content_type+"/"+urlid, function() {
			$("#workflows").load("/workflow/change/status/"+content_type+"/"+urlid);
		});
	});
	
	$(document).on("click", '.new-window', function() {
		window.open($(this).attr('href'), '_blank');
	});
	
	$(document).on("click", '.btn_rich_select', function() {
		var content_type = $(this).attr('contenttype');
		var fieldname = $(this).attr('fieldname');
		var tablename = $(this).attr('tablename');
		$.getJSON("/list/jsonlist/"+content_type, {}, function(data) {
			data.fieldname=fieldname;
			data.tablename=tablename;
			var popup_data=_.template($('#field-rich-list').html(), data);
			$('#contentselect_'+content_type+'_'+fieldname).html(popup_data).dialog('open');
		});
		return false;
	});
	
	$(document).on("click", '.item-select', function() {
		update_rich(this);
	});
	
	$(document).on('keyup', '.popup_search', function(e) {
		if (e.which == 13) { 
			var content_type = $(this).attr('contenttype');
			var fieldname = $(this).attr('fieldname');
			var tablename = $(this).attr('tablename');
			$.getJSON("/list/jsonlist/"+content_type, { searchstring: $(this).val() }, function(data) {
				data.fieldname=fieldname;
				data.tablename=tablename;
				var popup_data=_.template($('#field-rich-list').html(), data);
				$('#contentselect_'+content_type+'_'+fieldname).html(popup_data).dialog('open');
			});
		}
	});
	
	$(document).on('click', '.btn_new', function() {
		var content_type = $(this).attr('contenttype');
		var fieldname = $(this).attr('fieldname');
		var tablename = $(this).attr('tablename');
		$(document.body).data('popup_data', {content_type: content_type, fieldname: fieldname, tablename: tablename });
		$.getJSON("/create/jsoncreate/"+content_type+"?jsoncallback=?", function(data) {
			var create_html=_.template($("#create-template").html(), { data:data, content_type: content_type, popup: true });
			$('#new_dialog_'+tablename+'_'+fieldname).html(create_html);
			init_form();
			$('#new_dialog_'+tablename+'_'+fieldname).dialog('open');
		});
		
		return false;
	});
	
	$(document).on('click', '.dosubmit_popup', function() {
		var content_type = $(this).attr('contenttype');
		var fieldname = $(this).attr('fieldname');
		var form = $('#form_create_'+content_type);
		popup_insert(form, content_type);
		return false;
	});
	
	$(document).on('keydown', '.datetime_hour', function(e) {
		var key = e.keyCode;
		if ((key > 33) && (key < 47)) {
			return false;
		}
		if ((key > 58) && (key < 126)) {
			return false;
		}
		/*var val=$(this).val();
		if ((key > 47) && (key < 58)) {
			if (val.length > 1) {
				return false;
			}
			var newval = val + (key-48);
			if (newval > 23) {
				return false;
			}
		}*/
	});
	
	$(document).on('blur', '.datetime_hour', function(e) {
		var val = 0;
		if (($(this).val())) {
			var val = Number($(this).val());
			$(this).val(leadingZeros(val));
			if ($(this).val().length > 2) {
				var s = $(this).val();
				$(this).val(s.substring(s.length - 2, s.length));
				val = parseInt($(this).val());
			}
			if (val > 23) {
				$(this).val("23");
			}
		} else {
			$(this).val("00");
		}
		if (!isFinite($(this).val())) {
			$(this).val("00");
		}
	});
	
	$(document).on('blur', '.datetime_minute', function(e) {
		if ($(this).val()) {
			var val = Number($(this).val());
			$(this).val(leadingZeros(val));
			if ($(this).val().length > 2) {
				var s = $(this).val();
				$(this).val(s.substring(s.length - 2, s.length));
				val = parseInt($(this).val());
			}
			if (val > 59) {
				$(this).val("59");
			}
		} else {
			$(this).val("00");
		}
		if (!isFinite($(this).val())) {
			$(this).val("00");
		}
	});
	
	$(document).on('keydown', '.datetime_minute', function(e) {
		var key = e.keyCode;
		if ((key > 33) && (key < 47)) {
			return false;
		}
		if ((key > 58) && (key < 126)) {
			return false;
		}
		/*var val=$(this).val();
		if ((key > 47) && (key < 58)) {
			if (val.length > 1) {
				return false;
			}
			var newval = val + (key-48);
			if (newval > 59) {
				return false;
			}
		}
		return true;*/
	});
	
	$(document).on('keyup', '.datetime_change', function() {
		updateDateTime(this);
	});
	
	$(document).on('change', '.datetime_change', function() {
		updateDateTime(this);
	});
	
	function updateDateTime(sender) {
		var parent = $(sender).parent();
		var val = "";
		val += parent.children('.datetime_date').val();
		val += " ";
		val += (parent.children('.datetime_hour').val()) ? parent.children('.datetime_hour').val() : "00";
		val += ":";
		val += (parent.children('.datetime_minute').val()) ? parent.children('.datetime_minute').val() : "00";
		$(sender).siblings(':hidden').val(val);
	}
	
	
	$(document).on('click', '.deepsearch-search', function() {
		var searchel=$(this).prev();
		var origel = this;
		var search=$(this).prev().val();
		var content_type=$(this).prev().attr("contenttype");
		var optionel=$(this).parent().siblings('.deepsearch-options');
		var resultel=$(this).parent().siblings('.deepsearch-results');
		var indicator = $(this);
		//resultel.html('Searching...');

		while(xhr_reqs.length>0) {
			jqXHR=xhr_reqs.pop();
			jqXHR.abort();
		}
		indicator.html("<span class='label label-success'>Searching...</span>");
		xhr_reqs.push(
			$.getJSON("/api/content", { search: search, content_type: content_type, limit: 20, fields: ["_id", "title"], order_by: "last_modified" }, function(data) {
				var pos = $.extend({}, searchel.offset(), {
	        		height: origel.offsetHeight
				});
				console.log(pos);
				optionel.css({
					top: pos.top + pos.height
					, left: pos.left
				});
				
				optionel.show();
				
				optionel.html('');
				_.each(data.content, function(item) {
					var el=$('<li><a href="#">'+item.title+'</a></li>').click(function(e) {
						e.stopPropagation();
						e.preventDefault();
						optionel.hide();
						var newel= _.template($('#field-autocomplete-item').html(), { urlid: item._id, field: { contenttype: content_type, name: "", value: "" } });
						resultel.append(newel);
						searchel.val("");
					});
					indicator.html('Search');
					optionel.append(el);
				});
			})
		);

		
	});
	
});

function checkreqs() {
	var reqs=true;
	$(".required").each(function() {
		var val=$(this).val();
		if (val=="") {
			reqs=false;
		}
	});
	if (!reqs) {
		$("#submit").addClass("inactive");
	} else {
		$("#submit").removeClass("inactive");
	}
	return reqs;
}

function get_wordcount(sender) {
	sender.nextAll(".wordcount_container").first().find(".wordcount_result").load("/workers/ajax_edit/wordcount", {"str": sender.val()})
}

function init_form() {

	/*$($('.nested_container')).each(function(index) {
		var container = $(this);
		var content_type = $(this).attr("contenttype");
		var items = container.children().eq(1);
		    	
    	$.getJSON('/list/jsonnested/'+content_type+'/1?jsoncallback=?', function(data) {
    		console.log(data);
  				items.html(_.template($('#edit-field-nesteditems-list').html(), data));
  				
  				$(".nested_section").live("click", function(){
					var content_id = $(this).attr("content_id");
					var indicator = $(this);
					$(".cool_t").removeClass("cool_t");
                	indicator.children().eq(0).addClass("cool_t");
					var value = $(this).attr('label');
					var display_el = $(this).parentsUntil(".nested_items").parent().prev();
					display_el.children().eq(1).html(value);
					var value_el = display_el.children().eq(0);
					value_el.val(content_id);
					return false;
				});				

		});
	});*/
	
	$(".chzn-select").chosen();
	
	$('button').each(function() {
		//$(this).button();
	});
	
	/*$(".datetimepicker").each(function() {
		$(this).datetimepicker({
			dateFormat:"yy-mm-dd",
			timeFormat:"hh:mm:ss",
		});
	});*/
	
	$( ".datepicker" ).datepicker();
	
	var autocomplete_timer = false;
	$(".autocomplete").each(function(){
		var self = $(this);
		var self_val = '';
		var origel = this;
		self.keyup(function(){

			if ($.trim(self_val) != $.trim(self.val())) {
				self_val = self.val();
				
				clearTimeout(autocomplete_timer);
				autocomplete_timer = setTimeout(function(){

				var searchel=self.prev();
				
				var multiple_status = self.attr('multiple');
				var field_name = self.attr('fieldname');
				var search=self.prev().val();
				var content_type=self.prev().attr("contenttype");
				var optionel=self.parent().siblings('.options');
				var resultel=self.parent().siblings('.result_container');
				var content_type = self.attr("contenttype");
				var url="/api/content/listing/";
				var search = self.val();
				var indicator = self.parent().siblings('.indicator');

		        while(xhr_reqs.length>0) {
					jqXHR=xhr_reqs.pop();
					jqXHR.abort();
				}
				indicator.show();
				xhr_reqs.push(
					$.getJSON("/api/content", { search: search, content_type: content_type, limit: 20, fields: ["_id", "title"], order_by: "last_modified" }, function(data) {
						var pos = $.extend({}, searchel.offset(), {
			        		height: origel.offsetHeight
						});
				
						optionel.css({
							top: pos.top + pos.height
							, left: pos.left
						});
						
						if(data.lenth > 0){
							optionel.show();
						}else{
							optionel.hide();
							indicator.hide();
						}
						
						
						optionel.html('');
						_.each(data.content, function(item) {
							var el=$('<li><a href="#">'+item.title+'</a></li>').click(function(e) {
								e.stopPropagation();
								e.preventDefault();
								optionel.hide();
								resultel.append(create_autocomplete_item(item, content_type, multiple_status, field_name));
								searchel.val("");
							});
							optionel.append(el);
							indicator.hide();
						});



					})
				)

				}, 500);
			
			}
			
		});

	});

		
	/*$(".autocomplete").each(function() {
		var contenttype=$(this).attr("contenttype");
		var contenttypes=false;
		var source="/list/"+contenttype+"/suggest";
		if (contenttype=="mixed") {
			contenttypes=$(this).attr("contenttypes");
			contenttypes=contenttypes.replace(/,/g,"/");
			var source="/list/mixed/suggest/"+contenttypes;
		}
		if ($(this).hasClass("multiple")) {
			$(this).width(450);
		}
		$(this).autocomplete({
		
			source: function (request, response) {
				try {
					$.ajax({
						url: source,
						data: request,
						dataType: "json",
						success: function(data, status) {
							response(data);
						}
					});
				} catch(err) {
					//Do nothing
				}
			},
			select: function( event, ui ) {
				if (ui.item) {
					if ($(this).hasClass("multiple")) {
						//Check for repeats
						var isRepeat=false;
						$(this).next().next().find("input").each(function() {
							if ($(this).val()==ui.item.id) {
								isRepeat=true;
								return false;
							}
						});
						if (isRepeat) {
							return false;
						}
						//console.log(ui.item);
						var newdisp= ui.item.label;
						var newobj="<input type='hidden' value='"+ui.item.id+"' name='"+$(this).attr("tablename")+"_"+$(this).attr("fieldname")+"[]' />";

						var append_material = "<li class='autocomplete_item'><span class='ui-icon ui-icon-arrowthick-2-n-s float-left' style='margin:10px;'></span><span class='remover'>" + newdisp + "</span>" + newobj + "</li>";
						
						$(this).next().next().children(":first").append(append_material);
						
						
						
						$(".items_container").sortable();
						$(this).val("");
						
						return false;
					} else {
						//This looks incomplete
						//$("#autocomplete_"+$(this).attr("contenttype")+"_"+$(this).attr("fieldname")).val(ui.item.id);
						//alert($("#autocomplete_"+$(this).attr("contenttype")+"_"+$(this).attr("fieldname")).val());
					}
				}
			},
		});
	});*/
	
	
	
		
	//$(".items_container").sortable();
	
	$(".countchars").each(function() {
		if ($(this).hasClass("countdown")) {
			var max=$(this).attr("max");
			var current=max-$(this).val().length;
			if (current>=0) {
				$(this).after("<div class='charcount'>"+current+" chars remaining</div>");
			} else {
				$(this).after("<div class='charcount red'>"+Math.abs(current)+" chars over</div>");
			}
			
			$(this).keyup(function() {
				var max=$(this).attr("max");
				var current=max-$(this).val().length;
				if (current>=0) {
					$(this).next().html(current+" chars remaining").removeClass("red");
				} else {
					$(this).next().html(Math.abs(current)+" chars over").addClass("red");
				}
			});
		} else {
			var current=$(this).val().length;
			$(this).after("<div class='charcount'>"+current+" chars</div>");
			$(this).keyup(function() {
				current=$(this).val().length;
				$(this).next().html(current+" chars");
			});
		}
		$(this).removeClass("countchars");
	});
	
	if ($(".richedit").length) {
		//init_tinymce();
		//clearCKEditor();
		initCKEditor();
	}
	
	$('.link_results').each(function() {
		var content_type=$(this).attr('content_type');
		var urlid=$(this).attr('urlid');
		$(this).load("/list/"+content_type+"/item/"+urlid);
	});
	
	rich_overlay();
	
	/*$(".popup").dialog({
		autoOpen: false,
		height: 600,
		width: 700,
		modal: true,
	});*/

	
	var content_type=$(document.body).data('content_type');
	var urlid=$(document.body).data('urlid');
	$("#workflows").load("/workflow/change/status/"+content_type+"/"+urlid);
}

function rich_overlay() {
	/*$( ".rich_overlay_remove" ).button({
        icons: {
            primary: "ui-icon-trash"
        },
        text: false
	}).click(function() {
	    $(this).parent().parent().next().val("");
	    $(this).parent().parent().empty();
	});
	$( ".rich_overlay_edit" ).button({
        icons: {
            primary: "ui-icon-pencil"
        },
        text: false
	});*/
}

function insert(form, content_type) {
	_insert(form, content_type, uploadComplete);
}

function popup_insert(form, content_type) {
	_insert(form, content_type, popupUploadComplete)
}

function _insert(form, content_type, success) {
	for ( instance in CKEDITOR.instances )
		CKEDITOR.instances[instance].updateElement();
	if (!$(document.body).data('saving')) {
		$(document.body).data('saving', true);
		var formData = new FormData(form[0]);
		$.ajax({
			url: "/workers/api/insert/"+content_type+"/"+$(document.body).data('api_key'),  //server script to process data
			type: 'POST',
			xhr: function() {  // custom xhr
				myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // check if upload property exists
					myXhr.upload.addEventListener('progress',uploadProgress, false); // for handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: uploadBefore,
			success: success,
			error: uploadFailed,
			// Form data
			data: formData,
			//Options to tell JQuery not to process data or worry about content-type
			cache: false,
			contentType: false,
			processData: false
		});
	}
}

function uploadProgress(e) {}

function uploadBefore(e) {}

function uploadComplete(data) {
    $(document.body).data("saving",false);
    if (data.error) {
    	$("#msgdialog-header").html("Error");
		$("#msgdialog-body").html("<h4>"+data.msg+"</h4><p>"+data.info+"</p>");
		$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
		$("#msgdialog").modal();
    } else {
    	$("#msgdialog-header").html("Saved");
		$("#msgdialog-body").html("<p>Content has been saved</p>");
		$("#msgdialog").modal();
        if ($(document.body).data('done_submit')) {
        	content_type=$(document.body).data('content_type');
        	urlid=$(document.body).data('urlid');
        	$.ajax({ type: "GET", url: "<?= base_url() ?>/workflow/change/advance/"+content_type+"/"+urlid, async:false});
        	location.href="/edit/"+content_type;
        	//location.href="/workers/content/unlock/"+content_type+"/"+urlid;
        } else {
        	$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
        }
    }
}

function popupUploadComplete(data) {
	$(document.body).data("saving",false);
	var popup_data=$(document.body).data('popup_data');
    if (data.error) {
        $("#msgdialog").html("<div class='ui-state-error' style='padding: 5px'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span><strong>"+data.msg+"</strong><br /> "+data.info+"</p></div>");
        $("#msgdialog").dialog({
        	modal: true,
        	buttons: {
        		Ok: function() {
        			$(this).dialog("close");
        		}
        	}
        });
    } else {
    	$('#new_dialog_'+popup_data.tablename+'_'+popup_data.fieldname).dialog('close');
        console.log("Finished saving");
        console.log(popup_data);
    }
}

		function uploadFailed(e) {
			$(document.body).data("saving",false);
			$("#msgdialog-header").html("Error");
			$("#msgdialog-body").html("<p>"+e.responseText+"</p>");
			$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
			$("#msgdialog").modal();
		}
