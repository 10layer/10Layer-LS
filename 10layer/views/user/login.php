<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<script language="javascript">
	$(function() {
		$("input").keyup(function() {
			var reqs=checkreqs();
			if (!reqs) {
				$("#submit").addClass("inactive");
			} else {
				$("#submit").removeClass("inactive");
			}
		});
		$("#submit").click(function() {
			if (checkreqs()) {
				return true;
			}
			return false;
		});
		
		
	});
	
	function checkreqs() {
		var reqs=true;
		$(".required").each(function() {
			var val=$(this).val();
			if (val=="") {
				reqs=false;
			}
		});
		return reqs;
	}
</script>
<?php
	if ($error==1) {
?>
	<div class="message">Incorrect email or password.</div>
<?php
	}
?>
<div id="loginbox" class="boxed wide centered">
	<div class="title">Login</div>
	<form id="loginform" method="post">
		<input type="hidden" name="dologin" value="1" />
		<label>Email</label>
		<input type="text" name="email" class="required" value="" /><br />
		<label>Password</label>
		<input type="password" name="password" class="required" value="" /><br />
		<input class="button" type="submit" value="Login" />
	</form>
	<br clear="both" />
</div>
<?php
	$this->load->view("/templates/footer");
?>