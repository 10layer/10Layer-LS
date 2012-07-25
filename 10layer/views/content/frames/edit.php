<?php 
	$data["menu1_active"]="edit";
	$data["menu2_active"]="edit/".$type;
	$this->load->view('templates/header',$data);
	link_js("/tlresources/file/js/forms.js");
	link_js("/tlresources/file/jquery/jquery.form.js?1");
	link_js("/tlresources/file/js/forms/default.js");
	ckeditor();
	//tinymce();
?>
<script src="/tlresources/file/js/underscore-min.js"></script>
<script src="/tlresources/file/js/jquery.pagination.js"></script>
<script src="/tlresources/file/js/davis.min.js"></script>
<script>
	var currentpage=false;
	
		
	$(function() {
		
		$(document.body).data('api_key', '<?= $this->config->item('api_key') ?>');
		
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
			$('#dyncontent').children().find('.richedit').each(function() {
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
		//app.handleRequest('<?= $this->uri->uri_string() ?>');
		
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
			$.getJSON("<?= base_url() ?>list/jsonlist/"+content_type+"?jsoncallback=?", {searchstring: searchstring}, function(data) {
				$('#dyncontent').html(_.template($("#listing-template").html(), {content_type: content_type, data:data}));
				update_pagination(content_type, data.count, 0, data.perpage );
				update_autos();
				$("#list-search").data('searchstring', searchstring);
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
			$.getJSON("<?= base_url() ?>list/jsonlist/"+content_type+"?jsoncallback=?", { searchstring: searchstring, offset: offset }, function(data) {
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
			$.getJSON("<?= base_url() ?>list/jsonlist/"+content_type+"?jsoncallback=?", { searchstring: searchstring, offset: offset }, function(data) {
				update_pagination( content_type, data.count, offset, data.perpage );
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
			$.getJSON("<?= base_url() ?>edit/jsonedit/"+content_type+"/"+urlid+"?jsoncallback=?", function(data) {
				$('#dyncontent').html(_.template($("#edit-template").html(), {data:data, content_type: content_type, urlid: urlid }));
				init_form();
			});
		}
		
		$(document).on('click', '#dosubmit_right', function() {
			$(document.body).data('done_submit', false);
			save();
			return false;
		});
		
		$(document).on('click', '#dodone_right', function() {
			$(document.body).data('done_submit', true);
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
					url: "<?= base_url() ?>/workers/api/update/"+content_type+"/"+urlid+"/<?= $this->config->item('api_key') ?>",  //server script to process data
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
		
		$(document).on('change', 'input[type=file]', function() {
			content_type=$(document.body).data('content_type');
			var files=this.files; //FileList object
			var file=files[0]; //Only handle single upload at a time
			var el=$(this);
			var container=el.parent();
			var viewer = new FileReader();
			viewer.onload = (function(f) {
				container.find('.preview-image img').attr('src', f.target.result).css("height", 300);
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
		
		
	});
	
	<?php
		$this->load->model('model_workflow');
		$workflows=$this->model_workflow->getAll();
		$workflow_array=array();
		foreach($workflows as $workflow) {
			$workflow_array[]="'$workflow->name'";
		}
	?>
	version_map=new Array(
		<?= implode(",", $workflow_array); ?>
	);
	
</script>
<script type="text/template" id="listing-template">
	<div id="contentlist" class="boxed full">
		<div id="listSearchContainer">
			<%= _.template($('#listing-template-search').html(), { search: data.search, content_type: content_type }) %>
		</div>
		<div id='pagination'></div>
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content_type: content_type, content: data.content }) %>
		</div>
	</div>
</script>

<script type='text/template' id='listing-template-search'>
	<input content_type='<%= content_type %>' type="text" id="list-search" value="<%= (search=='') ? 'Search' : search %>" />
	<span id="loading_icon" style="display:none;">
		<img src="/tlresources/file/images/loader.gif" />
	</span>
</script>

<script type='text/template' id='listing-template-content'>
		<table>
			<tr> 
				<th>Title</th> 
				<th>Last Edit</th>
				<th>Edited by</th> 
				<th>Start Date</th>
				<th>Live</th>
				<th>Workflow</th>
				<th></th>
			</tr>
	<% var x=0; _.each(content, function(item) { %>
			<tr class="<%= ((x % 2) == 0) ? 'odd' : '' %> content-item" id="row_<%= item.id %>">
				<td class='content-workflow-<%= item.major_version %>'><a href='/edit/<%= content_type %>/<%= item.urlid %>' content_id='<%= item.id %>' content_urlid='<%= item.urlid %>' class='content-title-link'><%= item.title %></a></td>
				<td><%= item.last_modified %></td>
				<td class='ajax_autoload' url='/workers/content/jsongetlasteditor/<%= content_type %>/<%= item.urlid %>' ></td>
				<td><%= item.start_date %></td>
				<td class="<%= (item.live==1) ? 'green' : 'red' %>"><%= (item.live==1) ? 'Live' : 'Not live' %></td>
				<td class='content-workflow-<%= item.major_version %>'><%= version_map[item.major_version] %></td>
				<td class='ajax_auto_link_check' url='/list/jsonfilelist/<%= content_type %>/<%= item.urlid %>' ></td>
			</tr>
	<% x++; }); %>
		</table>
</script>



<script type='text/template' id='edit-template'>
	<div id="edit-content" class="boxed wide">
		<h2>Edit - <%= data.content_type %></h2>
		<form id='contentform' method='post' enctype='multipart/form-data' action='<?= base_url() ?>edit/ajaxsubmit/<%= content_type %>/<%= urlid %>'>
		<input type='hidden' name='action' value='submit' />
		<% _.each(data.fields, function(field) { %>
			<% if (!field.hidden) { %>
				<%= _.template($('#edit-field-'+field.type).html(), { field: field, urlid: urlid, content_type: content_type  }) %>
			<% } %>
		<% }); %>
		</form>
	</div>
	<div id="sidebar" class="pin">
		<div id="sidebar_accordian">
			<h3><a href="#">Actions</a></h3>
			<div>
				<button id="dodone_right" content_type="<%= content_type %>" urlid="<%= urlid %>">Done</button><br />
				<br />
				<button id="dosubmit_right">Save</button><br />
				<br />
			</div>
			<!--<h3><a href="#">Versions</a></h3>
			<div>
				<button id="dofork_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowthickstop-1-n"></span>Fork</button><br />
				<br />
				<button id="dolink_right" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-link"></span>Link</button><br />
				<br />
			</div>-->
			<h3><a href="#">Workflow</a></h3>
			<div id="workflows"></div>
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

<div id="msgdialog"></div>
<div id="dyncontent">
</div>
<div id="createdialog"></div>
<?php
	$this->load->view("templates/footer");

?>