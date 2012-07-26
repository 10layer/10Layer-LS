<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<?php
	if ($error==1) {
?>
	<div class="message">Incorrect email or password.</div>
<?php
	}
?>
<div class="page-header">
	<h1>Login</h1>
</div>
	
<div id="loginbox" >
	<form id="loginform" method="post" class="well form-inline">
		<input type="hidden" name="dologin" value="1" />
		<label>Email</label>
		<input type="text" name="email" class="required" value="" />
		<label>Password</label>
		<input type="password" name="password" class="required" value="" />
		<button type="submit">Login</button>
	</form>
</div>
<?php
	$this->load->view("/templates/footer");
?>