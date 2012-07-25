<?php
	$headerdata["menu1"]="default";
	$headerdata["menu2"]="";
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
	<div class="message">
		<?= $status ?>
		<p>You do not have sufficient permissions to perform this action. Please speak to your systems administrator if this is an error.</p>
		<p><a href='<?= base_url() ?>'>Return home</a></p>
	</div>

<?php
	$this->load->view("/templates/footer");
?>