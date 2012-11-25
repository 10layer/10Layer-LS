<?php
	$this->load->view("/templates/header",array("menu1"=>"default"));
?>
<script src="/resources/knockout/knockout-2.2.0.js"></script>
<script>
	var User = function(data) {
		var self = this;
		self.id = ko.observable(data._id);
		self.hash = ko.computed(function() {
			return "#"+self.id();
		});
		self.name = ko.observable(data.name);
		self.email = ko.observable(data.email);
		self.password = ko.observable("");
		self.permission = ko.observable(data.permission);
		self.isActive = ko.observable(false);
		self.title = ko.computed(function() {
			return (self.name().length > 0) ? self.name() : "New User";
		});
	}

	var Users = function() {
		var self = this;
		self.users = ko.observableArray([]);
		
		$.getJSON("/api/users?api_key=<?= $this->config->item("api_key") ?>", function(data) {
			mapped = _.map(data.content, function(item) { return new User(item) });
			self.users(mapped);
			self.users()[0].isActive(true);
		});
		
		self.save = function() {
			$.ajax("/api/users/save?api_key=<?= $this->config->item("api_key") ?>", {
				data: ko.toJSON({ users: self.users }),
				type: "post", contentType: "application/json",
				success: function(result) { 
					if (result.error) {
						$("#save_fail").show();
					} else {
						$("#save_success").show();
					}
				}
			});
		}
		
		self.add = function() {
			var tmparr = self.users.removeAll();
			_.each(tmparr, function(item) {
				item.isActive = false;
			});
			self.users(tmparr);
			self.users.push(new User( { id: "new", name: "", email: "", password: "", permissions: [], isActive: true } ));
		};
	}
	
	$(function() {
		ko.applyBindings(new Users());
		
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
	<div class="span7">
		<p>Here you can add a user or edit existing users. You can set up as many users as you like, but be warned: a user can never be deleted, only disabled. All fields are required. Please save when you're done.</p>
		<div class="tabbable tabs-left">
			<ul class="nav nav-tabs">
				<li><a href="#new" data-bind="click: add"><i class="icon-plus"></i></a></li>
				<!-- ko foreach: users -->
				<li data-bind="css: { active: isActive}"><a data-toggle="tab" data-bind="text: title, attr: { href: hash }"></a></li>
				<!-- /ko -->
			</ul>
  			<div class="tab-content" >
				<!-- ko foreach: users -->
				<div data-bind="attr: { id: id }, css: { active: isActive }" class="tab-pane">
					<label>Name</label>
					<input type="text" name="name" placeholder="Joe Soap" data-bind="value: name">
					<label>Email address</label>
				    <input type="text" name="email" placeholder="admin@10layer.com" data-bind="value: email">
				    <label>Password</label>
				    <input type="password" name="password" placeholder="Password" data-bind="value: password">
				    <span class="help-block">Random password: <?= $this->tlsecurity->random_pass(8, 10) ?></span>
				    <label>Permissions</label>
				    <label class="radio"><input type="radio" name="permission" value="Administrator"  data-bind="checked: permission, attr: { name: 'permission_' + id() }"> Administrator</label>
				    <label class="radio"><input type="radio" name="permission" value="Editor" data-bind="checked: permission, attr: { name: 'permission_' + id() }"> Editor</label>
				    <label class="radio"><input type="radio" name="permission" value="Viewer" data-bind="checked: permission, attr: { name: 'permission_' + id() }"> Viewer</label>
				</div>
				<!-- /ko -->
			</div>
		</div>
	</div>
	<div class="span3">
		<a class="btn btn-large btn-primary" href="#" data-bind="click: save"><i class="icon-thumbs-up icon-white"></i> Save users</a>
		<p />
				<div id="save_success" class="alert alert-success fade in" style="display: none"><a class="close" href="#">&times;</a> Users updated</div>
				<div id="save_fail" class="alert alert-error fade in" style="display: none"><a class="close" href="#">&times;</a> Failed to update Users</div>
	</div>
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>