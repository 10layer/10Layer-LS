<?php
	$this->load->view("/templates/header",array("menu1"=>"default"));
?>
<div class="page-header">
	<h1>Setup Administrator</h1>
</div>
<form method="post">
<div class="row">
	<?php
	$this->load->view("setup/menu");
	?>
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