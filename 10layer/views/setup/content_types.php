<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<script>
	var content_types = <?= json_encode($content_types) ?>;
	var types = { 
		autocomplete: "Autocomplete",
		cdn: "CDN",
		checkbox: "Checkbox",
		date: "Date",
		datetime: "Date Time",
		deepsearch: "Deep Search",
		external: "External",
		file: "File",
		hidden: "Hidden",
		image: "Image",
		nesteditems: "Nested Items",
		password: "Password",
		radio: "Radio",
		reverse: "Reverse",
		rich: "Rich",
		select: "Select",
		text: "Text",
		textarea: "Text Area"
	};
	
	var req_types = new Array("urlid", "title", "last_modified", "start_date", "workflow_status");
	
	$(function() {
	
		function show_content_type(id) {
			$("#core").html(_.template($("#content_type-core").html(), { content_type: content_types[id] }));
			$("#fields").html("");
			_.each(content_types[id].fields, function(field) {
				_.defaults(field, { type: "text" });
				$("#fields").append(_.template($("#content_type-field").html(), { field: field } ));
			});
		}
		
		show_content_type(<?= $content_type_id ?>);
		
		$(".field-edit").on("click", function() {
			$(this).parent().next().toggle();
			return false;
		});
	});
</script>
<script type='text/template' id='content_type-core'>
	<fieldset>
	    <legend>Core</legend>
	    <label>Name</label>
	    <input type="text" name="name" value="<%= nullStr(content_type.name) %>">
	    <label>Url</label>
	    <input type="text" name="urlid" value="<%= nullStr(content_type._id) %>">
	    <label>Order By</label>
	    <input type="text" name="order_by" value="<%= nullStr(content_type.order_by) %>">
	    <label class="checkbox"><input name="collection" type="checkbox" <%= (content_type.collection==true) ? 'checked="checked"' : '' %>> Collection</label>
	</fieldset>
	<legend>Fields</legend>
</script>

<script type='text/template' id='content_type-field'>
	<div class="span3">
	<fieldset>
		<legend><button class='field-edit btn btn-small btn-primary'><i class='icon-edit icon-white'></i></button> 
		<% if (!_.contains(req_types, field.name)) { %><button class='field-delete btn btn-small btn-warning'><i class='icon-trash icon-white'></i></button><% } %> <%= field.name %> </legend>
		<div class='field-details' style='display: none'>
		<label>Name</label>
		<input type="text" name="name" value="<%= nullStr(field.name) %>">
		<label>Label</label>
		<input type="text" name="label" value="<%= nullStr(field.label) %>">
		<label>Type</label>
		<select name="content_type">
			<% _.each(types, function(val, key) { %>
			<option value='<%= key %>' <%= (field.type == key) ? 'selected="selected"' : '' %>><%= val %></option>
			<% }); %>
		</select>
		<label>Default value</label>
		<input type="text" name="value" value="<%= nullStr(field.value) %>">
		<label class="checkbox"><input name="readonly" type="checkbox" <%= (field.readonly==true) ? 'checked="checked"' : '' %>> Read Only</label>
		<label>Rules</label>
		<input type="text" name="rules" value="<%= field.rules %>">
		<label>Transformations</label>
		<input type="text" name="transformations" value="<%= field.transformations %>">
		<label>Content Type</label>
		<input type="text" name="content_type" value="<%= nullStr(field.contenttype) %>">
		<label>Options</label>
		<input type="text" name="options" value="<%= nullStr(field.options) %>">
		<label>File Types</label>
		<input type="text" name="filetypes" value="<%= nullStr(field.filetypes) %>">
		<label>Directory</label>
		<input type="text" name="directory" value="<%= nullStr(field.directory) %>">
		<label>Link Format</label>
		<input type="text" name="linkformat" value="<%= nullStr(field.linkformat) %>">
		<label class="checkbox"><input name="multiple" type="checkbox" <%= (field.multiple==true) ? 'checked="checked"' : '' %>> Multiple</label>
		<label class="checkbox"><input name="showcount" type="checkbox" <%= (field.showcount==true) ? 'checked="checked"' : '' %>> Show Count</label>
		<label class="checkbox"><input name="hidenew" type="checkbox" <%= (field.hidenew==true) ? 'checked="checked"' : '' %>> Hide New</label>
		</div>
	</fieldset>
	</div>
</script>
<div class="page-header">
	<h1>Setup</h1>
</div>
<form method="post">
<div class="row">
	<div class="span2">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="/setup/admin">Administrator</a></li>
			<li><a href="/setup/users">Users</a></li>
			<li class="active"><a href="/setup/content_types">Content Types</a></li>
			<li><a href="/setup/security">Security</a></li>
		</ul>
	</div>
	<div class="span10">
		<p>This is where you set up the meat of your CMS, your content types. You can customise your website completely by using different content types.</p>
		<p>There are some pre-made content types that you can select, or you can create your own.</p>
		<ul class="nav nav-tabs">
			<?php
			$x=0;
			foreach($content_types as $content_type) {
			?>
			<li <?= ($x==$content_type_id) ? 'class="active"' : '' ?>><a href="/setup/content_types/<?= $content_type->_id ?>"><?= $content_type->name ?></a></li>
			<?php
				$x++;
			}
			?>
			<li><a href="#"><i class="icon-plus"></i></a></li>
		</ul>
		<div id="core"></div>
		<div id="fields" class="row"></div>	
	</div>
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>