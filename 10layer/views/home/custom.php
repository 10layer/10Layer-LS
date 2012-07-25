<link rel="stylesheet" href="/resources/css/home.css" type="text/css" media="screen, projection" charset="utf-8" />
<script type="text/javascript" src="/resources/backbone/backbone-min.js"></script>
<script type="text/javascript" src="/tlresources/file/jquery/jquery.center.js"></script>
<script type="text/javascript" src="/tlresources/file/js/queues/queues.js"></script>

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

<div id="homepage">
	<div id="dialog_first_queue" style="display: none">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You don't have any queues. Create one now?</p>
	<div id="dialog_confirm_queue_delete" style="display: none">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Delete this queue?</p>
	</div>
	</div>
	<div id="topbuttons">
		<div class="addqueue">Add a queue</div> <div id="tiles">Tile queues</div>
	</div>
	<div id="queues"></div>
</div>

