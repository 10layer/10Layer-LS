<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<script src="/resources/knockout/knockout-2.2.0.js"></script>
<script>

	var Transformation = function(data) {
		var self = this;
		self.fn = ko.observable(data.fn);
		self.hint = ko.observable(data.hint);
		self.vars = ko.observable(data.vars);
		self.params = ko.observable(data.var);
		self.var_check = ko.observable(data.var_check);
	}

	var Rule = function(data) {
		var self = this;
		self.fn = ko.observable(data.fn);
		self.hint = ko.observable(data.hint);
		self.vars = ko.observable(data.var);
		self.var_check = ko.observable(data.var_check);
	}
	
	var Field = function(data) {
		var self = this;
		self.name = ko.observable(data.name);
		self.isRemovable = (!_.contains(req_types, data.name));
		self.label = ko.observable(data.label);
		self.type = ko.observable(data.type);
		self.defaultValue = ko.observable(data.default);
		self.options = ko.observable(data.options);
		self.external = ko.observable(data.external);
		self.filetypes = ko.observable(data.filetypes);
		self.directory = ko.observable(data.directory);
		self.readonly = ko.observable(data.readonly);
		self.multiple = ko.observable(data.multiple);
		self.showcount = ko.observable(data.showcount);
		self.hidenew = ko.observable(data.hidenew);
		self.rules = ko.observableArray(_.map(data.rules, function(item) { return new Rule(item) }));
		self.transformations = ko.observableArray(_.map(data.transformations, function(item) { return new Transformation(item) }));
		
		self.clickTransformationsUpArrow = function(data) {
			var pos = self.transformations.indexOf(data);
			if (pos <= 0) {
				return;
			}
			var tmp = self.transformations();
			self.transformations.splice(pos-1, 2, tmp[pos], tmp[pos-1]);
		}
		
		self.clickTransformationsDownArrow = function(data) {
			var pos = self.transformations.indexOf(data);
			if (pos >= self.transformations().length - 1) {
				return;
			}
			var tmp = self.transformations();
			self.transformations.splice(pos, 2, tmp[pos + 1], tmp[pos]);
		}
		
		self.clickTransformationsRemove = function(data) {
			self.transformations.remove(data);
		}
		
		self.clickTransformationsAdd = function(data) {
			self.transformations.push(new Transformation(data));
		}
		
		self.clickRulesUpArrow = function(data) {
			var pos = self.rules.indexOf(data);
			if (pos <= 0) {
				return;
			}
			var tmp = self.rules();
			self.rules.splice(pos-1, 2, tmp[pos], tmp[pos-1]);
		}
		
		self.clickRulesDownArrow = function(data) {
			var pos = self.rules.indexOf(data);
			if (pos >= self.rules().length - 1) {
				return;
			}
			var tmp = self.rules();
			self.rules.splice(pos, 2, tmp[pos + 1], tmp[pos]);
		}
		
		self.clickRulesRemove = function(data) {
			self.rules.remove(data);
		}
		
		self.clickRulesAdd = function(data) {
			self.rules.push(new Transformation(data));
		}
	}
	
	var ContentType = function(data, key) {
		var self = this;
		
		//Clean up our MongoDB mess
		if (data.collection == "0") {
			data.collection = false;
		}
		
		self.fields = ko.observableArray([]);
		self.fields(
			_.map(
				data.fields, function(item) { 
					return new Field(item)
				}
			)
		);
		self.urlid = ko.observable(data._id);
		self.id = data._id; // We keep an immutable copy of this so we know which one to edit
		self.name = ko.observable(data.name);
		self.collection = ko.observable(data.collection);
		self.order_by = ko.observable(data.order_by);
		self.isActive = (data._id == content_type_urlid);
		
		//Save at this level to not wipe all the content types
		self.save = function() {
        	$.ajax("/api/content_types/save?api_key=<?= $this->config->item("api_key") ?>", {
				data: ko.toJSON({ content_type: self }),
				type: "post", contentType: "application/json",
				success: function(result) { alert(result) }
			});
		}; 
	};
		
	var ContentTypesModel = function() {
		var self = this;
		
		self.contentTypes = ko.observableArray([]);
		self.contentType = ko.observableArray([]);
		
		self.types = ko.observableArray(types);
		self.rules = ko.observableArray(rule_template);
		self.transformations = ko.observableArray(transformation_template);
		
		$.getJSON("/api/content_types?api_key=<?= $this->config->item("api_key") ?>", function(data) {
			content_types=data.content;
			var mappedContentTypes = _.map(content_types, function(item, key) { return new ContentType(item, item._id);  });
			self.contentTypes(mappedContentTypes);
			_.each(mappedContentTypes, function(item) {
				if (item.urlid() == content_type_urlid) {
					self.contentType(item);
				}
			});
			//self.contentType(mappedContentTypes[content_type_urlid]);
		});
	};
	
	var types = ([
		{ _id: "autocomplete", name: "Autocomplete" },
		{ _id: "checkbox", name: "Checkbox" },
		{ _id: "date", name: "Date" },
		{ _id: "datetime", name: "Date Time" },
		{ _id: "file", name: "File" },
		{ _id: "hidden", name: "Hidden" },
		{ _id: "image", name: "Image" },
		{ _id: "nesteditems", name: "Tree" },
		{ _id: "password", name: "Password" },
		{ _id: "radio", name: "Radio" },
		{ _id: "search", name: "Search" },
		{ _id: "wysiwyg", name: "WYSIWYG editor" },
		{ _id: "select", name: "Select" },
		{ _id: "text", name: "Text" },
		{ _id: "textarea", name: "Text Area" }
	]);
	
	var transformation_template = ([
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
	
	var rule_template = ([
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
	
	var content_type_urlid="<?= $content_type_urlid ?>";
	
	$(function() {
		ko.applyBindings(new ContentTypesModel());
		
		//Events
		$(document).on('click', '.field-edit', function(e) {
			e.preventDefault();
			$(this).parent().next().toggle();
		});
		
	});
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
		<div id="content_type_app">
			<script> var x=0; </script>
			<ul class="nav nav-tabs">
				<!-- ko foreach: contentTypes -->
				<li data-bind="attr: { class: (isActive) ? 'active' : '' }"><a data-bind="text: name, attr: { href: urlid }"></a></li>
				<!-- /ko -->
				<li><a href="#"><i class="icon-plus"></i></a></li>
			</ul>
			<fieldset>
				<button data-bind="click: contentType().save">Save</button>
	   			<legend>Core</legend>
				<label>Name</label>
				<input type="text" name="name" value="" data-bind="value: contentType().name ">
		 		<label>ID</label>
				<input type="text" name="urlid" value="" data-bind="value: contentType().urlid ">
				<label>Order By</label>
				<input type="text" name="order_by" value="" data-bind="value: contentType().order_by ">
				<label class="checkbox"><input name="collection" type="checkbox" data-bind="checked: contentType().collection"> Collection</label>
			</fieldset>
			<legend>Fields</legend>
			<div data-bind="foreach: contentType().fields">
				<div class="span3">
				<fieldset>
					<legend><button class='field-edit btn btn-small btn-primary'><i class='icon-edit icon-white'></i></button> 
						<!-- ko if: isRemovable -->
						<button class='field-delete btn btn-small btn-warning'><i class='icon-trash icon-white'></i></button>
						<!-- /ko --> 
						<span data-bind="text: name"></span> 
					</legend>
					<div class='field-details' style='display: none'>
						<label>Name</label>
						<input type="text" name="name" value="" data-bind="value: name">
					
						<label>Label</label>
						<input type="text" name="label" value="" data-bind="value: label">
					
						<label>Type</label>
						<select name="content_type" data-bind="foreach: $parent.types">
							<option value='' data-bind="text: name, value: _id"></option>					
						</select>
					
						<label>Default value</label>
						<input type="text" name="value" value="" data-bind="value: defaultValue">
			
						<label>Rules</label>
						<div class="btn-group">
							<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
								Add a rule <span class="caret"></span>
							</a>
							<ul class="dropdown-menu" data-bind="foreach: $parent.rules">
								<li><a class="rule_add" data-bind='text: fn, click: $parent.clickRulesAdd' href='#'></a></li>
							</ul>
						</div>
						<dl data-bind="foreach: rules">
							<div>
							<dt><i class="icon-arrow-up" data-bind="click: $parent.clickRulesUpArrow"></i><i class="icon-arrow-down" data-bind="click: $parent.clickRulesDownArrow"></i><i class="icon-remove" data-bind="click: $parent.clickRulesRemove"></i> <span data-bind="text: fn"></span> <span data-bind="text: vars"></span></dt>
							<dd data-bind="text: hint"></dd>
							</div>
						</dl>
						
						<label>Transformations</label>
						<div class="btn-group">
							<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
								Add a transformation <span class="caret"></span>
							</a>
							<ul class="dropdown-menu" data-bind="foreach: $parent.transformations">
								<li><a data-bind='text: fn, click: $parent.clickTransformationsAdd' href='#'></a></li>
							</ul>
						</div>
						<dl data-bind="foreach: transformations">
							<div>
							<dt><i class="icon-arrow-up" data-bind="click: $parent.clickTransformationsUpArrow"></i><i class="icon-arrow-down" data-bind="click: $parent.clickTransformationsDownArrow"></i><i class="icon-remove" data-bind="click: $parent.clickTransformationsRemove"></i> <span data-bind="text: fn"></span> <span data-bind="text: vars"></span></dt>
							<dd data-bind="text: hint"></dd>
							</div>
						</dl>
						
						<label>Import from another Content Type</label>
						<select name="content_type" multiple="multiple">
							<option value="">None</option>
							<!-- ko foreach: $parent.contentTypes -->
								<option data-bind="value: urlid, text: name" value=""></option>
							<!-- /ko -->
						</select>
						
						<div><strong>OR</strong></div>
						
						<label>Set pre-defined Options</label>
						<input type="text" name="options" value="" data-bind="value: options" />
						
						<div><strong>OR</strong></div>
						
						<label>Import from a file or network</label>
						<input type="text" name="external" value="" data-bind="value: external" />
						
						<label>Permitted File Types</label>
						<input type="text" name="filetypes" value="" data-bind="value: filetypes">
						
						<label>Directory for Files</label>
						<input type="text" name="directory" value="" data-bind="value: directory">
						
						<label class="checkbox"><input name="readonly" type="checkbox" data-bind="checked: readonly"> Read Only</label>
						<label class="checkbox"><input name="multiple" type="checkbox" data-bind="checked: multiple"> Allow Multiple Selections</label>
						<label class="checkbox"><input name="showcount" type="checkbox" data-bind="checked: showcount"> Show Character Count</label>
						<label class="checkbox"><input name="hidenew" type="checkbox" data-bind="checked: hidenew"> Hide New Button</label>
					</div>
				</fieldset>
				</div>
			</div>
			<!-- <div id="fields"></div> -->
		</div>
	</div>
	
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>