<script src="<?= $server ?>/socket.io/socket.io.js"></script>
<script>
	$(function(){
		var iosocket = io.connect("<?= $server ?>");
		iosocket.on('connect', function () {
			//Some online indication
			$("#online_indicator").show();
			$("#offline_indicator").hide();

			iosocket.on('undelete', function(id) {
				$(document).trigger("undelete", id);
			});

			iosocket.on('update', function(id) {
				$(document).trigger("update", id);
			});

			iosocket.on('delete', function(id) {
				$(document).trigger("delete", id);
			});

			iosocket.on('disconnect', function() {
				//Some offline indication
				$("#online_indicator").hide();
				$("#offline_indicator").show();
			});
		});

	});
</script>