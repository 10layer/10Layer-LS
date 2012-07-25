<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="user/retrieve_password";
	$this->load->view("/templates/header",$headerdata);
?>

<div id="retrieve" class="boxed wide centered">
	<div class="title">Reset Password</div>
	Please enter a new password
	<form id="retrieveform" method="post">
		<input type="hidden" name="dologin" value="1" />
		<label>Password</label>
		<input type="password" name="password" class="required" value="" /><br />
		<label>Confirm Password</label>
		<input type="password" name="password_confirm" class="required" value="" /><br />
		<input type="submit" id="submit" name="submit" value="Reset Password" class="button" />
	</form>
	<br clear="both" />
</div>
<?php
	$this->load->view("/templates/footer");
?>