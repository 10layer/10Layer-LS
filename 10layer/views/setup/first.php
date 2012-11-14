<?php
	$headerdata["menu1"]="login";
	$headerdata["menu2"]="login";
	$headerdata["menu2_active"]="login";
	$this->load->view("/templates/header",$headerdata);
?>
<div class="page-header">
	<h1>Setup</h1>
</div>

<div class="row">
	<div class="span5">
		<p>This seems to be the first time you're using 10Layer. We'll need to set everything up first. If you're seeing this page and 10Layer was previously working, we suggest you give IT Support a call, pronto. Otherwise, let's get ready to set this thing up!</p>
	</div>
	<div class="span3">
		<a class="white" href="setup/admin"><button class="btn btn-large btn-primary" type="button"><i class="icon-thumbs-up icon-white"></i> Let's Go!</button></a>
	</div>
</div>
<?php
	$this->load->view("/templates/footer");
?>