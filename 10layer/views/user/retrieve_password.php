<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="user/retrieve_password";
	$this->load->view("/templates/header",$headerdata);
?>
<?php
	if ($error==1) {
?>
	<div class="message">Incorrect email or password.</div>
<?php
	}
?>
<?php
	if ($email_sent) {
?>
	<div class="alert alert-block">Your password has been sent to your email.</div>
<?php
	}
?>
<div class="page-header">
	<h1>Retrieve Password</h1>
</div>
<div id="retrieve" class="well">
	<h3>Enter the email you use to sign in with to have a one-time login emailed to you</h3>
	<form id="retrieveform" method="post" class="form-inline">
		<input type="hidden" name="dologin" value="1" />
		<label>Email</label>
		<input type="text" name="email" class="required" value="" />
		<input type="submit" id="submit" name="submit" value="Retrieve Password" class="btn" />
	</form>
	<br clear="both" />
</div>
<?php
	$this->load->view("/templates/footer");
?>