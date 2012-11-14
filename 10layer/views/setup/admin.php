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
			<li class="active"><a href="/setup/admin">Administrator</a></li>
			<li><a href="/setup/users">Users</a></li>
			<li><a href="/setup/content_types">Content Types</a></li>
			<li><a href="/setup/security">Security</a></li>
		</ul>
	</div>
	<div class="span5">
		<p>Please set up an administrator account. We suggest you use a strong password for this account - it will have access to the entire CMS.</p>
			
				<fieldset>
					<legend>Administrator</legend>
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
					<label>Email address</label>
					<input type="text" name="email" placeholder="admin@10layer.com">
					<label>Password</label>
					<input type="password" name="password" placeholder="Password">
					<span class="help-block">Random password: <?= $this->tlsecurity->random_pass(8, 10) ?></span>
					<button type="submit" class="btn">Submit</button>
				</fieldset>
			
	</div>
	<div class="span3">
		<a class="white" href="setup/content_types"><button type="submit" class="btn btn-large btn-primary" type="button"><i class="icon-thumbs-up icon-white"></i> Done here</button></a>
	</div>
</div>
</form>
<?php
	$this->load->view("/templates/footer");
?>