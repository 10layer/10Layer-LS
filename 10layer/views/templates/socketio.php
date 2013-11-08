<?php
$socketio_enabled = true;
$socketio_port = 8080; //Must be same as port set in /node/server.js

if (!$socketio_enabled) {
	return true;
}
$socket_payload = new stdClass();
$socket_payload->location = $this->uri->rsegment_array();
$socket_payload->user = $this->session->userdata("name");
?>
<script src="<?= rtrim(base_url(), "/") . ":" . $socketio_port ?>/socket.io/socket.io.js"></script>
<script>
	var iosocket = io.connect("<?= rtrim(base_url(), "/") . ":" . $socketio_port ?>");

	iosocket.on('connect', function () {
		var payload = <?= json_encode($socket_payload) ?>;
		payload.status = "connect";
		iosocket.emit("announce", payload);
		iosocket.on('announced', function(data) {
			console.log(data);
		});

		iosocket.on('disconnect', function() {
			payload.status = "disconnect";
			iosocket.emit("announce", payload);
		});
	});

</script>