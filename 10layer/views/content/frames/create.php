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
				if ($(document.body).data('content_type') == req.params['content_type'] && $(document.body).data('page')=='list') {
					return false;
				}
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
			$('#dyncontent').children().find('.richedit').each(function() {
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
			$.getJSON("<?= base_url() ?>create/jsoncreate/"+content_type+"?jsoncallback=?", function(data) {
				$('#dyncontent').html(_.template($("#create-template").html(), { data:data, content_type: content_type }));
				init_form();
			});
		}
		
		$(document).on('click', '#dosubmit_right', function() {
			if (!$(document.body).data('saving')) {
				$("#contentform").submit();
			}
			return false;
		});
		
		$(document).ajaxError(function(e, xhr, settings, exception) { 
			//$("#dyncontent").html('<h1>Caught error</h1>'+xhr.responseText); 
		});
		
		$("#dyncontent").ajaxComplete(function() {
			cl.hide();
		});
		
		$("#dyncontent").ajaxStart(function() {
			cl.show();
		});
		
		/*$("#dyncontent").load("<?= base_url()."create/fullview/$type" ?>", function() {
			if ($(".richedit").length) {
//				init_tinymce();
				initCKEditor();
			}
			
			$(".datepicker").datepicker({dateFormat:"yy-mm-dd"});
		});*/
		
		$("#dyncontent").delegate("#contentform","submit",function() {
			$(document).data("saving",true);
			content_type=$(document.body).data('content_type');
			if (!$(document.body).data('saving')) {
				$(document.body).data('saving', true);
				var formData = new FormData($('#contentform')[0]);
				$.ajax({
					url: "<?= base_url() ?>/workers/api/insert/"+content_type+"/<?= $this->config->item('api_key') ?>",  //server script to process data
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
			/*$(this).ajaxSubmit({
				iframe: true,
				dataType: "json",
				beforeSubmit: function(a,f,o) {
					o.dataType = "json";
					$(document).data("saving",true);
				},
				success: function(data) {
					$(document).data("saving",false);
					if (data.error) {
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
								"Create another": function() {
									location.href="<?= base_url() ?>create/"+$(document.body).data('content_type');
								},
								"Reuse info": function() {
									$(this).dialog( "close" );
								},
								"Edit": function() {
									location.href="<?= base_url() ?>edit/"+$(document.body).data('content_type')+"/"+data.data.urlid;
								}
							}
						});
					}
				},
				error: function(e) {
					$(document).data("saving",false);
					$("#msgdialog").html("<div class='ui-state-error' style='padding: 5px'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span><strong>Error</strong><br /> Problem communicating with the server: "+e.error+"</p></div>");
					$("#msgdialog").dialog({
						modal: true,
						buttons: {
							Ok: function() {
								$(this).dialog("close");
							}
						}
					});
				},
			});*/
			return false;
		});
		
		function uploadBefore(e) {}
		
		function uploadProgress(e) {
			//console.log("Upload progress");
			//console.log(e);
		}
		
		function uploadComplete(data) {
			$(document.body).data("saving",false);
			if (data.error) {
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
			    		"Create another": function() {
			    			location.href="<?= base_url() ?>create/"+$(document.body).data('content_type');
			    		},
			    		"Reuse info": function() {
			    			$(this).dialog( "close" );
			    		},
			    		"Edit": function() {
			    			location.href="<?= base_url() ?>edit/"+$(document.body).data('content_type')+"/"+data.data.urlid;
			    		}
			    	}
			    });
			}
		}
		
		function uploadFailed(e) {
			$(document.body).data("saving",false);
			$("#msgdialog").html("<div class='ui-state-error' style='padding: 5px'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span><strong>Error</strong><br /> Problem communicating with the server: "+e.statusText+"</p></div>");
			$("#msgdialog").dialog({
				modal: true,
				buttons: {
					Ok: function() {
						$(this).dialog("close");
					}
				}
			});
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
		
		
		$("#createdialog").delegate("#contentform","submit",function() {
		//Handles the submit for a new item
			$("#createdialog #contentform").ajaxSubmit({
				dataType: "json",
				iframe: true,
				
				beforeSubmit: function(a,f,o) {
					o.dataType = "json";
				},
				success: function(data) {
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
						$("#msgdialog").html("<div class='ui-state-highlight' style='padding: 5px'><p><span class='ui-icon ui-icon-info' style='float: left; margin-right: .3em;'></span><strong>Saved</strong></p></div>");
						var title=data.data.title;
						var id=data.data.id;
						var fieldname=$("#createdialog").data("fieldname");
						var newoption="<option value='"+id+"'>"+title+"</option>";
						$("."+fieldname).prepend(newoption);
						$("."+fieldname).val(id);
						//$("#dyncontent").find()
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
</script>

<script type='text/template' id='create-template'>
	<%	
		if (typeof popup == 'undefined') {
			popup = false;
		}
	%>
	<div id="create-content" class="boxed wide">
		<h2>Create - <%= content_type %></h2>
		<form id='form_create_<%= content_type %>' class='form-horizontal span12 <%= (popup) ? "popupform" : "contentform" %>' method='post' enctype='multipart/form-data' action='<?= base_url() ?>api/content/save?api_key=<%= $(document.body).data('api_key') %>'>
		<input type='hidden' name='action' value='submit' />
		<% _.each(data.fields, function(field) { %>
			<% if (!field.hidden) { %>
				<%= _.template($('#create-field-'+field.type).html(), { field: field, urlid: false, content_type: content_type  }) %>
			<% } %>
		<% }); %>
		<% if (popup) { %>
		<button contenttype="<%= content_type %>" fieldname="<%= name %>" class='dosubmit_popup'>Save</button>
		<% } %>
		</form>
	</div>
	<% if (popup == false) { %>
	<div id="sidebar" class="pin">
		<div id="sidebar_accordian">
			<h3><a href="#">Actions</a></h3>
			<div>
				<button id="dosubmit_right">Save</button><br />
				<br />
			</div>
		</div>
	</div>
	<% } %>
</script>

<?php
	$this->load->view("snippets/javascript_templates");
?>


<div id="msgdialog"></div>
<div id="createdialog"></div>
<div id="dyncontent">

</div>
<?php
	$this->load->view("templates/footer");

?>