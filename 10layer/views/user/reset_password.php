<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="user/retrieve_password";
	$this->load->view("/templates/header",$headerdata);
?>
<h1>Reset Password</h1>
<?php
	if (!empty($error)) {
?>
<div class="alert alert-error">
	<h4>Error</h4>
	<?= $error ?>
</div>
<?php
	}
?>
<div id="retrieve" class="well">
	
	<legend>Please enter a new password</legend>
	<form id="retrieveform" method="post">
		<input type="hidden" name="dologin" value="1" />
		<label>Password</label>
		<input type="password" name="password" class="required" value="" autocomplete="off" /><br />
		<label>Confirm Password</label>
		<input type="password" name="password_confirm" class="required" value="" autocomplete="off" /><br />
		<input type="submit" id="submit" name="submit" value="Reset Password" class="btn" />
	</form>
	<br clear="both" />
</div>
<?php
	$this->load->view("/templates/footer");
?>