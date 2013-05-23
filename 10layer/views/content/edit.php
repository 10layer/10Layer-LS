<?php 
	$data["menu1_active"]="edit";
	$data["menu2_active"]="edit/".$type;
	$this->load->view('templates/header',$data);
	link_js("/resources/js/forms.js");
	ckeditor();
?>
<script src="/resources/js/jquery.pagination.js"></script>
<script src="/resources/js/davis.min.js"></script>
<link rel="stylesheet" href="/resources/chosen/chosen.css">
<script src="/resources/chosen/chosen.jquery.js"></script>

<link rel="stylesheet" href="/resources/bootstrap-datepicker/css/datepicker.css">
<script src="/resources/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script>
	var currentpage=false;
	
	var content_types=<?= json_encode($content_types); ?>;

	$(function() {
		
		$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
		
		//Router
		var app = Davis(function() {
			this.configure(function () {
				this.generateRequestOnPageLoad = true;
				this.raiseErrors = true;
				this.formSelector = "noforms";
			});
			
			this.before('/edit/:content_type', function(req) {
				if ($(document.body).data('content_type') == req.params['content_type'] && $(document.body).data('page')=='list') {
					return false;
				}
			});
			
			this.before('/edit/:content_type/:urlid', function(req) {
				if ($(document.body).data('urlid') == req.params['urlid'] && $(document.body).data('page')=='edit') {
					return false;
				}
			});
			
			this.before('/api/files/download/*filename', function(req) {
				//console.log(req);
				//return false;
			});
			
			this.get('/edit/:content_type', function(req) {
				$(document.body).data('content_type', req.params['content_type']);
				$(document.body).data('page', 'list');
				$(document.body).trigger('router.init_list');
			});
			
			this.get('/edit/:content_type/:urlid', function(req) {
				$(document.body).data('content_type', req.params['content_type']);
				$(document.body).data('urlid', req.params['urlid']);
				$(document.body).data('page', 'edit');
				$(document.body).trigger('router.init_edit');
			});
			this.get('#', function(req) {});
		});
	
		function prepRouter() {
			clear_ajaxqueue();
			$('#dyncontent').children().find('.wysiwyg').each(function() {
				var name=$(this).attr('name');
				var o=CKEDITOR.instances[name];
				if (o) o.destroy();
			});
		}

		$(document.body).bind('router.init_list', function() {
			prepRouter();
			init_list();
		});
		
		$(document.body).bind('router.init_edit', function() {
			prepRouter();
			init_edit();
		});
		app.start();
		
		// Listing
		function init_list() { //Run this the first time we initiate our list. After that, run update_list
			content_type=$(document.body).data('content_type');
			var searchstring=$("#listSearch").val();
			if (searchstring=='Search') {
				searchstring='';
			}
			$(".menuitem").each(function() {
				$(this).removeClass('selected');
			});
			$('#menuitem_'+content_type).addClass('selected');
			$('#dyncontent').html("Loading...");
			$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", { search: searchstring, content_type: content_type, order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 100, fields: [ "id", "title", "last_modified", "live", "start_date", "workflow_status", "last_editor" ] }, function(data) {
				//console.log("here");
				//console.log(data);
				$('#dyncontent').html(_.template($("#listing-template").html(), {content_type: content_type, data:data}));
				update_pagination(content_type, data.count, 0, 100 );
				update_autos();
				$("#list-search").data('searchstring', searchstring);
			}).error(function(jqXHR, textStatus, errorThrown) {
				$("#msgdialog-header").html("Error");
				$("#msgdialog-body").html("<h4>"+textStatus+"</h4><p>"+jqXHR.responseText+"</p>");
				$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
				$("#msgdialog").modal();
			});
		}
		
		function update_list(content_type, offset) {
			var searchstring=$("#list-search").val();
			if (searchstring=='Search') {
				searchstring='';
			}
			if (!offset) {
				offset=0;
			}
			$('#content-table').html("Loading...");
			//Cancel any existing Ajax calls
			clear_ajaxqueue();
			$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", { search: searchstring, offset: offset, content_type: content_type, order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 100, fields: [ "id", "title", "last_modified", "live", "start_date", "workflow_status", "last_editor" ] }, function(data) {
				//update_pagination( data.count, offset, data.perpage );
				$('#content-table').html(_.template($("#listing-template-content").html(), { content_type: content_type, content:data.content }));
				update_autos();
				$("#list-search").data('searchstring', searchstring);
			});
		}
		
		function search_list() {
			content_type=$(document.body).data('content_type');
			var searchstring=$("#list-search").val();
			if (searchstring=='Search') {
				return;
			}
			offset=0;
			$('#content-table').html("Loading...");
			$('#pagination').html('');
			//Cancel any existing Ajax calls
			clear_ajaxqueue();
			$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", { search: searchstring, offset: offset, content_type: content_type, order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 100, fields: [ "id", "title", "last_modified", "live", "start_date", "workflow_status", "last_editor" ] }, function(data) {
				update_pagination( content_type, data.count, offset, 100 );
				$('#content-table').html(_.template($("#listing-template-content").html(), { content_type: content_type, content:data.content }));
				update_autos();
				$("#list-search").data('searchstring', searchstring);
			});
		}
		
		var ajaxqueue=new Array();
		function clear_ajaxqueue() {
			if (!ajaxqueue) return;
			while(ajaxqueue.length>0) {
				jqXHR=ajaxqueue.pop();
				jqXHR.abort();
			}
		}
		
		function update_pagination(content_type, count, offset, perpage) {
			$("#pagination").pagination(
				count,
				{ 
					items_per_page: perpage,
					current_page: (offset / perpage ),
					callback: function(pg) {
						var offset=(pg)*perpage;
						update_list(content_type, offset);
						return false;
					}
				}
			);
		}
		
		function update_autos() {
			$(".ajax_autoload").each(function() {
				var url=$(this).attr("url");
				var el=$(this);
				ajaxqueue[ajaxqueue.length]=$.getJSON(url+"?jsoncallback=?", function(data) {
					el.html(data.value);
				});
			});
			$('.ajax_auto_link_check').each(function() {
				var url=$(this).attr("url");
				var el=$(this);
				ajaxqueue[ajaxqueue.length]=$.getJSON(url+"?jsoncallback=?", function(data) {
					if (data.value) {
						el.html('<input type="text" value="'+data.value+'" readonly="readonly" class="select_on_click" />');
					}
				});
			});
		}
		
		$(document).on('focus', "#list-search", function() {
			if ($(this).val()=="Search") {
				$(this).val("");
			}
		});
		
		function _check_search() {
			var searchstring=$("#list-search").val();
			if (searchstring=='Search') {
				searchstring='';
			}
			if (searchstring != $("#list-search").data('searchstring')) {
				search_list($(this).attr('content_type'));
			}
		}
		
		$(document).on('keyup','#list-search',function(e) {
			if(e.keyCode == '13'){
				clearTimeout($.data(this, 'timer'));
				search_list($(this).attr('content_type'));
			}
			
			clearTimeout($.data(this, 'timer'));
			var wait = setTimeout(_check_search, 1000);
			$(this).data('timer', wait);
		});
		
		//Editing
		function init_edit() {
			content_type=$(document.body).data('content_type');
			urlid=$(document.body).data('urlid');
			$(".menuitem").each(function() {
				$(this).removeClass('selected');
			});
			$('#menuitem_'+content_type).addClass('selected');
			$('#dyncontent').html("Loading...");
			$.getJSON("<?= base_url() ?>api/content/get_linked_object?jsoncallback=?", { api_key: $(document.body).data('api_key'), id: urlid, meta: true }, function(data) {
				$('#dyncontent').html(_.template($("#edit-template").html(), {data:data, content_type: content_type, urlid: urlid }));
				init_form();
				$(".chzn-select").chosen();
			});
		}
		
		$(document).on('click', '.the_action', function() {
			action = $(this).attr('id');
			$(document.body).data('action',action);
			save();
			return false;
		});
		
		function save() {
			for ( instance in CKEDITOR.instances )
				CKEDITOR.instances[instance].updateElement();
			content_type=$(document.body).data('content_type');
			urlid=$(document.body).data('urlid');
			if (!$(document.body).data('saving')) {
				$(document.body).data('saving', true);
				var formData = new FormData($('#contentform')[0]);
				$.ajax({
					url: "<?= base_url() ?>api/content/save?api_key=<?= $this->session->userdata('api_key') ?>&id="+urlid,  //server script to process data
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
					success: uploadComplete,
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

		function uploadBefore(e) {
			$('#upload_indicator').css("width", '0%' )
			$('#progress_container').show();
		}
		
		function uploadProgress(e) {
			$('#upload_indicator').css("width", ( Math.round((e.loaded / e.total) * 100)) + '%' );
		}

		function hide_progress_bar(){
			$('#progress_container').hide();
		}

		function uploadFailed(e){
			hide_progress_bar();
		}

		function uploadComplete(data) {
			setTimeout(hide_progress_bar, 1000);
			//hide_progress_bar();
			$(document.body).data("saving", false);
			if (data.error) {
				$("#msgdialog-header").html("Error");
				var info = (data.info) ? data.info : '';
				if (_.isArray(info)) {
					var tmp='';
					for(var x=0; x<info.length; x++) {
						tmp+="<li>"+info[x]+"</li>";
					}
				}
				info="<ul>"+tmp+"</li>";
				$("#msgdialog-body").html("<h4>"+data.msg+"</h4><p>"+info+"</p>");
			    $("#msgdialog").modal();
			} else {

				var url = '<?php echo base_url(); ?>';    
				if($(document.body).data("action") == '_done'){
					url += 'edit/'+$(document.body).data('content_type');
				}


				if($(document.body).data("action") == '_edit'){
					return false;
				}

				$(location).attr('href',url);
			}
		}
		
		$(document).on('change', 'input[type=file]', function() {
			content_type=$(document.body).data('content_type');
			var files=this.files; //FileList object
			var file=files[0]; //Only handle single upload at a time
			var el=$(this);
			var container=$(this).parent();
			multiple = el.attr("data-multiple");
			contenttype = el.attr("data-contenttype");
			name = el.attr("data-name");
			var fd = new FormData();
			fd.append("data", file);
			fd.append("filename", $(this).val());
			$.ajax({
				url: "<?= base_url() ?>api/files/upload?api_key=<?= $this->session->userdata('api_key') ?>&filename="+file.name,  //server script to process data
				type: 'POST',
				xhr: function() {  // custom xhr
					myXhr = $.ajaxSettings.xhr();
					if(myXhr.upload){ // check if upload property exists
						myXhr.upload.addEventListener('progress', function(data) {
							var percentage = Math.round((data.position / data.total) * 100);
							container.find('.preview-image .progress .bar').css('width', percentage + '%');
						}, false); // for handling the progress of the upload
					}
					return myXhr;
				},
				//Ajax events
				beforeSend: function(data) {
					container.find('.preview-image .progress').show();
					container.find('.preview-image').slideDown();
					container.find('.preview-image .progress .bar').removeClass('bar-success').removeClass('bar-danger');
				},
				success: function(data) {
					if (data.error) {
						container.find('.preview-image .progress').hide();
						container.find('.alert').removeClass('alert-success').addClass('alert-error').html('<h4>File upload failed</h4> '+data.message).slideDown(500).delay(2000).slideUp(500);
						container.find('.preview-image .progress .bar').removeClass('bar-danger').addClass('bar-success');
						return false;
					}
					container.find('.preview-image .progress').hide();
					container.find('.alert').addClass('alert-success').removeClass('alert-error').html('File uploaded').show().delay(1000).slideDown(500).delay(2000).slideUp(500);
					fullname = data.content.full_name;
					
					if (multiple == 1) {
						container.find('.preview-image-items').prepend(_.template($("#field-image-item").html(), { value: fullname, field: { name: name, contenttype: contenttype, multiple: multiple } } ));
					} else {
						container.find('.preview-image-items').html(_.template($("#field-image-item").html(), { value: fullname, field: { name: name, contenttype: contenttype, multiple: multiple } } ));
					}
					el.val(""); //Clear the file upload so we don't upload on form submission
				},
				error: function(xhr, s, obj) {
					container.find('.alert').removeClass('alert-success').addClass('alert-error').html('File upload failed: '+s).slideDown(500).delay(2000).slideUp(500);
					container.find('.preview-image .progress .bar').removeClass('bar-success').addClass('bar-danger');
				},
				// Form data
				data: file,
				//Options to tell JQuery not to process data or worry about content-type
				cache: false,
				contentType: false,
				processData: false,
				async: true,
				timeout: 600000 //10 mins
			});
			
			var viewer = new FileReader();
			viewer.onload = (function(f) {
				if (file.type.match(/image.*/)) {
					container.find('.preview-image img').attr('src', f.target.result).show();
				} else {
					container.find('.preview-image img').hide();
				}
				
				
			});
			viewer.readAsDataURL(file);
			
		});
		
		function uploadCanceled(e) {
			$(document.body).data("saving",false);
			console.log("uploadCanceled");
			console.log(e);
		}
		
		$(document).on('click', '.select_on_click', function() {
			$(this).select();
		});
		
		$(document).on('click', '.add-relation',function() {
		//Creates the popup box for adding a new item
			var fieldname=$(this).attr("contenttype")+"_"+$(this).attr("fieldname");
			var content_type=$(this).attr("contenttype");
			$.getJSON("<?= base_url() ?>create/jsoncreate/"+content_type+"?jsoncallback=?", function(data) {
				$('#createdialog').dialog({ minWidth: 700, modal: true, }).html(_.template($("#create-popup-template").html(), { data:data, content_type: content_type }));
				init_form();
			});
			return false;
		});
		
		$(document).on('click', '#create-popup-submit', function() {
			$(this).parent().submit();
		});
		
		var allow_done=true;
		
		$(document.body).data('done_submit',false);
		$(document.body).data("saving",false);
		
		$("#createdialog").delegate("#createform-popup","submit",function() {
		//Handles the submit for a new item
			$("#createdialog #createform-popup").ajaxSubmit({
				dataType: "json",
				iframe: true,
				
				beforeSubmit: function(a,f,o) {
					o.dataType = "json";
				},
				success: function(data) {
					if (data.error) {
						allow_done=false;
						$("#msgdialog-header").html("Error");
						$("#msgdialog-body").html("<h4>"+data.msg+"</h4><p>"+data.info+"</p>");
						$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
						$("#msgdialog").modal();
					} else {
						allow_done=true;
						$("#msgdialog").html("<div class='ui-state-highlight' style='padding: 5px'><p><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span><strong>Saved</strong></p></div>");
						var title=data.data.title;
						var id=data.data.id;
						var fieldname=$("#createdialog").data("fieldname");
						var newoption="<option value='"+id+"'>"+title+"</option>";
						$("."+fieldname).prepend(newoption);
						$("."+fieldname).val(id);
						$("#createdialog").dialog("close");
						$("#msgdialog").dialog({
						    modal: true,
						    buttons: {
						    	Ok: function() {
						    		$(this).dialog("close");
						    	}
						    }
						});
						
					}
					
				}
			});
			return false;
		});
		
		$(document).on("click", ".add-zone", function(e) {
			e.preventDefault();
			var fieldname=$(this).attr("data-fieldname");
			$(this).before(_.template($("#field-zone-item").html(), { fieldname: fieldname, zone: {} }));
		});
		
		$(document).on("change", ".zone-field", function() {
			var data={};
			var urlid = "";
			$(this).parent().find(".zone-field").each(function() {
				var name = $(this).attr("name");
				var val = $(this).val();
				data[name]=val;
				if (name == "zone_urlid") {
					urlid = val;
				}
			});
			datael=$(this).parent().find(".zone-data");
			datael.val(JSON.stringify(data));
			datael.attr("name", "section_zone["+urlid+"]");
		});
		
		$(document).on("click", ".remove-zone", function() {
			$(this).parent().parent().parent().remove();
		});
		
		$(document).on("click", "#_delete", function(e) {
			e.preventDefault();
			$("#msgdialog-header").html("Confirm Delete");
			$("#msgdialog-body").html("<p>Are you sure you want to delete this document?</p>");
			$("#msgdialog-buttons").html('<button class="btn btn-danger" data-dismiss="modal" aria-hidden="true" id="btn_confirm_delete">Delete</button> <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>');
			$("#msgdialog").modal();
			return false;
		});
		
		$(document).on("click", "#btn_confirm_delete", function(e) {
			e.preventDefault();
			$.getJSON("<?= base_url() ?>api/content/delete?jsoncallback=?", { id: $(document.body).data('urlid'), api_key: $(document.body).data('api_key') }, function(data) {
				if (data.error) {
					$("#msgdialog-header").html("Error");
					$("#msgdialog-body").html("<h4>There was an error deleting this item</h4> <p>"+data.msg+"</p>");
					$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Okay</button>');
					$("#msgdialog").modal();
				} else {
					var url = '<?php echo base_url(); ?>edit/'+$(document.body).data('content_type');
					$(location).attr('href',url);
				}
			});
			return false;
		});
		
		$(document).on("click", "#_delete_multiple", function(e) {
			e.preventDefault();
			var del_items = [];
			$(".select_item:checked").each(function() {
				del_items.push($(this).val());
			});
			if (del_items.length == 0) {
				$("#msgdialog-header").html("Confirm Delete");
				$("#msgdialog-body").html("<h4>No documents selected</h4> <p>Please select some documents by ticking the checkboxes</p>");
				$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Okay</button>');
				$("#msgdialog").modal();
				return false;
			}
			$("#msgdialog-header").html("Confirm Delete");
			$("#msgdialog-body").html("<p>Are you sure you want to delete "+del_items.length+" document"+((del_items.length > 1) ? "s" : "")+"?</p>");
			$("#msgdialog-buttons").html('<button class="btn btn-danger" data-dismiss="modal" aria-hidden="true" id="btn_confirm_multi_delete">Delete</button> <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>');
			$("#msgdialog").modal();
			return false;
		});
		
		$(document).on("click", "#btn_confirm_multi_delete", function(e) {
			var del_items = [];
			$(".select_item:checked").each(function() {
				del_items.push({ id: $(this).val() });
			});
			$.getJSON("<?= base_url() ?>api/content/multiple/delete?jsoncallback=?", { items: del_items, api_key: $(document.body).data('api_key') }, function(data) {
				$(".select_item:checked").each(function() {
					$(this).parent().parent().hide();
				});
			});
		});
		
		$(document).on("click", ".image-link", function(e) {
			e.preventDefault();
			$(this).parent().next(".link-show").toggle();
		});
		
		$(document).on("click", ".image-remove", function(e) {
			e.preventDefault();
			$(this).parent().parent().parent().remove();
		});
		
		$(document).on('click', '.do_publish', function() {
			var zone_urlid = $(this).attr("data-urlid");
			var zone_title = $(this).html();
			var section_id = $(this).attr("data-sectionid");
			var section_title = $(this).attr("data-sectiontitle");
			$("#published_list").append("<li><input type='hidden' name='autopublish_sections' value='"+section_id+"."+zone_urlid+"' /><a href='#'>"+section_title+" :: "+zone_title+"</li>");
		});
				
	}); //End of $(function)
	
	version_map=new Array( "", "New", "Edited", "Published" );
	
</script>
<script type="text/template" id="listing-template">
	<div id="contentlist" class="boxed full">
		<div id='pagination' class='pagination' style="float: left; margin-right: 50px"></div>
		<div id="listSearchContainer" style="float: left; margin-right: 50px; margin-top: 20px">
			<%= _.template($('#listing-template-search').html(), { search: data.search, content_type: content_type }) %>
		</div>
		<div id="group_actions" class="btn-group" style="float: left; margin-top: 20px">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">With selected <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<!--<li><a href="#" class="group_action" data-action="">Create</a></li>
				<li><a href="#">Edit</a></li>
				<li><a href="#">Publish</a></li>-->
				<li><a href="#" id="_delete_multiple">Delete</a></li>
			</ul>
		</div>
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content_type: content_type, content: data.content }) %>
		</div>
	</div>
