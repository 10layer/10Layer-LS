<?php
	$this->load->view("/templates/header",array("menu1"=>"default"));
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
		self.hidden = ko.observable(data.hidden);
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
		self.url = ko.computed(function() {
			return "/setup/content_types/" + self.urlid();
		});
		self.id = data._id; // We keep an immutable copy of this so we know which one to edit
		self.name = ko.observable(data.name);
		self.collection = ko.observable(data.collection);
		self.order_by = ko.observable(data.order_by);
		self.isActive = ko.observable(data.isActive);		
		self.clickRemove = function(data) {
			var pos = self.fields.indexOf(data);
			self.fields.splice(pos, 1);
		};
		
		self.clickAddField = function(data) {
			self.fields.push(new Field({ 
				name: "newtype",
				label: "New Type",
				type: "text"
			}));
		}
	};
		
	var ContentTypesModel = function() {
		var self = this;
		
		self.contentTypes = ko.observableArray([]);
		
		self.content_type_urlid = ko.computed({
			read: function() { return this },
			write: function(value) {
				var tmparr = self.contentTypes.removeAll();
				_.each(tmparr, function(item) {
					if (item.id == value) {
						item.isActive = true;	
					} else {
						item.isActive = false;
					}
				});
				self.contentTypes(tmparr);
			}
		});
		
		self.types = ko.observableArray(types);
		self.rules = ko.observableArray(rule_template);
		self.transformations = ko.observableArray(transformation_template);
		
		$.getJSON("/api/content_types?api_key=<?= $this->config->item("api_key") ?>", function(data) {
			self.mappedContentTypes = _.map(data.content, function(item, key) { if (item._id == self.content_type_urlid()) {item.isActive = true}; return new ContentType(item, item._id);  });
			self.contentTypes(self.mappedContentTypes);
			self.content_type_urlid("<?= $content_type_urlid ?>");
		});
		
		//Events
		self.clickAddContentType = function() {
			//Make sure we don't already have a new content type
			var found = false;
			_.each(self.contentTypes(), function(item) {
				if (item.urlid() == "new_content_type") {
					found = true;
				}
			});
			if (found) {
				return false;
			}
			var emptyType = empty_type_template;
			emptyType._id = "content_type_"+self.contentTypes().length;
			self.contentTypes.push(new ContentType( empty_type_template ));
			self.content_type_urlid(emptyType._id);
		};
		
		self.clickShowContentType = function(data) {
			self.content_type_urlid(data.id);
		};
		
		self.clickRemoveContentType = function(data) {
			self.contentTypes.remove(data);
		}
		
		self.save = function() {
			$.ajax("/api/content_types/save?api_key=<?= $this->config->item("api_key") ?>", {
				data: ko.toJSON({ content_types: self.contentTypes() }),
				type: "post", contentType: "application/json",
				success: function(result) { console.log("Saved") }
			});
		}
	};
	
	var types = [
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
	];
	
	var empty_type_template = { 
		"_id" : "new_content_type", 
		"fields" : [
		{
			"name" : "urlid",
			"hidden" : true,
			"type" : "text",
			"transformations" : [
				{ 
					"fn": "copy",
					"params": [ "title" ]
				},
				{
					"fn": "urlid",
					"params": [ "true" ]
				}
			]
		},
		{
			"name" : "title",
			"class" : "bigger",
			"label_class" : "bigger",
			"rules" : [
				{
					"fn": "required"
				}
			],
			"transformations" : [
				{ 
					"fn": "safetext" 
				}
			],
			"libraries" : {
				"semantic" : true,
				"search" : "like"
			},
			"type" : "textarea"
		},
		{
			"name" : "last_modified",
			"hidden" : true,
			"type" : "datetime",
			"hidden" : true,
			"transformations" : [
				{ 
					"fn": "date('c')",
					"hint": "Today's date"
				}
			]
		},
		{
			"name" : "start_date",
			"type" : "date",
			"value" : "Today"
		},
		{
			"name" : "workflow_status",
			"type" : "select",
			"options" : [
				"New",
				"Edited",
				"Published"
			]
		}], 
		"name" : "New Content Type", 
		"collection" : false, 
		"order_by" : [ "last_modified desc" ] 
	};
	
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
	
	var rule_template = [
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
	];
	
	var req_types = new Array("urlid", "title", "last_modified", "start_date", "workflow_status");
	
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
	<h1>Setup Content Types</h1>
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
				<button class="btn btn-large btn-primary" data-bind="click: save"><i class="icon-fire icon-white"></i> Onward!</button>
			</div>
		</div>
		<div id="content_type_app">
			<script> var x=0; </script>
			<ul class="nav nav-tabs">
				<!-- ko foreach: contentTypes -->
				<li data-bind="css: { active: isActive }"><a href="#" data-bind=""><span data-bind="text:name, click: $parent.clickShowContentType"></span> <i data-bind="click: $parent.clickRemoveContentType" class="icon-remove"></i></a> </li>
				<!-- /ko -->
				<li><a href="#" data-bind="click: clickAddContentType"><i class="icon-plus"></i></a></li>
			</ul>
			<!-- ko foreach: contentTypes -->
				<!-- ko if: isActive -->
			<fieldset class="form-inline">
				<label>Name</label>
				<input type="text" name="name" value="" data-bind="value: name ">
		 		<label>ID</label>
				<input type="text" name="urlid" value="" data-bind="value: urlid ">
				<label>Order By</label>
				<input type="text" name="order_by" value="" data-bind="value: order_by ">
				<label class="checkbox"><input name="collection" type="checkbox" data-bind="checked: collection"> Collection</label>
			</fieldset>
			<legend>Fields</legend>
			<div data-bind="foreach: fields">
				<div class="span3">
				<fieldset>
					<legend><button class='field-edit btn btn-small btn-primary'><i class='icon-edit icon-white'></i></button> 
						<!-- ko if: isRemovable -->
						<a class='field-delete btn btn-small btn-warning' data-bind="click: $parent.clickRemove"><i class='icon-trash icon-white'></i></a>
						<!-- /ko --> 
						<span data-bind="text: name"></span> 
					</legend>
					<div class='field-details' style='display: none'>
						<label>Name</label>
						<input type="text" name="name" value="" data-bind="value: name">
					
						<label>Label</label>
						<input type="text" name="label" value="" data-bind="value: label">
					
						<label>Type</label>
						<select name="content_type" data-bind="options: types, optionsText: 'name', optionsValue: '_id', value: type">
						</select>
					
						<label>Default value</label>
						<input type="text" name="value" value="" data-bind="value: defaultValue">
			
						<label>Rules</label>
						<div class="btn-group">
							<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#">
								Add a rule <span class="caret"></span>
							</a>
							<ul class="dropdown-menu" data-bind="foreach: rule_template">
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
							<ul class="dropdown-menu" data-bind="foreach: transformation_template">
								<li><a data-bind='text: fn, click: $parent.clickTransformationsAdd' href='#'></a></li>
							</ul>
						</div>
						<dl data-bind="foreach: transformations">
							<div>
							<dt><i class="icon-arrow-up" data-bind="click: $parent.clickTransformationsUpArrow"></i><i class="icon-arrow-down" data-bind="click: $parent.clickTransformationsDownArrow"></i><i class="icon-remove" data-bind="click: $parent.clickTransformationsRemove"></i> <span data-bind="text: fn"></span> <input type="text" data-bind="value: params" data-hint="Parameters" /></dt>
							<dd data-bind="text: hint"></dd>
							</div>
						</dl>
						
						<label>Import from another Content Type</label>
						<select name="content_type" multiple="multiple">
							<option value="">None</option>
							<!-- ko foreach: $root.contentTypes -->
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
						<label class="checkbox"><input name="hide" type="checkbox" data-bind="checked: hidden"> Do Not Render</label>
					</div>
				</fieldset>
				</div>
			</div>
				
			<div class="span3">
				<button class="btn btn-primary" data-bind="click: clickAddField"><i class="icon-plus icon-white"></i></button>
			</div>
				<!-- /ko -->
			<!-- /ko -->
			<!-- <div id="fields"></div> -->
		</div>
	</div>
	
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>