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

	// Class to represent a user in the users grid
	var User = function(id, name, email, password, permission, activeness) {
		var self = this;
		self.id = ko.observable(id);
		self.name = ko.observable(name);
		self.email = ko.observable(email);
		self.password = ko.observable(password);
		self.permission = ko.observable(permission);
		self.isActive = ko.observable(activeness);
		self.title = ko.computed(function() {
			return (self.name.length > 0) ? self.name() : "New User";
		});
	}

	// Overall viewmodel for this screen, along with initial state
	var UsersViewModel = function() {
		var self = this;
		self.users = ko.observableArray([]);
		self.permissions = [
			{permissionName:'Administrator', permissionValue:'Administrator'},
			{permissionName:'Editor', permissionValue:'Editor'},
			{permissionName:'Viewer', permissionValue:'Viewer'}
		];
		self.randomPassword = ko.observable(randomPass());

		$.getJSON("/api/users?api_key=<?= $this->config->item("api_key") ?>", function(data) {
			mapped = _.map(data.content, function(item) {
				id = item._id;
				name = item.name;
				email = item.email;
				password = item.password;
				permission = self.permissions[self.getInitialPermissionIndex(item.permission)];//item.permission;
				activeness = item.isActive; 
				return new User(id, name,email, password, permission,activeness) 
			});
			self.users(mapped);
		});

		self.getInitialPermissionIndex = function(permission){
			index = null;
			for(var i = 0; i < self.permissions.length; i++){
				if(self.permissions[i].permissionValue == permission.permissionValue){
					index = i;
				}
			}
			return index;
		}
		
		self.save = function() {
			$.ajax("/api/users/save?api_key=<?= $this->config->item("api_key") ?>", {
				data: ko.toJSON({ users: self.users }),
				type: "post", contentType: "application/json",
				success: function(result) { 
					if (result.error) {
						console.log(result.message.join("<br />"));
						$("#fail_message").html(result.message.join("<br />"));
						$("#save_fail").slideDown(1000).delay(3000).slideUp(1000);
					} else {
						$("#save_success").slideDown(1000).delay(3000).slideUp(1000);
					}
				}
			});
		}
		
		self.add = function() {
			item = { id: "new", name: "", email: "", password: "", permission: self.permissions[1], isActive: true }
			id = item._id;
			name = item.name;
			email = item.email;
			password = item.password;
			permission = self.permissions[self.getInitialPermissionIndex(item.permission)];//item.permission;
			activeness = item.isActive; 
			user = new User(id, name,email, password, permission,activeness) ;
			self.users.push(user);
		};
		
		self.genNewPassword = function() {
			self.randomPassword(randomPass());
		}
	}
	
	$(function() {
		ko.applyBindings(new UsersViewModel());
		
		$(".alert").on('click', '.close', function(e) {
			e.preventDefault();
			$(this).parent().hide();
		});
	});
</script>

<div class="page-header">
	<h1>Setup Users</h1>
</div>

<form method="post">
<div class="row">
	<div class="span2">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="/setup/admin">Administrator</a></li>
			<li class="active"><a href="/setup/users">Users</a></li>
			<li><a href="/setup/content_types">Content Types</a></li>
			<li><a href="/setup/security">Security</a></li>
		</ul>
	</div>
	<div class="span10">
		<div class="row">
			<div class='span7'>
				<p>
					Here you can add a user or edit existing users. 
					You can set up as many users as you like, but be warned: a user can never be deleted, only disabled. 
					All fields are required. Please save when you're done.
				</p>
			</div>
			<div class='span3'>
				<div class="btn-group pull-right">
					<a href="#new" class="btn btn-success " data-bind="click: add"><i class="icon-plus"></i> Add New</a>
				    <a class="btn btn-primary " href="#" data-bind="click: save"><i class="icon-thumbs-up icon-white"></i> Save users</a>
				</div>
			</div>
		</div>
		<div class="span10">
			<div id="save_success" class="alert alert-success fade in" style="display: none"><a class="close" href="#">&times;</a> Users updated</div>
			<div id="save_fail" class="alert alert-error fade in" style="display: none"><a class="close" href="#">&times;</a> <h4>Failed to update Users</h4> <span id="fail_message"></span></div>
		</div>
		<table class="table">
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Password</th>
				<th>Permissions</th>
				<th class='centralise'>Account Active?</th>
			</tr>
			<!-- ko foreach: users -->
			<tr>
				<td><input type="text" name="name" placeholder="Joe Soap" class='grid_input' data-bind="value: name" autocomplete="off"></td>
				<td><input type="text" name="email" placeholder="admin@10layer.com" class='grid_input' data-bind="value: email" autocomplete="off"></td>
				<td><input type="password" name="password" placeholder="Leave blank to not change" class='grid_input' data-bind="value: password" autocomplete="off"></td>
				<td>
					<select data-bind="options: $root.permissions, value: permission, optionsText: 'permissionName'"></select>
			    </td>
				<td class='centralise'><input type="checkbox" name="status" value="" data-bind="checked: isActive" /></td>
			</tr>
			<!-- /ko -->
		</table>
	</div>
	
		<div class="offset2 span5">
			<h4>Random password</h4> <input type="text" data-bind="value: randomPassword" onclick="select()" />
			<div><a href="#gen" class="btn " data-bind="click: genNewPassword"><i class="icon-lock"></i> Generate new password</a></div>
		</div>
	
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>