<?php
	$headerdata["menu1"]="default";
	$headerdata["menu2"]="";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
	<div class="alert alert-error">
		<h2><?= $status ?></h2>
		<p>You do not have sufficient permissions to perform this action. Please speak to your systems administrator if this is an error.</p>
	</div>

<?php
	$this->load->view("/templates/footer");
?>