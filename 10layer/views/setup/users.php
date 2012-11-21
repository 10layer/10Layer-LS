<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<div class="page-header">
	<h1>Setup</h1>
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
	<div class="span5">
		<p>Let's set up some users.</p>
			<fieldset>
				<input id="fin" type="hidden" name="fin" value="0" />
			    <legend>Users</legend>
			    <?php
			    	if (isset($errors)) {
			    ?>
			    <ul>
			    <?php
			    		foreach($errors as $error) {
			    ?>
			    <li class="text-error"><?= $error ?></li>
			    <?php	
			    		}
			    ?>
			    </ul>
			    <?php
			    	}
			    ?>
			    <label>Name</label>
			    <input type="text" name="name" placeholder="Joe Soap">
			    <label>Email address</label>
			    <input type="text" name="email" placeholder="admin@10layer.com">
			    <label>Password</label>
			    <input type="password" name="password" placeholder="Password">
			    <span class="help-block">Random password: <?= $this->tlsecurity->random_pass(8, 10) ?></span>
			    <label>Permissions</label>
			    <label class="checkbox"><input type="checkbox" name="permissions[]" value="Administrator"> Administrator</label>
			    <label class="checkbox"><input type="checkbox" name="permissions[]" value="User"> User</label>
			    <label class="checkbox"><input type="checkbox" name="permissions[]" value="Viewer"> Viewer</label>
			    <button type="submit" class="btn">Add</button>
			</fieldset>
	</div>
	<div class="span3">
		<a class="btn btn-large btn-primary" href="/setup/content_types"><i class="icon-thumbs-up icon-white"></i> Done here</a>
	</div>
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>