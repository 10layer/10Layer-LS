<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<script src="/resources/js/backbone-min.js"></script>
<script>
	var content_types = <?= json_encode($content_types) ?>;
	
	var ModelContentType = Backbone.Model.extend({});
	
	var CollectionContentTypes = Backbone.Collection.extend({
		model: ModelContentType,
		initialize: function() {
			this.content_type_id = 0;
		}
	});
	
	var collection_content_types=new CollectionContentTypes(content_types);
	
	var ContentTypesView = Backbone.View.extend({
		el: "#content_type_app",
		initialize: function() {
			this.render();
		},
		render: function() {
			$(this.el).html(_.template($("#content_types").html(), this.model));
		}
	});
	
	var ContentTypeView = Backbone.View.extend({
		el: "#core",
		initialize: function() {
			this.render();
		},
		render: function() {
			$(this.el).html(_template($("#content_types-core").html(), this.model));
		}
	});
	
	var ViewRules = Backbone.View.extend({
		render: function() {
			$(this.el).html(_.template($("#content_type-rule").html(), this.model));
		}
	});
	
	var types = new Backbone.Model({ 
		autocomplete: "Autocomplete",
		checkbox: "Checkbox",
		date: "Date",
		datetime: "Date Time",
		file: "File",
		hidden: "Hidden",
		image: "Image",
		nesteditems: "Tree",
		password: "Password",
		radio: "Radio",
		search: "Search",
		wysiwyg: "WYSIWYG editor",
		select: "Select",
		text: "Text",
		textarea: "Text Area"
	});
	
	var transformation_template = new Backbone.Model([
		{
			fn: "urlid",
			hint: "Unique URL identifier. Set to false to exclude date",
			var_check: function(x) { return _.isBoolean(x) }
		},
		{
			fn: "copy",
			hint: "Copies one field to another",
			var_check: function(x) { return _.isString(x) }
		},
		{
			fn: "copymultiple",
			hint: "Copy from multiple fields and join with $join",
			var_check: function(join, fields) { return _.isString(join) && _.isArray(fields) }
		},
		{
			fn: "concat",
			hint: "Concatenate a value onto field",
			var_check: function(x) { return _.isString(x) }
		},
		{
			fn: "soundslide",
			hint: "Unzips Soundslide bundles"
		},
		{
			fn: "str_replace",
			hint: "Replace $search with $replace",
			var_check: function(search, replace) { return _.isString(search) && _.isString(replace) }
		},
		{
			fn: "safetext",
			hint: "Returns very, very clean text",
		},
		{
			fn: "custom",
			hint: "Define your own rad transformation",
		}
	]);
	
	var rule_template = new Backbone.Model([
		{
			fn: "required",
			hint: "Required"
		},
		{ 
			fn: "minlen",
			hint: "Minimum length",
			var_check: function(x) { return _.isNumeric(x); }
		},
		{
			fn: "maxlen",
			hint: "Maximum length",
			var_check: function(x) { return _.isNumeric(x); }
		},
		{ 
			fn: "min_count",
			hint: "Minimum count",
			var_check: function(x) { return _.isNumeric(x); }
		},
		{
			fn: "max_count",
			hint: "Maximum count",
			var_check: function(x) { return _.isNumeric(x); }
		},
		{
			fn: "password_strength",
			hint: "Password strength",
			var_check: function(x) { return _.isNumeric(x); }
		},
		{
			fn: "valid_email",
			hint: "Must be a valid email address"
		},
		{
			fn: "database_nodupe",
			hint: "No duplicates allowed in the database. USAGE: database_nodupe={row} in {table}",
			var_check: function(x) { return _.isString(x); },
		},
		{
			fn: "valid_ip",
			hint: "Valid IP address"
		},
		{
			fn: "alpha",
			hint: "Alpha characters"
		},
		{
			fn: "alpha_numeric",
			hint: "Alphanumeric characters"
		},
		{
			fn: "alpha_numeric_dash",
			hint: "Alphanumeric and dash characters"
		},
		{
			fn: "alpha_dash",
			hint: "Alpha and dash characters"
		},
		{
			fn: "alpha_numeric_dash_space",
			hint: "Alphanumeric, dash and space characters"
		},
		{
			fn: "alpha_dash_space",
			hint: "Alpha, dash and space characters"
		},
		{
			fn: "numeric",
			hint: "Numeric"
		},
		{
			fn: "integer",
			hint: "Integer"
		},
		{
			fn: "is_natural",
			hint: "Natural number"
		},
		{
			fn: "is_natural_no_zero",
			hint: "Natural number excluding zero"
		},
		{
			fn: "valid_base64",
			hint: "Base64"
		},
		{
			fn: "valid_url",
			hint: "URL"
		},
		{
			fn: "match",
			hint: "Must match",
			var_check: function(x) { return _.isString(x); }
		}
	]);
	
	var req_types = new Array("urlid", "title", "last_modified", "start_date", "workflow_status");
	
	var content_type_id=<?= $content_type_id ?>;
	
	$(function() {
		var content_types_app = new ContentTypesView();
		
		function show_content_type(id) {
			$("#core").html(_.template($("#content_type-core").html(), { content_type: content_types[id] }));
			$("#fields").html("");
			_.each(content_types[id].fields, function(field, key) {
				_.defaults(field, { type: "text" });
				$("#fields").append(_.template($("#content_type-field").html(), { id: key, field: field } ));
			});
		}
		
		show_content_type(content_type_id);
		
		$(".field-edit").on("click", function() {
			$(this).parent().next().toggle();
			return false;
		});
		
		$(document).on('click', '.rule_add', function(e) {
			e.preventDefault();
			var rule = { fn: false };
			rule.fn = $(this).attr("data-fn");
			var field_id = $(this).attr("data-field_id");
			var rules = content_types[content_type_id].fields[field_id].rules;
			if (! rules) {
				rules = new Array();
			}
			if (!_.isArray(rules)) {
				rules = [ rules ];
			}
			rules.push(rule);
			console.log(rules);
			var self=this;
			_.each(rules, function(rule_template) {
				if (rule_template.fn == rule.fn) {
					$(self).parent().parent().parent().next().append(_.template($("#content_type-rule").html(), {rule: rule}) );
				}
			});
		});
		
		$(document).on('click', '.transformation_add', function(e) {
			e.preventDefault();
			var transformation_id=$(this).attr("data-fn");
			var self=this;
			_.each(transformation_template.toJSON(), function(transformation) {
				if (transformation.fn == transformation_id) {
					$(self).parent().parent().parent().next().append(_.template($("#content_type-transformation").html(), {transformation: transformation}) );
				}
			});
		});
		
		$(document).on('click', '.icon-arrow-up', function(e) {
			e.preventDefault();
			var self=this;
			var prev=$(self).parent().parent().prev();
			if (prev.is("div")) {
				el=$(self).parent().parent().detach();
				prev.before(el);
			}
		});
		
		$(document).on('click', '.icon-arrow-down', function(e) {
			e.preventDefault();
			var self=this;
			var prev=$(self).parent().parent().next();
			if (prev.is("div")) {
				el=$(self).parent().parent().detach();
				prev.after(el);
			}
		});
		
		$(document).on('click', '.icon-remove', function(e) {
			e.preventDefault();
			$(this).parent().parent().remove();
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
<script type='text/template' id='content_type-rule'>
	<div><dt><i class="icon-arrow-up"></i><i class="icon-arrow-down"></i><i class="icon-remove"></i> <%= rule.fn %> <%= (rule.vars) ? rule.vars.join(", ") : ''  %></dt>
	<dd><%= nullStr(rule.hint) %></dd></div>
</script>
<script type='text/template' id='content_type-transformation'>
	<div><dt><i class="icon-arrow-up"></i><i class="icon-arrow-down"></i><i class="icon-edit"></i><i class="icon-remove"></i> <%= transformation.fn %> <%= (transformation.vars) ? transformation.vars.join(", ") : ''  %></dt>
	<dd><%= nullStr(transformation.hint) %></dd></div>
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
			<% _.each(types.toJSON(), function(val, key) { console.log(val) %>
			<option value='<%= key %>' <%= (field.type == key) ? 'selected="selected"' : '' %>><%= val %></option>
			<% }); %>
		</select>
		<label>Default value</label>
		<input type="text" name="value" value="<%= nullStr(field.value) %>">
		
		<label>Rules</label>
		<div class="btn-group">
			<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
				Add a rule
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
			<% _.each(rule_template.toJSON(), function(rule) { %>
				<li><a class="rule_add" data-fn="<%= rule.fn %>" data-field_id="<%= id %>" href='#'><%= rule.fn %></a></li>
			<% }); %>
			</ul>
		</div>
		<dl>
		<% 
		if (field.rules) {
			_.each(field.rules, function(rule) { %>
				<%= _.template($("#content_type-rule").html(), {rule: rule}) %>
			<% });
		}
		%>
		</dl>
		<label>Transformations</label>
		<div class="btn-group">
			<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
				Add a transformation
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
			<% _.each(transformation_template.toJSON(), function(transformation) { %>
				<li><a class="transformation_add" data-fn="<%= transformation.fn %>" href='#'><%= transformation.fn %></a></li>
			<% }); %>
			</ul>
		</div>
		<dl>
		<% 
		if (field.transformations) {
			_.each(field.transformations, function(transformation) { %>
				<%= _.template($("#content_type-transformation").html(), { transformation: transformation }) %>
			<% });
		}
		%>
		</dl>
		<label>Import from another Content Type</label>
		<select name="content_type" multiple="multiple">
			<option value="">None</option>
			<% _.each(content_types, function(content_type) { %>
				<option value="<%= content_type._id %>" <%= (content_type._id == field.contenttype) ? 'selected="selected"' : '' %>><%= content_type.name %></option>
			<% }); %>
		</select>
		<label>OR</label>
		<label>Set pre-defined Options</label>
		<input type="text" name="options" value="<%= nullStr(field.options) %>">
		<label>OR</label>
		<label>Import from a file or network</label>
		<input type="text" name="external" value="<%= nullStr(field.external) %>">
		<label>Permitted File Types</label>
		<input type="text" name="filetypes" value="<%= nullStr(field.filetypes) %>">
		<label>Directory for Files</label>
		<input type="text" name="directory" value="<%= nullStr(field.directory) %>">
		<label class="checkbox"><input name="readonly" type="checkbox" <%= (field.readonly==true) ? 'checked="checked"' : '' %>> Read Only</label>
		<label class="checkbox"><input name="multiple" type="checkbox" <%= (field.multiple==true) ? 'checked="checked"' : '' %>> Allow Multiple Selections</label>
		<label class="checkbox"><input name="showcount" type="checkbox" <%= (field.showcount==true) ? 'checked="checked"' : '' %>> Show Count</label>
		<label class="checkbox"><input name="hidenew" type="checkbox" <%= (field.hidenew==true) ? 'checked="checked"' : '' %>> Hide New Button</label>
		</div>
	</fieldset>
	</div>
</script>

<script type='text/template' id='content_types'>
	<ul class="nav nav-tabs">
	<%
		var x=0;
		_.each(content_types, function(content_type) { 
	%>
		<li <%= (x==content_type_id) ? 'class="active"' : '' %>><a href="/setup/content_types/<%= content_type._id %>"><%= content_type.name %></a></li>
	<%
			x++;
		});
	%>
		<li><a href="#"><i class="icon-plus"></i></a></li>
	</ul>
	<div id="core"></div>
	<div id="fields" class="row"></div>	
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
		<div class="row">
			<div class="span6">
				<p>This is where you set up the meat of your CMS, your content types. You can customise your website completely by using different content types.</p>
				<p>There are some pre-made content types that you can select, or you can create your own.</p>
				<p>It's okay to accept the defaults. You'll be able to come back and change the content types later.</p>
			</div>
			<div class="span4">
				<button class="btn btn-large btn-primary"><i class="icon-fire icon-white"></i> Onward!</button>
			</div>
		</div>
		<div id="content_type_app"></div>
	</div>
	
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>