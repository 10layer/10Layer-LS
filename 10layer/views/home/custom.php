

<script type="text/javascript" >

$(function(){
	$(".chzn-select").chosen().change(function(){
		filter();
	});
	
	$(document.body).data('api_key', '<?= $this->config->item('api_key') ?>');
	$('#dyncontent').html("Loading...");
	$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", {  order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "major_version", "last_editor", "content_type" ] }, function(data) {
		$('#dyncontent').html(_.template($("#listing-template").html(), { data:data}));
	});

	$("#quick_search_button").live('click',function(){
		filter();
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



	function filter(){
		$("#quick_search_button").text('searching...');
		var edited_by = $("#edited_by").val();
		var content_types = $("#content_types").val();
		var workflows = $("#workflows").val();
		var search_string = $('#search_query').val();
		var params = {order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "major_version", "last_editor", "content_type" ] }
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
		
		

});

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
	    	<th width='500px'>Title</th>
	    	<th>Last Edit</th>
	    	<th>Edited by</th> 
	    	<th>Start Date</th>
	    	<th>Content Type</th>
	    	<th>Workflow</th>
	    </tr>
	    </thead>
	    <tbody>
		<% var x=0; _.each(content, function(item) {  %>
		    <tr id="row_<%= item.id %>">
		    	<td class='content-workflow-<%= item.major_version %>'><a href='/edit/<%= item.content_type %>/<%= item._id %>' content_urlid='<%= item._id %>' class='content-title-link'><%= item.title %></a></td>
		    	<td><%= item.last_modified %></td>
		    	<td><%= (item.last_editor) ? item.last_editor : '' %></td>
		    	
		    	<td><%= item.start_date %></td>
		    	<td><%= item.content_type %></td>
		    	<td class='content-workflow-<%= item.major_version %>'><%= version_map[item.major_version] %></td>
		    	
		    </tr>
		<% x++; }); %>
	    </tbody>
	</table>
</script>

















<div id="filter_pane">
	
	<div id="quick_search_container" >
		<h5>Quick Search / Filters </h5>
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
				<?php
					$this->load->model('model_workflow');
					$workflows=$this->model_workflow->getAll();
					$i = 0;
					foreach($workflows as $workflow) {
						echo "<option value='".$i."'>".$workflow->name."</option>";
						$i++;
					}
				?>
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

