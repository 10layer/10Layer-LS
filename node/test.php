<?php
	include("../10layer/resources/elephant.io/lib/ElephantIO/Client.php");
	use ElephantIO\Client as Elephant;
	$server = 'http://localhost:8181';
	print $server;
	$elephant = new Elephant($server, 'socket.io', 1, false, true, true);
	try {
	$elephant->init();
} catch (Exception $e) {
	print $e->getMessage();
	print_r($e);
	die();
}
	$elephant->emit('announce', array('data' => 'I am a walrus'), null);
    $elephant->close();
?>