<?php 
	$data["menu1_active"]="edit";
	$data["menu2_active"]="edit/".$content_type;
	$this->load->view('templates/header',$data);
?>
<?php
	$this->socketio->js();
?>
<script src="/resources/js/forms.js"></script>
<script src="/resources/js/davis.min.js"></script>
<script src="/resources/bootstrap-datepicker/js/bootstrap-datepicker-ck.js"></script>
<script language="javascript" src="/resources/ckeditor4/ckeditor.js"></script>
<script src="/resources/ckeditor4/adapters/jquery.js"></script>
<script language="javascript" src="/resources/js/ckeditor.js"></script>
<script>
	var currentpage=false;
	
	var content_types=<?= json_encode($content_types); ?>;

	$(function() {
		
		$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
		$(document.body).data('content_type', '<?= $content_type ?>');
		$(document.body).data('urlid', '<?= $urlid ?>');
		$(document.body).data('page', 'edit');
		
		var ajaxqueue=new Array();
		function clear_ajaxqueue() {
			if (!ajaxqueue) return;
			while(ajaxqueue.length>0) {
				jqXHR=ajaxqueue.pop();
				jqXHR.abort();
			}
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
				init_section_modal(content_type);
				$(".chzn-select").chosen();
			});
		}

		init_edit();

		init_section_modal = function(content_type) {
			$("#modal-sections .modal-section").hide();
			$("#modal-sections .checkbox").each(function() {
				$(this).find("input").prop("checked", false);
				var content_types = $(this).attr("data-content-types");
				content_types = content_types.split(" ");
				if (content_types.indexOf(content_type) >= 0) {
					$(this).show();
					$(this).parent().show();
				} else {
					$(this).hide();
				}
			});
			$.getJSON("<?= base_url() ?>api/publish/document", {
				api_key: "<?= $this->session->userdata('api_key') ?>",
				id: $(document.body).data('urlid')
			}, function(data) {
				_.each(data.sections, function(zones, section) {
					_.each(zones, function(zone) {
						$("#modal-sections input[name=" + section+"]").each(function() {
						 	if ($(this).val() == zone) {
						 		$(this).prop("checked", true);
						 	}
						});
					});
				});
			});
		}

		$(document).on('click', '#btn-publish-publish', function() {
			$(document.body).data('action',"_edit");
			save();
			$.getJSON("<?= base_url() ?>api/publish/unpublish_document", {
				api_key: "<?= $this->session->userdata('api_key') ?>",
				id: $(document.body).data('urlid')
			},
			function(data) {
				$("#modal-sections .checkbox input:checked").each(function() {
					var section_id = $(this).attr("name");
					var zone_id = $(this).val();
					$.getJSON("<?= base_url() ?>api/publish/publish_document", {
						api_key: "<?= $this->session->userdata('api_key') ?>",
						section_id: section_id,
						zone_id: zone_id,
						id: $(document.body).data('urlid')
					},
					function(data) {
					});
				});
			});
			$("#modal-sections").modal("hide");
		});

		$(document).on('click', '.the_action', function() {
			action = $(this).attr('id');
			$(document.body).data('action',action);
			save();
			return false;
		});

		$(document).on('click', '#btn-publish', function() {
			$("#modal-sections").modal();
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
				var url = '<?= base_url(); ?>';    
				if($(document.body).data("action") == '_done'){
					url += 'listing/'+$(document.body).data('content_type');
				}
				if($(document.body).data("action") == '_edit'){
					return false;
				}
				$(location).attr('href',url);
			}
		}
		
		function uploadCanceled(e) {
			$(document.body).data("saving",false);
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
					var url = '<?php echo base_url(); ?>listing/'+$(document.body).data('content_type');
					$(location).attr('href',url);
				}
			});
			return false;
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
					<li><button id="btn-publish" class="btn btn-mini btn-warning">Save and Publish</button></li>
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

<div id="modal-sections" class="modal hide fade" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header">Publish to Sections</h3>
	</div>
	<div class="modal-body">
		<form id="modal-sections-form">
			<?php
			$collections=$this->model_collections->get_all();
			foreach($collections as $collection) {
				$sections=$this->model_collections->get_options($collection->_id);
				foreach($sections as $section) {
				?>
				<div class="modal-section" data-section="<?= $section->_id ?>">
					<h3><?= $section->title ?></h3>
					<?php
					foreach($section->zone as $zone) {
					?>
					<label class="checkbox" data-content-types="<?= implode(' ', $zone["zone_content_types"]) ?>">
						<input type="checkbox" name="<?= $section->_id ?>" value="<?= $zone["zone_urlid"] ?>" />
						<?= $zone["zone_name"] ?>
					</label>
					<?php
					}
				?>
				</div>
				<?php
				}
			}
			?>
		</form>
	</div>
	<div class="modal-footer">
		<div class="btn-group">
			<a href="#" id="btn-publish-publish" class="btn btn-primary">Publish</a>
			<a href="#" class="btn btn-warning" data-dismiss="modal">Cancel</a>
		</div>
	</div>
</div>

<div id="dyncontent">
</div>
<div id="createdialog"></div>

<?php
	$this->load->view("templates/footer");
?>