</script>

<script type='text/template' id='listing-template-search'>
	<input content_type='<%= content_type %>' type="text" id="list-search" value="<%= (search) ? search : 'Search' %>" />
	<span id="loading_icon" style="display:none;">
		<!--<img src="/tlresources/file/images/loader.gif" />-->
	</span>
</script>

<script type='text/template' id='listing-template-content'>
	<table class='table table-bordered table-striped table-condensed'>
	    <thead>
	    <tr>
	    	<th></th>
	    	<th>Title</th>
	    	<th>Last Edit</th>
	    	<th>Edited by</th> 
	    	<th>Start Date</th>
	    	<th>Workflow</th>
	    </tr>
	    </thead>
	    <tbody>
		<% var x=0; _.each(content, function(item) {  %>
	    <tr id="row_<%= item.id %>">
	    	<td><input type="checkbox" class="select_item" name="select_item" value="<%= item._id %>"></td>
	    	<td class='content-workflow-<%= item.workflow_status %>'><a href='/edit/<%= content_type %>/<%= item._id %>' content_urlid='<%= item._id %>' class='content-title-link'><%= item.title %></a></td>
	    	<td style="width: 100px"><%= dateToString(item.last_modified) %></td>
	    	<td><%= (item.last_editor) ? item.last_editor : '' %></td>
	    	<td style="width: 100px"><%= dateToString(item.start_date) %></td>
	    	<td class='content-workflow-<%= item.workflow_status %>'><%= item.workflow_status %></td>
	    </tr>
		<% x++; }); %>
	    </tbody>
	</table>
