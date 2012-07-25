<?php
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");
?>
<?php
	$jsoncallback=$this->input->get("jsoncallback");
?>
<?= (empty($jsoncallback)) ? json_encode($data) : "$jsoncallback(".json_encode($data).")" ?>