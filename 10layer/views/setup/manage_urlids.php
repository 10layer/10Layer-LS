

<script type="text/javascript" >
$(function(){
	$(".chzn-select").chosen().change(function(){
		filter();
	});

	$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
	$('#dyncontent').html("Loading...");
	$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", {  order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }, function(data) {
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

	function filter(){
		$("#quick_search_button").text('searching...');
		var edited_by = $("#edited_by").val();
		var content_types = $("#content_types").val();
		var workflows = $("#workflows").val();
		var search_string = $('#search_query').val();
		var params = {order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }
		if(search_string != ''){
			params.search = search_string;
		}
		if(content_types != ''){
			params.content_type = content_types;
		}
		if(workflows != ''){
			params.workflow = workflows;
		}
		if(edited_by != ''){
			params.last_editor = edited_by;
		}


		$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", params, function(data) {
			$('#dyncontent').html(_.template($("#listing-template").html(), {data:data}));
			$("#quick_search_button").text('Quick Search');
		});
	}



	$(document).on('click', '.urlid_changer', function(){
		var id = $(this).attr('id');
		var content_type = $(this).attr('content_type');
		$(this).parent().html(_.template($('#change_urlid').html(), { id: id, contenttype:content_type }));
	});

	$(document).on('click', '#cancel_urlid', function(){
		var id = $(this).prev().prev().attr('id');
		$(this).parent().parent().html(_.template($('#restore_urlid').html(), { id: id }));
	});

	$(document).on('click', '#save_urlid', function(){
		var pointer = $(this);
		var id = $(this).prev().attr('id');
		var contenttype = $(this).prev().attr('contenttype');
		var new_value = $(this).prev().val();
		var params = {id: id, new_val: new_value,contenttype:contenttype, api_key: $(document.body).data('api_key') };
		if(id != new_value){
			$.getJSON("<?= base_url() ?>api/content/update_urlid?jsoncallback=?", params, function(data){

				if(!data.error){
					pointer.parent().parent().html(_.template($('#restore_urlid').html(), { id: new_value })).append(_.template($('#message').html(), { message: data.msg, the_class:'success' }) );
				}else{
					pointer.parent().parent().append(_.template($('#message').html(), { message: data.msg, the_class:'error' }) );
				}

			});
		}else{
			pointer.parent().parent().html(_.template($('#restore_urlid').html(), { id: new_value }));
		}

	});



});

</script>

<script type="text/template" id="change_urlid">
	<div class="input-append">
	  <input contenttype='<%= contenttype %>' class="span3" id="<%= id %>" type="text" value='<%= id %>'>
	  <button id='save_urlid' class="btn" type="button">Save</button>
	  <button id='cancel_urlid' class="btn" type="button">Cancel</button>
	</div>
</script>

<script type="text/template" id="message">
<div class="alert alert-<%= the_class %> fade in">
	<button type="button" class="close" data-dismiss="alert">×</button>
	<%= message %>
</div>
</script>

<script type="text/template" id="restore_urlid">
	<a id='<%= id %>' class='urlid_changer'><%= id %></a>
</script>

<script type="text/template" id="listing-template">
	<div id="contentlist" class="boxed full">
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content: data.content }) %>
		</div>
	</div>
</script>

<script type='text/template' id='listing-template-content'>
	<table class='table  table-striped'>
	    <thead>
	    <tr>
	    	<th width='400px'>Title</th>
	    	<th width='400px'>Url ID</th>
	    	<th>Last Edit</th>

	    	<th>Edited by</th>

	    	<th>Content Type</th>

	    </tr>
	    </thead>
	    <tbody>
		<% var x=0; _.each(content, function(item) {  %>
		    <tr id="row_<%= item.id %>">
		    	<td ><%= item.title %></td>
		    	<td class='content-workflow-<%= item.major_version %>'><a content_type='<%= item.content_type %>' id='<%= item._id %>' class='urlid_changer'><%= item._id %></a></td>
		    	<td><%= dateToString(item.last_modified) %></td>
		    	<td><%= (item.last_editor) ? item.last_editor : '' %></td>
		    	<td><%= item.content_type %></td>

		    </tr>
		<% x++; }); %>
	    </tbody>
	</table>
</script>

<div id="filter_pane">

	<div id="quick_search_container" >
		<h5>Manage Url IDs </h5>
		<div style="float:left; margin-right:10px; margin-top:4px;">
			<select id='edited_by' class="chzn-select" data-placeholder="Edited by..." name="edited_by" id="" style='width:200px;display:none;'>
				<option value=''></option>
				<?php
					$this->load->model('model_user');
					$users=$this->model_user->getAllUsers();
					foreach($users as $user){
						echo "<option value='".$user->name."'>".$user->name."</option>";
					}
				?>
			</select>
		</div>

		<div <div style="float:left; margin-right:10px; margin-top:4px;">
			<select id='content_types' class="chzn-select" data-placeholder="Content Types..." name="tag_workflow_status" id="" style='width:200px;display:none;'>
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

		<div <div style="float:left; margin-right:10px; margin-top:4px;">
			<select id='workflows' class="chzn-select" data-placeholder="Choose Workflow status" name="tag_workflow_status" id="" style='width:200px;display:none;'>
				<option value=''></option>
				<option>New</option>
				<option>Edited</option>
				<option>Published</option>
			</select>
		</div>

		<div class="input-append" style="float:right;">
		  <input type="text" id='search_query' class="">
		  <a id="quick_search_button" class="btn">Quick Search</a>
		 </div>

	</div>

</div>
<div id="dyncontent">

</div>