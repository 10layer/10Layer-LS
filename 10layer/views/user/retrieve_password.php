<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="user/retrieve_password";
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
<?php
	if ($email_sent) {
?>
	<div class="message">Your password has been sent to your email.</div>
<?php
	}
?>

<div id="retrieve" class="boxed wide centered">
	<div class="title">Retrieve Password</div>
	Enter the email you use to sign in with to have a one-time login emailed to you
	<form id="retrieveform" method="post">
		<input type="hidden" name="dologin" value="1" />
		<label>Email</label>
		<input type="text" name="email" class="required" value="" /><br />
		<input type="submit" id="submit" name="submit" value="Retrieve Password" class="button" />
	</form>
	<br clear="both" />
</div>
<?php
	$this->load->view("/templates/footer");
?>