</script>



<script type='text/template' id='edit-template'>

<div class="row" >

	<div style='margin-left:0;' class='main_form_container span10'>
		<div class='root'>
			
			<div id="edit-content" class="span10" >
				<h2>Edit</h2>
				<form id='contentform' method='post' enctype='multipart/form-data' action='<?= base_url() ?>api/content/save?api_key=<%= $(document.body).data('api_key') %>&id=<%= urlid %>' class='form-horizontal span12'>
				<input type='hidden' name='action' value='submit' />
				<input type='hidden' name='id' value='<%= urlid %>' />
				<% _.each(data.meta, function(field) {
					field.value = data.content[field.name];
				%>
					<% 
					if (!field.hidden) { 
						try {
					%>
						<%= _.template($('#edit-field-'+field.type).html(), { field: field, urlid: urlid, content_type: content_type  }) %>
					<%	} catch(err) {
							$("#msgdialog-header").html("Error");
							$("#msgdialog-body").html("<h4>A problem was detected with field " + field.name+"</h4><p>"+err+"</p>");
							$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
							$("#msgdialog").modal();
						}
					} 
					%>
				<% }); %>
				</form>

			</div>
			<br clear='both'>
		</div>

		<div class="over_lay slider span10"></div>
    </div>
</div>

	<div id='bottom_bar' class="navbar navbar-fixed-bottom">
		<div class="navbar-inner">
			<div class="container">
				<ul class="nav">

					<li><button id="_done" class="the_action btn btn-mini btn-primary">Save and List</button></li>
					<li class='divider-vertical'></li>
					<li><button id="_edit" class="the_action btn btn-mini btn-info">Save and Edit</button></li>
					<li class='divider-vertical'></li>
					<li><button id="_publish" class="the_action btn btn-mini btn-warning">Save and Publish</button></li>
					<li class='divider-vertical'></li>
					<li><button id="_delete" class="btn btn-mini btn-danger">Delete</button></li>
					<li class="divider-vertical"></li>
					<li style=" padding-top:10px; width:300px;">
						<div id='progress_container' style='display:none;' class="progress progress-striped active">
							<div id="upload_indicator" class="bar" style="width: 0%;"></div>
						</div>
					</li>
				</ul>
			</div>
		</div>
		
	</div>
</div>
</script>

<script type='text/template' id='create-popup-template'>
	<div id="create-content" class="boxed wide">
		<h2>Create - <%= content_type %></h2>
		<form id='createform-popup' method='post' enctype='multipart/form-data' action='<?= base_url() ?>create/ajaxsubmit/<%= content_type %>'>
		<input type='hidden' name='action' value='submit' />
		<% _.each(data.fields, function(field) { %>
			<% if (!field.hidden) { %>
				<%= _.template($('#create-field-'+field.type).html(), { field: field, urlid: false, content_type: content_type  }) %>
			<% } %>
		<% }); %>
		<button id='create-popup-submit'>Submit</button>
		</form>
	</div>
</script>

<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>

<?php
	$this->load->view("snippets/javascript_templates");
?>

<div class="modal hide fade" id="msgdialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header"></h3>
	</div>
	<div class="modal-body" id="msgdialog-body">
	</div>
	<div class="modal-footer">
		<div id="msgdialog-buttons" class="btn-group">
		</div>
	</div>
</div>

<div id="dyncontent">
</div>
<div id="createdialog"></div>

<?php
	$this->load->view("templates/footer");
?>