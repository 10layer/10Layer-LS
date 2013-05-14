<?php
	$this->load->view("/templates/header",array("menu1"=>"default"));
?>
<script src="/resources/knockout/knockout-2.2.1.js"></script>
<script src="/resources/js/10layer.js"></script>
<script>
	
	$('.close_perms').live('click', function(){
		$(this).parent().hide();
	});
	
	$('.drop_activator').live('click', function(){
		$(this).next().show();
	});


	// Class to represent aan API key
	var ApiKey = function(data) {
		var self = this;
		
		self._id = ko.observable(data._id);
		self.name = ko.observable(data.name);
		self.api_key = ko.observable(data.api_key ? data.api_key : "");
		self.role = ko.observable(data.role);
		self.expiry = ko.observable(data.expiry ? data.expiry : 0 );
		self.revoked = ko.observable(data.revoked ? data.revoked : false );
		self.isEdit = ko.computed(function() {
			return !!(self._id());
		});
		self.expiryText = ko.computed(function() {
			if (self.expiry() == "0") {
				return "Never";
			}
			var date = new Date(self.expiry() * 1000);
			return date.toUTCString();
		});
		self.expiryEdit = ko.computed(function() {
			if (self.expiry() == "0") {
				return "";
			}
			var date = new Date(self.expiry() * 1000);
			return date.getYear() + "-" + date.getMonth() + "-" + date.getDay() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
		});
		self.isExpired = ko.computed(function() {
			if (self.expiry() == 0) {
				return false;
			}
			var today = new Date();
			var date = new Date(self.expiry() * 1000);
			return (today > date);
		});

		
	}

	// Overall viewmodel for this screen, along with initial state
	var ApiKeyViewModel = function() {
		var self = this;
		self.api_keys = ko.observableArray([]);
		self.roles = [
			{ name: "Administrator", value: "administrator" }, 
			{ name: "Editor", value: "editor" }, 
			{ name: "Viewer", value: "viewer" }
		];

		self.api_key = ko.observable();
		self.api_key(new ApiKey({ _id: false, name: "", api_key: "", role: "viewer", revoked: false }));

		self.update = function() {
			$.getJSON("/api/users/api_keys?api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
				mapped = _.map(data.content, function(item) {
					return new ApiKey(item) 
				});
				self.api_keys(mapped);
			});
		}
		
		self.clickSave = function() {
			$.ajax("/api/users/save_api_key?api_key=<?= $this->session->userdata("api_key") ?>", {
				data: ko.toJSON(this),
				type: "post", contentType: "application/json",
				success: function(result) { 
					if (result.error) {
						$("#fail_message").html(result.message.join("<br />"));
						$("#save_fail").slideDown(1000).delay(3000).slideUp(1000);
						$(".edit").modal("hide");
					} else {
						$("#save_success").slideDown(1000).delay(3000).slideUp(1000);
						self.update();
						$(".edit").modal("hide");
					}
				}
			});

		}
		
		self.clickAdd = function() {
			$.getJSON("/api/users/generate_api_key?api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
				self.api_key(new ApiKey({ _id: false, name: "", api_key: data.content, role: "viewer", revoked: false }));
				$(".edit").modal("show");
			});
		};

		self.clickEdit = function() {
			self.api_key(this);
			$(".edit").modal("show");
		}
		
		self.update();
	}
	
	$(function() {
		ko.applyBindings(new ApiKeyViewModel());
		
		$(".alert").on('click', '.close', function(e) {
			e.preventDefault();
			$(this).parent().hide();
		});
	});
</script>

<div class="page-header">
	<h1>Setup API Keys</h1>
</div>
<form method="post">
<div class="row">
	<div class="span2">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="/setup/admin">Administrator</a></li>
			<li><a href="/setup/users">Users</a></li>
			<li><a href="/setup/content_types">Content Types</a></li>
			<li class="active"><a href="/setup/api_keys">API Keys</a></li>
		</ul>
	</div>
	<div class="span10">
		<div class="row">
			<div class='span7'>
				<p>
					You can manage and change API keys here.
				</p>
			</div>
			<div class='span3'>
				<div class=" pull-right">
					<a href="#new" class="btn btn-success " data-bind="click: clickAdd"><i class="icon-plus"></i> Add New</a>
				    
				</div>
			</div>
		</div>
		<div class="span10">
			<div id="save_success" class="alert alert-success fade in" style="display: none"><a class="close" href="#">&times;</a> Users updated</div>
			<div id="save_fail" class="alert alert-error fade in" style="display: none"><a class="close" href="#">&times;</a> <h4>Failed to update Users</h4> <span id="fail_message"></span></div>
		</div>
		<table class="table">
			<tr>
				<th></th>
				<th>Name</th>
				<th>API Key</th>
				<th>Role</th>
				<th>Expiry</th>
				<th class='centralise'>Revoked</th>
			</tr>
			<!-- ko foreach: api_keys -->
			<tr>
				<td data-bind="click: $parent.clickEdit"><i class="icon-edit"></i></td>
				<td data-bind="text: name"></td>
				<td data-bind="text: api_key" autocomplete="off"></td>
				<td data-bind="text: role"></td>
				<td data-bind="text: expiryText, css: { 'text-warning': isExpired() }"></td>
				<td class='centralise'><input type="checkbox" name="status" value="" data-bind="checked: revoked" /></td>
			</tr>
			<!-- /ko -->
		</table>
	</div>
</div>
<div class="edit modal hide fade" data-bind="with: api_key">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    	<h3 data-bind="if: isEdit">Edit API Key</h3>
    	<h3 data-bind="ifnot: isEdit">Create API Key</h3>
    </div>
    <div class="modal-body">
		<label>Name</label>
		<input type="text" name="name" placeholder="Joe Soap" class='grid_input' data-bind="value: name" autocomplete="off">
		<label>API key</label>
		<input type="text" name="api_key" placeholder="" class='grid_input' data-bind="value: api_key" autocomplete="off">
		<label>Role</label>
		<select name="role" data-bind="value: role">
			<option value="viewer">Viewer</option>
			<option value="editor">Editor</option>
			<option value="administrator">Administrator</option>
		</select>
		<label>Expiry</label>

		<input type="text" name="expiry" placeholder="" class='grid_input' data-bind="value: expiryEdit" autocomplete="off">
		<label class="checkbox">
			Revoked
			<input type="checkbox" name="status" value="" data-bind="checked: revoked" />
		</label>
	</div>
	<div class="modal-footer">
		<input data-bind="click: $parent.clickSave" type="button" class="btn" value="Save" />
		<span data-bind="if: isEdit" ><input type="button" class="btn" value="Delete" /></span>
	</div>
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>