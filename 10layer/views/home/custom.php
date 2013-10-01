<script type="text/javascript" >
$(function(){
	$(".chzn-select").chosen().change(function(){
		filter();
	});
	
	$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
	$('#dyncontent').html("Loading...");
	$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", {  order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }, function(data) {
		$('#dyncontent').html(_.template($("#listing-template").html(), { data:data}));
	});

	$("#quick_search_button").live('click',function(){
		filter();
	});

	version_map=new Array(
		"",
		"New",
		"Edited",
		"Published"
	);

	function filter() {
		var edited_by = $("#edited_by").val();
		var content_types = $("#content_types").val();
		var workflow = $("#workflows").val();
		var search_string = $('#search').val();
		var params = {order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }
		if(search_string != '' && search_string){
			params.search = search_string;
		}
		if(content_types != '' && content_types){
			params.content_type = content_types;
		}
		if(workflow != '' && workflow){
			params.workflow = workflow;
		}
		if(edited_by != '' && edited_by){
			params.last_editor = edited_by;
		}
		$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", params, function(data) {
			$('#dyncontent').html(_.template($("#listing-template").html(), {data:data}));
		});
	}
	
	$(document).on("click", ".reset", function() {
		var el = $(this).next();
		el.val('').trigger("liszt:updated");
		
		filter();
	});

	$(document).on('click', '#select_all', function() {
		$(".select_item").prop("checked", $(this).prop("checked"));
	});

	var timer = null;

	$(document).on('keyup','#search', function(e) {
		if(e.keyCode == '13'){
			window.clearTimeout(timer);
			filter();
		}
		window.clearTimeout(timer);
		timer = window.setTimeout(filter, 500);
	});

	$(document).on('click', '.workflow_change', function() {
		var workflow = $(this).attr("data-workflow");
		var items = [];
		$(".select_item:checked").each(function() {
			items.push({ id: $(this).val(), workflow_status: workflow });
		});
		$.getJSON("<?= base_url() ?>api/content/multiple/change_workflow?jsoncallback=?", { items: items, api_key: $(document.body).data('api_key') }, function(data) {
			$(".select_item:checked").each(function() {
				$(this).parent().parent().children().last().html(workflow);
				$(this).parent().parent().children().first().next().removeClass("new");
				$(this).parent().parent().children().first().next().removeClass("edited");
				$(this).parent().parent().children().first().next().removeClass("published");
				$(this).parent().parent().children().first().next().addClass(workflow.toLowerCase());
				$(this).dropdown("toggle");
			});
		});
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
});

</script>

<script type="text/template" id="listing-template">
	<div id="contentlist" class="boxed full">
		<div id='pagination' class='pagination' style="float: left; margin-right: 50px"></div>
		
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content: data.content }) %>
		</div>
	</div>
</script>

<script type="text/template" id="listing-template-old">
	<div id="contentlist" class="boxed full">
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content: data.content }) %>
		</div>
	</div>
</script>

<script type='text/template' id='listing-template-content'>
	<table class='table table-bordered table-striped table-condensed'>
	    <thead>
	    <tr>
	    	<th><input type="checkbox" class="select-all" id="select_all" /></th>
	    	<th>Title</th>
	    	<th>Last Edit</th>
	    	<th>Edited by</th> 
	    	<th>Start Date</th>
	    	<th>Content Type</th>
	    	<th>Workflow</th>
	    </tr>
	    </thead>
	    <tbody>
		<% var x=0; _.each(content, function(item) { %>
		    <tr id="row_<%= item.id %>">
		    	<td><input type="checkbox" class="select_item" name="select_item" value="<%= item._id %>"></td>
		    	<td class='<%= item.workflow_status.toLowerCase() %>'><a href='/edit/<%= item.content_type %>/<%= item._id %>' content_urlid='<%= item._id %>' class='content-title-link'><%= item.title %></a></td>
		    	<td><%= dateToString(item.last_modified) %></td>
		    	<td><%= (item.last_editor) ? item.last_editor : '' %></td>
		    	
		    	<td><%= dateToString(item.start_date) %></td>
		    	<td><%= item.content_type %></td>
		    	<td class='content-workflow-<%= item.workflow_status %>'><%= item.workflow_status %></td>
		    </tr>
		<% x++; }); %>
	    </tbody>
	</table>
</script>
<div id="filter_pane">
	<div id="quick_search_container" >
		<div style="float:left; margin-right:10px; margin-top:4px;">
			<a href="#" class="close reset" style="float: right">&times;</a>
			<select id='content_types' class="chzn-select" data-placeholder="Content type" name="tag_workflow_status" id="" style='width:200px;display:none;'>
				<option value=''></option>
				<?php
					$this->load->model('model_content');
					$content_types=$this->model_content->get_content_types();
					foreach($content_types as $ct){
						echo "<option value='".$ct->_id."'>".$ct->name."</option>";
					}
				?>
			</select>
		</div>

		<div style="float:left; margin-right:10px; margin-top:4px;">
			<a href="#" class="close reset" style="float: right">&times;</a>
			<select id='edited_by' class="chzn-select" data-placeholder="Edited by" name="edited_by" id="" style='width:200px;display:none;'>
				<option value=''></option>
				<?php
					$this->load->model('model_user');
					$users=$this->model_user->get_all_users();
					foreach($users as $user){
						echo "<option value='".$user->name."'>".$user->name."</option>";
					}
				?>
			</select>
		</div>
		
		<div style="float:left; margin-right:10px; margin-top:4px;">
			<a href="#" class="close reset" style="float: right">&times;</a>
			<select id='workflows' class="chzn-select" data-placeholder="Workflow status" name="tag_workflow_status" id="" style='width:200px;display:none;'>
				<option value=''></option>
				<option value="New">New</option>
				<option value="Edited">Edited</option>
				<option value="Published">Published</option>
			</select>
		</div>
		<div class="input-append" style="float:right;">
			<a href="#" class="close reset" style="float: right">&times;</a>
			<input type="text" id='search' class="" placeholder="search" />
		 </div>
	
		<div id="group_actions" class="btn-group" style="float: left; ">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">With selected <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="#" class="workflow_change" data-workflow="New">Workflow - New</a></li>
				<li><a href="#" class="workflow_change" data-workflow="Edited">Workflow - Edited</a></li>
				<li><a href="#" class="workflow_change" data-workflow="Published">Workflow - Published</a></li>
				<li><a href="#" id="_delete_multiple">Delete</a></li>
			</ul>
		</div>
	</div>
</div>
<div id="dyncontent"></div>

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