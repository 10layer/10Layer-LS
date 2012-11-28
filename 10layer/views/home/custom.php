
<script src="/resources/js/davis.min.js"></script>
<script type="text/javascript" >

$(function(){

	$(document.body).data('api_key', '<?= $this->config->item('api_key') ?>');
	$('#dyncontent').html("Loading...");
	$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", {  order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "major_version", "last_editor", "content_type" ] }, function(data) {
		
		$('#dyncontent').html(_.template($("#listing-template").html(), {heading: 'Latest Content Items', data:data}));
	});

	$("#quick_search_button").live('click',function(){
		$(this).text('searching...');
		var search_string = $('#search_query').val();
		var heading = 'Search results for "'+search_string+'"';
		$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", { search: search_string, order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "major_version", "last_editor", "content_type" ] }, function(data) {
			$('#dyncontent').html(_.template($("#listing-template").html(), {heading: heading, data:data}));
			$(this).text('Quick Search');
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
		
		

});

</script>


<script type="text/template" id="listing-template">
	<h3 style="float:left;"><%= heading %></h3>
	<%= _.template($('#quick-search-template').html(),{}) %>
	<br clear="both">
	<div id="contentlist" class="boxed full">
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content: data.content }) %>
		</div>
	</div>
</script>

<script type="text/template" id="quick-search-template">
	<div id="quick_search_container" style="margin-top:10px;float:right;">
		<div class="input-append">
		  <input type="text" id='search_query' class="">
		  <a id="quick_search_button" class="btn">Quick Search</a>
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

















<script type="text/template" id="content-template">
	<div class="content">
		<div class="content-tools">
			<div class="btn-send" id="<%= id %>">Send to</div>
			<a href="/edit/<%= content_type %>/<%= urlid %>" target="_blank"><div class="btn-edit">Edit</div></a>
			<div class="btn-workflowprev">Revert Workflow</div>
			<div class="btn-workflownext">Advance Workflow</div>
			<div class="btn-live"><%= live ? 'Make unlive' : 'Make live' %></div>
		</div>
		<div class="directory_container shadow"></div>
		<div class="content-title content-workflow-<%= major_version %>"><%= title %></div>
	</div>
</script>

<script type="text/template" id="filters-template">
	<div class="option">
		<div class="option_header"><%= label %></div>
		<div class="allnone" queueid="<%= queueid %>"><span class="select-all">All</span> | <span class="select-none">None</span></div>
		<ul>
		<% _.each(options, function(option) { %>
			<%= _.template($("#filter-template").html(), option) %>
		<% }); %>
		</ul>
	</div>
	<br clear="both" />
</script>

<script type="text/template" id="filter-template">
	<div class="filter">
		<input class="filter_check" type="checkbox" <%= checked ? 'checked="checked"' : '' %> value="<%= urlid %>" />
		<%= value %>
	</div>
</script>

<script type="text/template" id="queue-template">
	<div id="<%= id %>" class="<%= personal %>" >
		<div class="options_icons">
			<div class="queue-name"><input class="queuename-edit" name="queuename" value="<%= name %>" /></div>
			<div class="options_close">Delete queue</div>
			<div class="options_dropdown">Filter queue</div>
			<div class="options_personalise">Make this queue personal</div>
		</div>
		
		
		<div class="queue_formatter" style="height:<%= height %>px; width:<%= width %>px">
		<div class="queue-content"></div>
		
	</div>
	<div class="options shadow" style="z-index:100000">
		<a class="config_close">close</a>
		<h4><%= name %> Configuarations...</h4>
		<div class="filters">
		</div>
	</div>
</script>

<div id="dyncontent">
	
</div>

