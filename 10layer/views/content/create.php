<?php 
	$data["menu1_active"]="create";
	$data["menu2_active"]="create/".$type;
	$this->load->view('templates/header',$data);
	link_js("/resources/js/forms.js");
	ckeditor();
?>

<script src="/resources/js/davis.min.js"></script>
<link rel="stylesheet" href="/resources/chosen/chosen.css">
<script src="/resources/chosen/chosen.jquery.js"></script>

<link rel="stylesheet" href="/resources/bootstrap-datepicker/css/datepicker.css">
<script src="/resources/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script language="javascript">
	$(function() {
		//Set the API key
		$(document.body).data('api_key', '<?= $this->config->item('api_key') ?>');
		
		//Router
		var app = Davis(function() {
			this.configure(function () {
				this.generateRequestOnPageLoad = true;
				this.raiseErrors = true;
				this.formSelector = "noforms";
			});
			
			this.before('/create/:content_type', function(req) {
				/*if ($(document.body).data('content_type') == req.params['content_type'] && $(document.body).data('page')=='list') {
					return false;
				}*/
			});
		
			this.get('#', function(req) {});
			this.get('/create/:content_type', function(req) {
				$(document.body).data('content_type', req.params['content_type']);
				$(document.body).data('page', 'list');
				prepRouter();
				init_create();
			});
		});
		
		app.start();
		
		function prepRouter() {
			$('#dyncontent').children().find('.wysiwyg').each(function() {
				var name=$(this).attr('name');
				var o=CKEDITOR.instances[name];
			    if (o) o.destroy();
			});
		}
		
		function init_create() {
			content_type=$(document.body).data('content_type');
			$(".menuitem").each(function() {
				$(this).removeClass('selected');
			});
			$('#menuitem_'+content_type).addClass('selected');
			$('#dyncontent').html("Loading...");
			$.getJSON("<?= base_url() ?>api/content/blank?jsoncallback=?", { api_key: $(document.body).data('api_key'), content_type: content_type, meta: true }, function(data) {
				$('#dyncontent').html(_.template($("#create-template").html(), { data:data, content_type: content_type }));
				init_form();
			});
		}
		
		$(document).on('click', '.the_action', function() {
			action = $(this).attr('id');
			$(document.body).data('action',action);
			if (!$(document.body).data('saving')) {
				save();
			}
			return false;
		});
		
		$(document).ajaxError(function(e, xhr, settings, exception) { 
			//$("#dyncontent").html('<h1>Caught error</h1>'+xhr.responseText); 
		});
		
		$("#dyncontent").ajaxComplete(function() {
			//cl.hide();
		});
		
		$("#dyncontent").ajaxStart(function() {
			//cl.show();
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
					url: "<?= base_url() ?>api/content/save?api_key=<?= $this->config->item('api_key') ?>&content_type="+content_type,  //server script to process data
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
			
			setTimeout(hide_progress_bar, 2000);
			$(document.body).data("saving",false);
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
				$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
			    $("#msgdialog").modal();
			} else {


				var url = '<?php echo base_url(); ?>';    
				if($(document.body).data("action") == '_create'){
					url += 'create/'+$(document.body).data('content_type');
				}

				if($(document.body).data("action") == '_reuse'){
					return false;
				}

				if($(document.body).data("action") == '_edit'){
					url += 'edit/'+$(document.body).data('content_type')+"/"+data.id;
				}

				$(location).attr('href',url);
				
			}
		}
		
		$("#dyncontent").delegate(".add-relation","click",function() {
		//Creates the popup box for adding a new item
			var fieldname=$(this).attr("contenttype")+"_"+$(this).attr("fieldname");
			$("#createdialog").dialog({ minWidth: 700, modal: true, }).load(
				"/create/fullview/"+$(this).attr("contenttype")+"/embed"
			);
			$("#createdialog").data("fieldname",fieldname);
			return false;
		});
		
		$(document).on('change', 'input[type=file]', function() {
			console.log("Change");
			content_type=$(document.body).data('content_type');
			var files=this.files; //FileList object
			var file=files[0]; //Only handle single upload at a time
			var el=$(this);
			var container=el.parent();
			var fd = new FormData();
			fd.append("data", file);
			fd.append("filename", $(this).val());
			$.ajax({
				url: "<?= base_url() ?>api/content/upload?api_key=<?= $this->config->item('api_key') ?>&filename="+file.name,  //server script to process data
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
						container.find('.alert').removeClass('alert-success').addClass('alert-error').html('<h4>File upload failed</h4> '+data.message).show();
						container.find('.preview-image .progress .bar').removeClass('bar-danger').addClass('bar-success');
						return false;
					}
					container.find('.preview-image .progress').hide();
					container.find('.alert').addClass('alert-success').removeClass('alert-error').html('File uploaded').show();
					container.find('input').each(function() { $(this).val(data.content.full_name) });
				},
				error: function(data) {
					container.find('.alert').removeClass('alert-success').addClass('alert-error').html('File upload failed').show();
					container.find('.preview-image .progress .bar').removeClass('bar-success').addClass('bar-danger');
				},
				// Form data
				data: file,
				//Options to tell JQuery not to process data or worry about content-type
				cache: false,
				contentType: false,
				processData: false
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
	});
</script>

<script type='text/template' id='create-template'>

<div class="row" >

	<div style='margin-left:0;' class='main_form_container span10'>
		<div class='root'>
			
			<div id="edit-content" class="span10" >
				<h2>Create - <%= content_type %></h2>
				<form id='contentform' class='form-horizontal span12' method='post' enctype='multipart/form-data' action='<?= base_url() ?>api/content/save?api_key=<%= $(document.body).data('api_key') %>'>
				<input type='hidden' name='action' value='submit' />
				<% _.each(data.meta, function(field) { %>
					<% if (!field.hidden) { %>
						<%= _.template($('#create-field-'+field.type).html(), { field: field, urlid: false, content_type: content_type  }) %>
					<% } %>
				<% }); %>
				</form>

			</div>
			<br clear='both'>
		</div>

		<div class="over_lay slider span10"></div>
    </div>

</div>

</script>

<?php
	$this->load->view("snippets/javascript_templates");
?>
<div id='bottom_bar' class="navbar navbar-fixed-bottom">
	<div class="navbar-inner">
		<div class="container">
			<ul class="nav">
				<li><button id="_create" class="the_action btn btn-mini btn-primary" id="dosubmit_right">Save and Create another</button></li>
				<li class='divider-vertical'></li>
				<li><button id="_reuse" class="the_action btn btn-mini btn-info" id="dosubmit_right">Save and Reuse Info</button></li>
				<li class='divider-vertical'></li>
				<li><button id="_edit" class="the_action btn btn-mini btn-warning" id="dosubmit_right">Save and Edit</button></li>
				<li class='divider-vertical'></li>
				<li><button id="_publish" class="the_action btn btn-mini btn-danger" id="dosubmit_right">Save and Publish</button></li>


				<li class='divider-vertical'></li>
				<li style=" padding-top:5px; width:300px;">
					<div id='progress_container' style='display:none;' class="progress progress-striped active">
						<div id="upload_indicator" class="bar" style="width: 0%;"></div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>

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

<div id="createdialog"></div>
<div id="dyncontent">

</div>
<?php
	$this->load->view("templates/footer");

?>